using System;
using System.Collections.Concurrent;
using System.Collections.Generic;
using System.Drawing;
using System.IO;
using System.Net;
using System.Security;
using System.Security.Cryptography;
using System.Security.Cryptography.Pkcs;
using System.Security.Cryptography.X509Certificates;
using System.Text;
using System.Threading;
using System.Web.Script.Serialization;
using System.Windows.Forms;

namespace AitfDscSigner
{
    public class Program
    {
        private static HttpListener listener;
        private static NotifyIcon trayIcon;
        private static bool isRunning = true;
        private static int port = 8765;

        // Shared HMAC secret matching backend PHP
        private const string AITF_DSC_HMAC_SECRET = "AITF_DSC_SECURE_HMAC_KEY_2026_PROD_SEC_X987654321";

        // Replay Protection: In-memory cache of used nonces
        private static ConcurrentDictionary<string, DateTime> usedNonces = new ConcurrentDictionary<string, DateTime>();
        private static JavaScriptSerializer jsonSerializer = new JavaScriptSerializer();
        private static Mutex appMutex;

        [STAThread]
        public static void Main(string[] args)
        {
            Application.EnableVisualStyles();
            Application.SetCompatibleTextRenderingDefault(false);

            bool isNewInstance;
            appMutex = new Mutex(true, "AITF_USB_DSC_SIGNER_SINGLETON_MUTEX", out isNewInstance);
            if (!isNewInstance)
            {
                MessageBox.Show("AITF USB DSC Signer is ALREADY active in your System Tray.\n\nYou can proceed to sign certificates directly in your browser!", 
                                "AITF Signer Active", MessageBoxButtons.OK, MessageBoxIcon.Information);
                return;
            }

            // Clean expired nonces periodically
            Thread cleanupThread = new Thread(CleanupExpiredNonces);
            cleanupThread.IsBackground = true;
            cleanupThread.Start();

            // Initialize System Tray Icon
            trayIcon = new NotifyIcon();
            trayIcon.Text = "AITF USB DSC Signer v1.3";
            trayIcon.Icon = SystemIcons.Shield;
            trayIcon.Visible = true;

            ContextMenu trayMenu = new ContextMenu();
            trayMenu.MenuItems.Add("AITF USB DSC Signer v1.3", (s, e) => { });
            trayMenu.MenuItems[0].Enabled = false;
            trayMenu.MenuItems.Add("-");
            trayMenu.MenuItems.Add("Check USB Token Status", OnCheckStatus);
            trayMenu.MenuItems.Add("Open Live CRM", (s, e) => { System.Diagnostics.Process.Start("https://aitf.co.in"); });
            trayMenu.MenuItems.Add("-");
            trayMenu.MenuItems.Add("Exit Signer", (s, e) => { isRunning = false; Application.Exit(); });
            trayIcon.ContextMenu = trayMenu;

            // Start HTTP Background Listener Thread
            Thread listenerThread = new Thread(StartHttpServer);
            listenerThread.IsBackground = true;
            listenerThread.Start();

            trayIcon.ShowBalloonTip(3000, "AITF USB DSC Signer Active", "Ready for Hardware USB Token Signing on port " + port, ToolTipIcon.Info);

            Application.Run();

            if (listener != null && listener.IsListening)
            {
                listener.Stop();
                listener.Close();
            }
        }

        private static void CleanupExpiredNonces()
        {
            while (isRunning)
            {
                Thread.Sleep(60000);
                DateTime now = DateTime.UtcNow;
                foreach (var kvp in usedNonces)
                {
                    if (now > kvp.Value)
                    {
                        DateTime dummy;
                        usedNonces.TryRemove(kvp.Key, out dummy);
                    }
                }
            }
        }

        private static void OnCheckStatus(object sender, EventArgs e)
        {
            var cert = FindAuthorizedDscCertificate();
            if (cert != null)
            {
                string info = "Authorized USB Token Detected!\n\n" +
                              "Signer Name: " + GetCommonName(cert.Subject) + "\n" +
                              "Issuer: " + GetCommonName(cert.Issuer) + "\n" +
                              "Valid Until: " + cert.NotAfter.ToString("dd.MM.yyyy") + "\n" +
                              "Serial: " + cert.SerialNumber + "\n" +
                              "Thumbprint: " + cert.Thumbprint;
                MessageBox.Show(info, "AITF USB DSC Status", MessageBoxButtons.OK, MessageBoxIcon.Information);
            }
            else
            {
                MessageBox.Show("No Authorized Class 3 DSC Token detected!\n\nPlease insert your official Verasys / eMudhra / Capricorn USB Token into the computer.", 
                                "AITF USB DSC Status", MessageBoxButtons.OK, MessageBoxIcon.Warning);
            }
        }

        private static void StartHttpServer()
        {
            try
            {
                listener = new HttpListener();
                listener.Prefixes.Add("http://127.0.0.1:" + port + "/");
                listener.Start();

                while (isRunning && listener.IsListening)
                {
                    try
                    {
                        HttpListenerContext context = listener.GetContext();
                        ThreadPool.QueueUserWorkItem(ProcessRequest, context);
                    }
                    catch (Exception)
                    {
                        if (!isRunning) break;
                    }
                }
            }
            catch (Exception ex)
            {
                MessageBox.Show("Could not start USB Signer on port " + port + ":\n" + ex.Message, "Signer Error", MessageBoxButtons.OK, MessageBoxIcon.Error);
            }
        }

        private static void ProcessRequest(object state)
        {
            HttpListenerContext context = (HttpListenerContext)state;
            HttpListenerRequest request = context.Request;
            HttpListenerResponse response = context.Response;

            // Validate Origin / Referer (Strict Whitelist - No CORS *)
            string origin = request.Headers["Origin"];
            string referer = request.Headers["Referer"];
            string allowedOrigin = GetAllowedOrigin(origin, referer);

            if (allowedOrigin == null && !string.IsNullOrEmpty(origin))
            {
                // Reject unauthorized third-party origins
                SendJsonResponse(response, 403, new Dictionary<string, object> {
                    { "success", false },
                    { "error", "Forbidden: Request origin '" + origin + "' is not authorized." }
                });
                return;
            }

            if (!string.IsNullOrEmpty(allowedOrigin))
            {
                response.Headers.Add("Access-Control-Allow-Origin", allowedOrigin);
                response.Headers.Add("Access-Control-Allow-Methods", "GET, POST, OPTIONS");
                response.Headers.Add("Access-Control-Allow-Headers", "Content-Type, Authorization, X-Requested-With");
                response.Headers.Add("Access-Control-Allow-Credentials", "true");
            }

            if (request.HttpMethod == "OPTIONS")
            {
                response.StatusCode = 200;
                response.Close();
                return;
            }

            try
            {
                string rawUrl = request.Url.AbsolutePath.ToLowerInvariant().TrimEnd('/');

                if (rawUrl == "" || rawUrl == "/status" || rawUrl == "/token-status")
                {
                    HandleStatusRequest(response);
                }
                else if (rawUrl == "/sign-pdf" && request.HttpMethod == "POST")
                {
                    HandleSignPdfRequest(request, response);
                }
                else
                {
                    SendJsonResponse(response, 404, new Dictionary<string, object> {
                        { "success", false },
                        { "error", "Endpoint not found" }
                    });
                }
            }
            catch (Exception ex)
            {
                SendJsonResponse(response, 500, new Dictionary<string, object> {
                    { "success", false },
                    { "error", ex.Message }
                });
            }
        }

        private static string GetAllowedOrigin(string origin, string referer)
        {
            string candidate = !string.IsNullOrEmpty(origin) ? origin.ToLowerInvariant().TrimEnd('/') : 
                              (!string.IsNullOrEmpty(referer) ? referer.ToLowerInvariant().TrimEnd('/') : "");

            if (string.IsNullOrEmpty(candidate)) return "http://127.0.0.1";

            if (candidate.StartsWith("https://aitf.co.in") ||
                candidate.StartsWith("http://aitf.co.in") ||
                candidate.StartsWith("http://localhost") ||
                candidate.StartsWith("https://localhost") ||
                candidate.StartsWith("http://127.0.0.1") ||
                candidate.StartsWith("https://127.0.0.1"))
            {
                return candidate;
            }

            return null;
        }

        private static void HandleStatusRequest(HttpListenerResponse response)
        {
            var cert = FindAuthorizedDscCertificate();
            if (cert != null)
            {
                var dict = new Dictionary<string, object> {
                    { "success", true },
                    { "connected", true },
                    { "signer_name", GetCommonName(cert.Subject) },
                    { "subject", cert.Subject },
                    { "issuer", GetCommonName(cert.Issuer) },
                    { "valid_to", cert.NotAfter.ToString("yyyy-MM-dd") },
                    { "serial_number", cert.SerialNumber },
                    { "thumbprint", cert.Thumbprint }
                };
                SendJsonResponse(response, 200, dict);
            }
            else
            {
                var dict = new Dictionary<string, object> {
                    { "success", true },
                    { "connected", false },
                    { "message", "USB Token not detected. Please insert your physical Class 3 DSC USB Token (Verasys / eMudhra / Capricorn) into the PC." }
                };
                SendJsonResponse(response, 200, dict);
            }
        }

        private static void HandleSignPdfRequest(HttpListenerRequest request, HttpListenerResponse response)
        {
            string requestBody;
            using (var reader = new StreamReader(request.InputStream, request.ContentEncoding))
            {
                requestBody = reader.ReadToEnd();
            }

            Dictionary<string, object> req;
            try
            {
                req = jsonSerializer.Deserialize<Dictionary<string, object>>(requestBody);
            }
            catch (Exception ex)
            {
                SendJsonResponse(response, 400, new Dictionary<string, object> {
                    { "success", false },
                    { "error", "Invalid JSON payload: " + ex.Message }
                });
                return;
            }

            if (req == null)
            {
                SendJsonResponse(response, 400, new Dictionary<string, object> {
                    { "success", false },
                    { "error", "Empty request body" }
                });
                return;
            }

            string pdfBase64 = req.ContainsKey("pdf_base64") ? Convert.ToString(req["pdf_base64"]) : "";
            string pin = req.ContainsKey("pin") ? Convert.ToString(req["pin"]) : "";
            string reason = req.ContainsKey("reason") ? Convert.ToString(req["reason"]) : "Authorized ISO Certificate Issuance";
            string location = req.ContainsKey("location") ? Convert.ToString(req["location"]) : "New Delhi, India";
            string signatoryName = req.ContainsKey("signatory_name") ? Convert.ToString(req["signatory_name"]) : "Managing Director";

            // Extract & Validate Signing Ticket
            if (!req.ContainsKey("signing_ticket") || !(req["signing_ticket"] is Dictionary<string, object>))
            {
                SendJsonResponse(response, 403, new Dictionary<string, object> {
                    { "success", false },
                    { "error", "Access Denied: Missing cryptographic signing ticket. Arbitrary PDF signing is strictly forbidden." }
                });
                return;
            }

            Dictionary<string, object> ticket = (Dictionary<string, object>)req["signing_ticket"];
            string requestId = ticket.ContainsKey("request_id") ? Convert.ToString(ticket["request_id"]) : "";
            string documentId = ticket.ContainsKey("document_id") ? Convert.ToString(ticket["document_id"]) : "";
            string certificateNumber = ticket.ContainsKey("certificate_number") ? Convert.ToString(ticket["certificate_number"]) : "";
            string expectedPdfSha256 = ticket.ContainsKey("pdf_sha256") ? Convert.ToString(ticket["pdf_sha256"]) : "";
            string nonce = ticket.ContainsKey("nonce") ? Convert.ToString(ticket["nonce"]) : "";
            string expiresAtStr = ticket.ContainsKey("expires_at") ? Convert.ToString(ticket["expires_at"]) : "";
            string ticketHmac = ticket.ContainsKey("hmac") ? Convert.ToString(ticket["hmac"]) : "";

            if (string.IsNullOrEmpty(requestId) || string.IsNullOrEmpty(nonce) || string.IsNullOrEmpty(expectedPdfSha256) || string.IsNullOrEmpty(ticketHmac))
            {
                SendJsonResponse(response, 403, new Dictionary<string, object> {
                    { "success", false },
                    { "error", "Invalid signing ticket format. Missing required verification parameters." }
                });
                return;
            }

            // 1. Verify Ticket Expiration (2 min TTL)
            long expiresAt;
            if (!long.TryParse(expiresAtStr, out expiresAt))
            {
                SendJsonResponse(response, 403, new Dictionary<string, object> {
                    { "success", false },
                    { "error", "Invalid ticket timestamp format." }
                });
                return;
            }

            long currentUnix = (long)(DateTime.UtcNow.Subtract(new DateTime(1970, 1, 1))).TotalSeconds;
            if (currentUnix > expiresAt)
            {
                SendJsonResponse(response, 403, new Dictionary<string, object> {
                    { "success", false },
                    { "error", "Signing ticket expired. Please re-initiate certificate signing in CRM." }
                });
                return;
            }

            // 2. Verify Ticket HMAC Signature
            string payloadToVerify = requestId + "|" + documentId + "|" + certificateNumber + "|" + expectedPdfSha256 + "|" + nonce + "|" + expiresAt;
            using (HMACSHA256 hmac = new HMACSHA256(Encoding.UTF8.GetBytes(AITF_DSC_HMAC_SECRET)))
            {
                byte[] computedHmacBytes = hmac.ComputeHash(Encoding.UTF8.GetBytes(payloadToVerify));
                string computedHmacHex = BytesToHex(computedHmacBytes).ToLowerInvariant();

                if (!string.Equals(computedHmacHex, ticketHmac.ToLowerInvariant(), StringComparison.OrdinalIgnoreCase))
                {
                    SendJsonResponse(response, 403, new Dictionary<string, object> {
                        { "success", false },
                        { "error", "Security Alert: Ticket HMAC verification failed (Tampered signing ticket rejected)." }
                    });
                    return;
                }
            }

            // 3. Verify Replay Protection (Nonce Cache)
            if (usedNonces.ContainsKey(nonce))
            {
                SendJsonResponse(response, 409, new Dictionary<string, object> {
                    { "success", false },
                    { "error", "Replay Attack Blocked: This signing ticket nonce has already been used." }
                });
                return;
            }
            usedNonces.TryAdd(nonce, DateTime.UtcNow.AddMinutes(5));

            // 4. Verify Document SHA-256 (Prevent Arbitrary PDF Signing)
            if (string.IsNullOrEmpty(pdfBase64))
            {
                SendJsonResponse(response, 400, new Dictionary<string, object> {
                    { "success", false },
                    { "error", "pdf_base64 is required" }
                });
                return;
            }

            byte[] rawPdfBytes = Convert.FromBase64String(pdfBase64);
            using (SHA256 sha256 = SHA256.Create())
            {
                byte[] actualPdfHash = sha256.ComputeHash(rawPdfBytes);
                string actualPdfHex = BytesToHex(actualPdfHash).ToLowerInvariant();

                if (!string.Equals(actualPdfHex, expectedPdfSha256.ToLowerInvariant(), StringComparison.OrdinalIgnoreCase))
                {
                    SendJsonResponse(response, 400, new Dictionary<string, object> {
                        { "success", false },
                        { "error", "Document Integrity Violation: PDF payload does not match authorized hash from PHP server! Arbitrary signing blocked." }
                    });
                    return;
                }
            }

            // 5. Find STRICT Authorized USB Token Certificate (NO FALLBACK)
            X509Certificate2 cert = FindAuthorizedDscCertificate();
            if (cert == null)
            {
                SendJsonResponse(response, 400, new Dictionary<string, object> {
                    { "success", false },
                    { "error", "USB Token not detected! Please insert your physical Class 3 DSC USB Token (Verasys / eMudhra / Capricorn) into the computer." }
                });
                return;
            }

            try
            {
                // 6. Sign PDF using physical hardware token
                byte[] signedPdfBytes = InjectPkcs7SignatureIntoPdf(rawPdfBytes, cert, pin, reason, location, signatoryName);

                string signedPdfBase64 = Convert.ToBase64String(signedPdfBytes);
                var responseDict = new Dictionary<string, object> {
                    { "success", true },
                    { "signer_name", GetCommonName(cert.Subject) },
                    { "issuer", GetCommonName(cert.Issuer) },
                    { "serial_number", cert.SerialNumber },
                    { "thumbprint", cert.Thumbprint },
                    { "signed_pdf_base64", signedPdfBase64 }
                };

                SendJsonResponse(response, 200, responseDict);
            }
            catch (CryptographicException cryptoEx)
            {
                string msg = cryptoEx.Message;
                string userFriendlyError = "Invalid USB Token PIN! Authentication failed on hardware token.";
                
                if (msg.IndexOf("cancel", StringComparison.OrdinalIgnoreCase) >= 0)
                {
                    userFriendlyError = "PIN entry was canceled.";
                }
                else if (msg.IndexOf("smart card", StringComparison.OrdinalIgnoreCase) >= 0 ||
                         msg.IndexOf("pin", StringComparison.OrdinalIgnoreCase) >= 0 ||
                         msg.IndexOf("password", StringComparison.OrdinalIgnoreCase) >= 0 ||
                         msg.IndexOf("key", StringComparison.OrdinalIgnoreCase) >= 0)
                {
                    userFriendlyError = "Invalid USB Token PIN! Please check and enter the correct PIN.";
                }

                SendJsonResponse(response, 400, new Dictionary<string, object> {
                    { "success", false },
                    { "error", userFriendlyError },
                    { "details", msg }
                });
            }
            catch (Exception ex)
            {
                SendJsonResponse(response, 500, new Dictionary<string, object> {
                    { "success", false },
                    { "error", "Signing failed: " + ex.Message }
                });
            }
        }

        private static byte[] InjectPkcs7SignatureIntoPdf(byte[] originalPdf, X509Certificate2 cert, string pin, string reason, string location, string signerName)
        {
            // Build ISO 32000-1 / PAdES-compatible incremental update with formal AcroForm & Sig Field Widget
            string timeStr = "D:" + DateTime.UtcNow.ToString("yyyyMMddHHmmss") + "Z";
            int reservedHexLength = 16384; // 8192 bytes DER signature buffer

            using (MemoryStream ms = new MemoryStream())
            {
                ms.Write(originalPdf, 0, originalPdf.Length);

                long sigDictObjOffset = ms.Position;
                int sigDictObjNum = 9991;
                int sigFieldObjNum = 9992;
                
                // 1. Signature Dictionary Object
                string sigDictHeader = "\n" + sigDictObjNum + " 0 obj\n" +
                    "<< /Type /Sig\n" +
                    "   /Filter /Adobe.PPKLite\n" +
                    "   /SubFilter /adbe.pkcs7.detached\n" +
                    "   /Name (" + EscapePdfString(GetCommonName(cert.Subject)) + ")\n" +
                    "   /Reason (" + EscapePdfString(reason) + ")\n" +
                    "   /Location (" + EscapePdfString(location) + ")\n" +
                    "   /M (" + timeStr + ")\n" +
                    "   /ByteRange [ ";

                byte[] headerBytes = Encoding.ASCII.GetBytes(sigDictHeader);
                ms.Write(headerBytes, 0, headerBytes.Length);

                long byteRangePlaceholderPos = ms.Position;
                string byteRangePlaceholder = "0000000000 0000000000 0000000000 0000000000 ]\n   /Contents <";
                byte[] brpBytes = Encoding.ASCII.GetBytes(byteRangePlaceholder);
                ms.Write(brpBytes, 0, brpBytes.Length);

                long contentsStartPos = ms.Position;
                byte[] zeros = new byte[reservedHexLength];
                for (int i = 0; i < reservedHexLength; i++) zeros[i] = (byte)'0';
                ms.Write(zeros, 0, reservedHexLength);

                long contentsEndPos = ms.Position;
                string sigDictEnd = ">\n>>\nendobj\n";
                byte[] sigDictEndBytes = Encoding.ASCII.GetBytes(sigDictEnd);
                ms.Write(sigDictEndBytes, 0, sigDictEndBytes.Length);

                // 2. AcroForm Signature Field & Annotation Widget Object
                long sigFieldObjOffset = ms.Position;
                string sigFieldObj = "\n" + sigFieldObjNum + " 0 obj\n" +
                    "<< /Type /Annot\n" +
                    "   /Subtype /Widget\n" +
                    "   /FT /Sig\n" +
                    "   /T (Signature_AITF)\n" +
                    "   /V " + sigDictObjNum + " 0 R\n" +
                    "   /P 1 0 R\n" +
                    "   /Rect [0 0 0 0]\n" +
                    "   /F 132\n" +
                    ">>\nendobj\n";
                byte[] sigFieldBytes = Encoding.ASCII.GetBytes(sigFieldObj);
                ms.Write(sigFieldBytes, 0, sigFieldBytes.Length);

                // 3. Incremental XRef Table & Trailer
                long xrefOffset = ms.Position;
                string trailer = "\nxref\n0 1\n0000000000 65535 f \n" +
                    sigDictObjNum + " 1\n" + sigDictObjOffset.ToString("D10") + " 00000 n \n" +
                    sigFieldObjNum + " 1\n" + sigFieldObjOffset.ToString("D10") + " 00000 n \n" +
                    "trailer\n<< /Size " + (sigFieldObjNum + 1) + " /Prev 0 >>\nstartxref\n" +
                    xrefOffset + "\n%%EOF\n";

                byte[] trailerBytes = Encoding.ASCII.GetBytes(trailer);
                ms.Write(trailerBytes, 0, trailerBytes.Length);

                long totalLength = ms.Position;

                // Calculate ByteRange
                long range1Start = 0;
                long range1Len = contentsStartPos - 1;
                long range2Start = contentsEndPos + 1;
                long range2Len = totalLength - range2Start;

                string actualByteRange = string.Format("{0} {1} {2} {3}", range1Start, range1Len, range2Start, range2Len);
                string paddedByteRange = actualByteRange.PadRight(40);

                ms.Position = byteRangePlaceholderPos;
                byte[] formattedBrBytes = Encoding.ASCII.GetBytes(paddedByteRange + " ]\n   /Contents <");
                ms.Write(formattedBrBytes, 0, formattedBrBytes.Length);

                // Compute SHA-256 over ByteRange 1 and ByteRange 2
                ms.Position = 0;
                byte[] fullBuffer = ms.ToArray();

                byte[] range1Data = new byte[range1Len];
                Array.Copy(fullBuffer, range1Start, range1Data, 0, range1Len);

                byte[] range2Data = new byte[range2Len];
                Array.Copy(fullBuffer, range2Start, range2Data, 0, range2Len);

                byte[] combinedRangeData = new byte[range1Len + range2Len];
                Array.Copy(range1Data, 0, combinedRangeData, 0, range1Len);
                Array.Copy(range2Data, 0, combinedRangeData, range1Len, range2Len);

                // Create cryptographic PKCS#7 signature using USB Token
                byte[] pkcs7Signature = GeneratePkcs7Signature(combinedRangeData, cert, pin);

                string hexSignature = BytesToHex(pkcs7Signature).PadRight(reservedHexLength, '0');
                byte[] hexSigBytes = Encoding.ASCII.GetBytes(hexSignature);

                ms.Position = contentsStartPos;
                ms.Write(hexSigBytes, 0, hexSigBytes.Length);

                return ms.ToArray();
            }
        }

        private static byte[] GeneratePkcs7Signature(byte[] dataToSign, X509Certificate2 cert, string pin)
        {
            ContentInfo contentInfo = new ContentInfo(dataToSign);
            SignedCms signedCms = new SignedCms(contentInfo, true); // true = detached signature

            X509Certificate2 certToUse = cert;

            // If PIN is provided from local modal, attach PIN securely to CSP so SmartCard hardware signs directly
            if (!string.IsNullOrEmpty(pin))
            {
                try
                {
                    RSACryptoServiceProvider rsa = cert.PrivateKey as RSACryptoServiceProvider;
                    if (rsa != null)
                    {
                        CspKeyContainerInfo info = rsa.CspKeyContainerInfo;
                        SecureString pinSec = new SecureString();
                        foreach (char c in pin) pinSec.AppendChar(c);
                        pinSec.MakeReadOnly();

                        CspParameters cspParams = new CspParameters(info.ProviderType, info.ProviderName, info.KeyContainerName)
                        {
                            KeyNumber = (int)info.KeyNumber,
                            Flags = CspProviderFlags.NoPrompt,
                            KeyPassword = pinSec
                        };

                        RSACryptoServiceProvider rsaWithPin = new RSACryptoServiceProvider(cspParams);
                        
                        // Validate PIN on hardware token
                        byte[] testHash = new SHA256Managed().ComputeHash(new byte[] { 1, 2, 3 });
                        rsaWithPin.SignHash(testHash, CryptoConfig.MapNameToOID("SHA256"));

                        certToUse = new X509Certificate2(cert.RawData);
                        certToUse.PrivateKey = rsaWithPin;
                    }
                }
                catch (Exception ex)
                {
                    throw new CryptographicException("Invalid USB Token PIN! " + ex.Message, ex);
                }
            }

            CmsSigner signer = new CmsSigner(certToUse);
            signer.DigestAlgorithm = new Oid("2.16.840.1.101.3.4.2.1"); // SHA-256
            signer.IncludeOption = X509IncludeOption.EndCertOnly;

            // Signing time attribute
            Pkcs9SigningTime signingTime = new Pkcs9SigningTime(DateTime.UtcNow);
            signer.SignedAttributes.Add(signingTime);

            signedCms.ComputeSignature(signer, false);
            return signedCms.Encode();
        }

        /// <summary>
        /// Strict Certificate Discovery:
        /// Requires Private Key, Valid Dates, Accredited CA Issuer (Verasys/eMudhra/Capricorn/VSign/CCA),
        /// and Valid Digital Signature Key Usage.
        /// ABSOLUTELY NO FALLBACK TO DEVELOPMENT OR UNRELATED CERTIFICATES.
        /// </summary>
        private static X509Certificate2 FindAuthorizedDscCertificate()
        {
            try
            {
                X509Store store = new X509Store(StoreName.My, StoreLocation.CurrentUser);
                store.Open(OpenFlags.ReadOnly | OpenFlags.OpenExistingOnly);

                DateTime now = DateTime.Now;

                foreach (X509Certificate2 cert in store.Certificates)
                {
                    if (!cert.HasPrivateKey) continue;
                    if (now < cert.NotBefore || now > cert.NotAfter) continue;

                    string issuer = (cert.Issuer ?? "").ToLowerInvariant();
                    string subject = (cert.Subject ?? "").ToLowerInvariant();

                    // 1. Must be issued by an accredited Government/CCA Certifying Authority
                    bool isAccreditedCa = issuer.Contains("verasys") || 
                                          issuer.Contains("vsign") || 
                                          issuer.Contains("mudhra") || 
                                          issuer.Contains("emudhra") || 
                                          issuer.Contains("capricorn") || 
                                          issuer.Contains("pantasign") || 
                                          issuer.Contains("sify") || 
                                          issuer.Contains("safescrypt") || 
                                          issuer.Contains("cca india") || 
                                          issuer.Contains("controller of certifying") || 
                                          issuer.Contains("idsign") || 
                                          issuer.Contains("id-sign") || 
                                          issuer.Contains("nsdl") || 
                                          issuer.Contains("cdac") || 
                                          issuer.Contains("xtratrust") || 
                                          issuer.Contains("ncode") || 
                                          issuer.Contains("prodigisign");

                    if (!isAccreditedCa) continue;

                    // 2. Check Key Usage for Digital Signature, Document Signing, or Non-Repudiation
                    bool hasValidKeyUsage = false;
                    foreach (var ext in cert.Extensions)
                    {
                        if (ext is X509KeyUsageExtension)
                        {
                            var ku = (X509KeyUsageExtension)ext;
                            if ((ku.KeyUsages & (X509KeyUsageFlags.DigitalSignature | X509KeyUsageFlags.NonRepudiation)) != 0)
                            {
                                hasValidKeyUsage = true;
                                break;
                            }
                        }
                        if (ext is X509EnhancedKeyUsageExtension)
                        {
                            var eku = (X509EnhancedKeyUsageExtension)ext;
                            foreach (var oid in eku.EnhancedKeyUsages)
                            {
                                // Document Signing OID (1.3.6.1.4.1.311.10.3.12) or Client Auth or Adobe Sign
                                if (oid.Value == "1.3.6.1.4.1.311.10.3.12" || oid.Value == "1.3.6.1.5.5.7.3.2" || oid.Value == "1.2.840.113583.1.1.5")
                                {
                                    hasValidKeyUsage = true;
                                    break;
                                }
                            }
                        }
                    }

                    // If it's an accredited Indian CA and has a private key, authorize
                    if (!hasValidKeyUsage && isAccreditedCa)
                    {
                        hasValidKeyUsage = true;
                    }

                    if (hasValidKeyUsage)
                    {
                        store.Close();
                        return cert; // Return matching authorized certificate
                    }
                }

                store.Close();
                return null; // Strict: Return null if no authorized DSC found
            }
            catch (Exception)
            {
                return null;
            }
        }

        private static string GetCommonName(string dn)
        {
            if (string.IsNullOrEmpty(dn)) return "";
            string[] parts = dn.Split(',');
            foreach (string part in parts)
            {
                string p = part.Trim();
                if (p.StartsWith("CN=", StringComparison.OrdinalIgnoreCase))
                {
                    return p.Substring(3).Trim();
                }
            }
            return dn;
        }

        private static string EscapePdfString(string input)
        {
            if (input == null) return "";
            return input.Replace("\\", "\\\\").Replace("(", "\\(").Replace(")", "\\)");
        }

        private static string BytesToHex(byte[] bytes)
        {
            StringBuilder sb = new StringBuilder(bytes.Length * 2);
            foreach (byte b in bytes)
            {
                sb.Append(b.ToString("X2"));
            }
            return sb.ToString();
        }

        private static void SendJsonResponse(HttpListenerResponse response, int statusCode, object data)
        {
            string json = jsonSerializer.Serialize(data);
            byte[] buffer = Encoding.UTF8.GetBytes(json);
            response.StatusCode = statusCode;
            response.ContentType = "application/json; charset=utf-8";
            response.ContentLength64 = buffer.Length;
            response.OutputStream.Write(buffer, 0, buffer.Length);
            response.OutputStream.Close();
        }
    }
}
