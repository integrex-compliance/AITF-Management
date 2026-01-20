<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './headSource.php'; ?>
    <style>
        :root {
            --primary-color: #1a365d;
            --secondary-color: #2b6cb0;
            --accent-color: #c53030;
            --light-bg: #f7fafc;
            --border-radius: 10px;
            --box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.7;
            color: #2d3748;
            background-color: #f8fafc;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-weight: 700;
            line-height: 1.3;
            color: var(--primary-color);
        }
        
        .document-section {
            background: #ffffff;
            border-radius: 12px;
            padding: 50px;
            margin: 30px auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
            max-width: 1200px;
            position: relative;
            overflow: hidden;
        }
        
        .document-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom, var(--secondary-color), #4a90e2);
        }
        
        .document-header {
            text-align: center;
            margin: 0 0 50px 0;
            padding-bottom: 30px;
            border-bottom: 1px solid #e2e8f0;
            position: relative;
        }
        
        .document-header::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(90deg, var(--secondary-color), #4a90e2);
            border-radius: 3px;
        }
        
        .document-title {
            color: var(--primary-color);
            font-size: 2.4rem;
            margin: 0 0 15px 0;
            font-weight: 800;
            letter-spacing: -0.5px;
            line-height: 1.2;
            background: linear-gradient(135deg, var(--primary-color), #2c5282);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-fill-color: transparent;
        }
        
        .document-subtitle {
            color: #4a5568;
            font-size: 1.3rem;
            margin: 0 0 15px 0;
            font-weight: 500;
            line-height: 1.5;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .document-meta {
            color: #718096;
            font-size: 0.95rem;
            margin: 25px 0 0 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .document-meta span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f7fafc;
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            font-size: 0.9rem;
        }
        
        .section-title {
            color: var(--primary-color);
            font-size: 1.6rem;
            margin: 45px 0 20px;
            padding: 0 0 12px 15px;
            border-left: 4px solid var(--secondary-color);
            position: relative;
            line-height: 1.4;
        }
        
        .section-title::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 15px;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, var(--secondary-color), rgba(43, 108, 176, 0.2));
            border-radius: 3px;
        }
        
        .document-content {
            font-size: 1.1rem;
            line-height: 1.9;
            color: #2d3748;
            max-width: 900px;
            margin: 0 auto;
        }
        
        .document-content p {
            margin-bottom: 15px;
        }
        
        .document-content ul, 
        .document-content ol {
            margin: 15px 0 15px 30px;
        }
        
        .document-content li {
            margin-bottom: 8px;
        }
        
        .highlight-box {
            background: linear-gradient(135deg, #f7fafc, #ebf8ff);
            border-left: 4px solid var(--secondary-color);
            padding: 25px 30px;
            margin: 35px 0;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
            position: relative;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        
        .highlight-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23e6f0ff' fill-opacity='0.3'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.3;
            z-index: 0;
        }
        
        .highlight-box > * {
            position: relative;
            z-index: 1;
        }
        
        .highlight-box h4 {
            color: var(--secondary-color);
            margin-top: 0;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .highlight-box h4::before {
            content: 'ℹ️';
            font-size: 1.1em;
        }
        
        .document-footer {
            margin-top: 60px;
            padding: 25px 0 0;
            border-top: 1px solid #e2e8f0;
            font-size: 0.9rem;
            color: #718096;
            text-align: center;
        }
        
        .document-footer p {
            margin: 5px 0;
        }
        
        .document-footer p:first-child {
            font-weight: 500;
            color: #4a5568;
        }
        
        .document-actions {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin: 50px 0 30px;
            flex-wrap: wrap;
        }
        
        .btn-document {
            padding: 14px 32px;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            position: relative;
            overflow: hidden;
            z-index: 1;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .btn-document::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            transition: var(--transition);
            opacity: 1;
            transform: scale(1);
            border-radius: 50px;
        }
        
        .btn-document::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            transition: var(--transition);
            opacity: 0;
            transform: scale(0.8);
            border-radius: 50px;
        }
        
        .btn-document:hover::before {
            opacity: 0;
            transform: scale(1.1);
        }
        
        .btn-document:hover::after {
            opacity: 1;
            transform: scale(1);
        }
        
        .btn-view {
            background: linear-gradient(135deg, var(--secondary-color), #3182ce);
            color: white;
            border: none;
        }
        
        .btn-view::before {
            background: linear-gradient(135deg, var(--secondary-color), #3182ce);
        }
        
        .btn-view::after {
            background: linear-gradient(135deg, #2c5282, var(--secondary-color));
        }
        
        .btn-download {
            background: linear-gradient(135deg, #38a169, #2f855a);
            color: white;
            border: none;
        }
        
        .btn-download::before {
            background: linear-gradient(135deg, #38a169, #2f855a);
        }
        
        .btn-download::after {
            background: linear-gradient(135deg, #2f855a, #276749);
        }
        
        .btn-view:hover {
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(43, 108, 176, 0.3);
        }
        
        .btn-download:hover {
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(56, 161, 105, 0.3);
        }
        
        @media (max-width: 992px) {
            .document-section {
                padding: 40px 25px;
                margin: 20px 15px;
            }
            
            .document-title {
                font-size: 2rem;
            }
            
            .document-subtitle {
                font-size: 1.15rem;
            }
            
            .section-title {
                font-size: 1.4rem;
            }
            
            .document-content {
                font-size: 1.05rem;
            }
            
            .highlight-box {
                padding: 20px;
            }
        }
        
        @media (max-width: 576px) {
            .document-section {
                padding: 30px 20px;
                margin: 15px 10px;
                border-radius: 8px;
            }
            
            .document-title {
                font-size: 1.7rem;
            }
            
            .document-subtitle {
                font-size: 1.05rem;
            }
            
            .section-title {
                font-size: 1.3rem;
                padding-left: 12px;
            }
            
            .document-actions {
                flex-direction: column;
                gap: 12px;
            }
            
            .btn-document {
                width: 100%;
                justify-content: center;
                padding: 14px 20px;
            }
            
            .document-meta {
                flex-direction: column;
                gap: 10px;
            }
            
            .document-meta span {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>

    <!-- Main Content -->
    <div class="section margin-top_80">
        <div class="container">
            <div class="document-section">
                <!-- Document Header -->
                <div class="document-header">
                    <h1 class="document-title">AITF MANAGEMENT CERTIFICATION LIMITED</h1>
                    <h2 class="document-subtitle">GUIDELINE FOR – Granting, Refusing, Maintaining, Renewing, Suspending, Restoring, Withdrawing, Expanding and Reducing of scope of certification</h2>
                    <div class="document-meta">
                        Document: G 1.11.00.12.04.2017 | 
                        <span id="fileSize">File size: Loading...</span>
                    </div>
                </div>

                <!-- Document Actions -->
                <div class="document-actions">
                    <a href="text/guidline_g_1_11.pdf" target="_blank" class="btn-document btn-view">
                        <i class="fa fa-eye"></i> View Full Document
                    </a>
                    <a href="text/guidline_g_1_11.pdf" download class="btn-document btn-download">
                        <i class="fa fa-download"></i> Download PDF
                    </a>
                </div>

                <!-- Document Content -->
                <div class="document-content">
                    <h3 class="section-title">1. Introduction</h3>
                    <p>This policy document describes the process of Granting, Refusing, Maintaining, Renewing, Suspending, Restoring, Withdrawing, Expanding and Reducing of scope of certification.</p>

                    <h3 class="section-title">2. Granting</h3>
                    <p>Certification is granted to organizations that have demonstrated conformity to the criteria for certification.</p>

                    <h3 class="section-title">3. Refusing</h3>
                    <p>AITF conducts a review of every application received to determine capability to undertake the conformity assessment activity applied for. Where it is determined that the AITF can does not have either the competence or the resources to undertake the activity or analysis shows any risk associated, in such event AITF may declines and informs to applicant with justification accordingly.</p>

                    <h3 class="section-title">4. Maintaining</h3>
                    <h4>4.1 General</h4>
                    <p>AITF certification is maintained through surveillance audits, recertification audits and special audits.</p>
                    
                    <h4>4.2 Surveillance Audits</h4>
                    <p>Surveillance audits are carried out at intervals specified in the certification contract. The surveillance assessment shall be carried out before 12 month from the date of initial certification decision.</p>
                    
                    <h4>4.3 Recertification Audits</h4>
                    <p>Recertification audits activities shall be carried out before the expiry of the certificate to enable renewal of certification on time. When recertification activities are successfully completed prior to the expiry date of the existing certification, the expiry date of the new certification shall be based on the expiry date of the existing certification. The issue date on a new certificate shall be the date of recertification decision.</p>
                    
                    <div class="highlight-box">
                        <h4>4.3.2 Incomplete Recertification</h4>
                        <p>If recertification audit could not be completed or is unable to verify the implementation of corrections and corrective actions for any major nonconformity prior to the expiry date of the certification, then recertification shall not be recommended and the validity of the certification shall not be extended. The client shall be informed and the consequences shall be explained.</p>
                        
                        <h4>4.3.3 Restoration After Expiry</h4>
                        <p>Following expiration of certification, the certification may restore within 6 months provided that the outstanding recertification activities are completed, otherwise at least a stage 2 shall be conducted. The effective date on the certificate shall be on or after the recertification decision and the expiry date shall be based on prior certification cycle.</p>
                    </div>
                    
                    <h4>4.4 Special Audits</h4>
                    <p>Special audits are carried out to respond to changes to the certified system or to investigate complaints against a certified management system. Special audits may also be undertaken in response to an application for modification of scope of a certification already granted (though this may be conducted in conjunction with a planned surveillance audit).</p>

                    <h3 class="section-title">5. Renewing</h3>
                    <p>Renewing of certification is done after the recertification decision the process of which is described in clause 4.3 above. This is done after the three year certification contract period.</p>

                    <h3 class="section-title">6. Suspending</h3>
                    <p>AITF may suspend a certificate for a specified period in the circumstances as follows:</p>
                    <ol type="a">
                        <li>If the client violates contractual obligations or fail to settle the outstanding dues.</li>
                        <li>The Client's management system's persistent or serious failure to meet certification requirements, including requirements of the effectiveness of the management system.</li>
                        <li>Corrective actions to the management system not having been carried out or effectively implemented within the agreed time period</li>
                        <li>The Client does not allow surveillance or re-certification audits to be carried out as per defined frequencies.</li>
                        <li>The client has misused the certificate or the certification logo. Conditions for use of Marks are publicly accessed on <a href="https://www.aitf.co.in" target="_blank">www.aitf.co.in</a></li>
                        <li>The certified client has voluntarily requested a suspension.</li>
                    </ol>
                    
                    <div class="highlight-box">
                        <p><strong>6.3</strong> The suspension period shall not be more than 6 months</p>
                        <p><strong>6.4</strong> Failure to resolve the issues that resulted in the suspension in the time determined by AITF shall lead to notice of withdrawal of the certification or reduction in the scope of certification.</p>
                        <p><strong>6.5</strong> Under suspension, the client's management system certification is temporarily invalid.</p>
                    </div>

                    <h3 class="section-title">7. Restoring</h3>
                    <p>If the required measures for a suspended certification has been implemented and demonstrated effectively by the established deadline, the AITF shall lift the suspension of the certificate and the certification shall be considered restored.</p>

                    <h3 class="section-title">8. Withdrawing</h3>
                    <p>AITF is entitled to terminate or withdraw the certification after giving written notice of intent if:</p>
                    <ul>
                        <li>If periodical audits reveal nonconformities of serious nature.</li>
                        <li>If the certificate holder fails to comply with the due settlement of his financial obligation despite several reminders.</li>
                        <li>If inadequate measures are taken by the certificate holder in case of suspension and Failure to resolve the issues that have resulted in the suspension in a time established by AITF shall result in withdrawal or reduction of the scope of certification.</li>
                        <li>The client terminates the contractual relationship with the AITF</li>
                    </ul>

                    <h3 class="section-title">9. Expanding</h3>
                    <p>In response to an application for expanding the scope of a certification already granted, undertake a review of the application and determine any audit activities necessary to decide whether or not the extension may be granted. This may be conducted in conjunction with a surveillance audit or by a special audit.</p>

                    <h3 class="section-title">10. Reducing</h3>
                    <p>If client requested to do so or during periodical assessment if it is found that the client doesn't having imparted same (scope) activity in existence in such an event scope will be reduce.</p>
                    <p>AITF shall reduce the client's scope of certification to exclude the parts not meeting the requirements, when the certified client has persistently or seriously failed to meet the certification requirements of those parts of the scope of certification. Any such reduction shall be in line with the requirements of the standard used for certification. AITF's decision on reducing the scope of certification shall be communicated to the client, in writing, through letter F 7.38</p>
                    <p>The certificate will be withdrawn in its entirety if client or in multisite case the central office or any of the sites does not fulfill the necessary provision for the maintenance of the certification.</p>
                </div>

                <!-- Document Footer -->
                <div class="document-footer">
                    <p>Document: G 1.11.00.12.04.2017 | Page 1 of 2 | Approved by AITF-MD</p>
                    <p>© AITF Management Certification Limited. All rights reserved.</p>
                </div>

                <!-- Document Actions -->
                <div class="document-actions">
                    <a href="#top" class="btn-document btn-view">
                        <i class="fa fa-arrow-up"></i> Back to Top
                    </a>
                    <a href="text/guidline_g_1_11.pdf" download class="btn-document btn-download">
                        <i class="fa fa-download"></i> Download Full Document
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include './footerSource.php'; ?>

    <script>
        // Get file size dynamically
        fetch('text/guidline_g_1_11.pdf')
            .then(response => {
                const fileSize = response.headers.get('content-length');
                const fileSizeInKB = Math.round(fileSize / 1024);
                document.getElementById('fileSize').textContent = `File size: ${fileSizeInKB} KB`;
            })
            .catch(error => {
                console.error('Error fetching file size:', error);
                document.getElementById('fileSize').textContent = 'File size: N/A';
            });
    </script>
</body>
</html>