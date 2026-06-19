<!DOCTYPE html>
<html lang="en">
    <head>
        <?php include './headSource.php'; ?>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            :root {
                --primary-color: #4361ee;
                --secondary-color: #3f37c9;
                --accent-color: #4cc9f0;
                --text-color: #2b2d42;
                --light-bg: #f8f9ff;
                --gradient: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%);
                --box-shadow: 0 10px 30px rgba(67, 97, 238, 0.15);
            }
            
            body {
                font-family: 'Poppins', sans-serif;
                color: var(--text-color);
                line-height: 1.7;
                background-color: #f9faff;
            }
            
            .certification-section {
                padding: 140px 0 60px;
                margin-top: 0;
                background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
                position: relative;
                overflow: hidden;
            }
            
            .certification-section::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiB2aWV3Qm94PSIwIDAgMTAwIDEwMCI+PHBhdGggZmlsbD0iI2Y4ZjlmZiIgZD0iTTAgMGgxMDB2MTAwSDB6Ii8+PHBhdGggZmlsbD0iI2YwZjRmZiIgZD0iTTAgMGg1MHY1MEgwek01MCA1MGg1MHY1MEg1MHpNMCA1MGg1MHY1MEgweiIvPjwvc3ZnPg==') repeat;
                opacity: 0.5;
                z-index: 0;
            }
            
            .certification-card {
                background: rgba(255, 255, 255, 0.95);
                border-radius: 16px;
                box-shadow: var(--box-shadow);
                padding: 25px 20px;
                margin: 15px 0;
                transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
                position: relative;
                z-index: 1;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.5);
                overflow: hidden;
            }
            
            .certification-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 5px;
                background: var(--gradient);
                transform: scaleX(0);
                transform-origin: left;
                transition: transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);
            }
            
            .certification-card:hover {
                transform: translateY(-8px) scale(1.01);
                box-shadow: 0 20px 40px rgba(67, 97, 238, 0.2);
            }
            
            .certification-card:hover::before {
                transform: scaleX(1);
            }
            
            .certification-header {
                text-align: center;
                margin-bottom: 60px;
                position: relative;
                padding-bottom: 20px;
                z-index: 1;
            }
            .certification-header h2 {
                color: var(--text-color);
                font-weight: 700;
                font-size: 2rem;
                margin: 0 0 15px 0;
                background: var(--gradient);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                display: inline-block;
                position: relative;
                letter-spacing: -0.5px;
                line-height: 1.3;
                padding: 0 10px;
            }
            
            .certification-header h2::after {
                content: '';
                position: absolute;
                width: 60px;
                height: 4px;
                background: var(--gradient);
                bottom: -15px;
                left: 50%;
                transform: translateX(-50%);
                border-radius: 2px;
            }
            
            .certification-content p {
                color: #4a5568;
                line-height: 1.8;
                margin-bottom: 25px;
                font-size: 1.05rem;
                font-weight: 400;
            }
            .criteria-list {
                list-style: none;
                padding: 0;
                margin: 30px 0;
            }
            
            .criteria-list li {
                position: relative;
                padding: 12px 0 12px 50px;
                margin-bottom: 10px;
                color: var(--text-color);
                font-weight: 500;
                border-bottom: 1px dashed rgba(67, 97, 238, 0.1);
                transition: all 0.3s ease;
            }
            
            .criteria-list li:last-child {
                border-bottom: none;
            }
            
            .criteria-list li:hover {
                transform: translateX(5px);
                color: var(--primary-color);
            }
            
            .criteria-list li::before {
                content: '✓';
                font-family: 'Poppins', sans-serif;
                position: absolute;
                left: 0;
                top: 50%;
                transform: translateY(-50%);
                width: 32px;
                height: 32px;
                background: var(--gradient);
                color: white;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
                font-weight: bold;
                box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
                transition: all 0.3s ease;
            }
            
            .criteria-list li:hover::before {
                transform: translateY(-50%) scale(1.1);
                box-shadow: 0 6px 15px rgba(67, 97, 238, 0.4);
            }
            .certificate-image {
                border-radius: 16px;
                overflow: hidden;
                box-shadow: var(--box-shadow);
                transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                position: relative;
                z-index: 1;
                transform-style: preserve-3d;
                perspective: 1000px;
                border: 10px solid white;
            }
            
            .certificate-image::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: var(--gradient);
                opacity: 0;
                z-index: 2;
                mix-blend-mode: multiply;
                transition: opacity 0.4s ease;
                border-radius: 8px;
            }
            
            .certificate-image:hover {
                transform: translateY(-10px) rotateX(5deg) rotateY(5deg);
                box-shadow: 0 25px 50px -12px rgba(67, 97, 238, 0.25);
            }
            
            .certificate-image:hover::before {
                opacity: 0.1;
            }
            
            .certificate-image img {
                width: 100%;
                height: auto;
                display: block;
                transition: transform 0.5s ease;
                transform: translateZ(0);
            }
            
            .certificate-image:hover img {
                transform: scale(1.05);
            }
            .highlight-box {
                background: linear-gradient(135deg, rgba(67, 97, 238, 0.05) 0%, rgba(63, 55, 201, 0.05) 100%);
                border-left: 4px solid var(--primary-color);
                padding: 25px 30px;
                margin: 35px 0;
                border-radius: 12px;
                position: relative;
                overflow: hidden;
                backdrop-filter: blur(5px);
                border: 1px solid rgba(67, 97, 238, 0.1);
                transition: all 0.3s ease;
            }
            
            .highlight-box::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 250, 255, 0.9) 100%);
                z-index: -1;
                opacity: 0.8;
            }
            
            .highlight-box:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 25px rgba(67, 97, 238, 0.1);
                border-left-color: var(--accent-color);
            }
            
            .highlight-box h4 {
                color: var(--primary-color);
                margin-top: 0;
                margin-bottom: 15px;
                font-size: 1.3rem;
                font-weight: 700;
                position: relative;
                display: inline-block;
            }
            
            .highlight-box h4::after {
                content: '';
                position: absolute;
                bottom: -5px;
                left: 0;
                width: 40px;
                height: 2px;
                background: var(--accent-color);
                transition: width 0.3s ease;
            }
            
            .highlight-box:hover h4::after {
                width: 60px;
            }
            .section-title {
                color: var(--text-color);
                margin: 30px 0 20px;
                font-weight: 600;
                font-size: 1.5rem;
                position: relative;
                padding-bottom: 12px;
                letter-spacing: -0.5px;
                line-height: 1.3;
            }
            
            .section-title::after {
                content: '';
                position: absolute;
                width: 60px;
                height: 4px;
                background: var(--gradient);
                bottom: 0;
                left: 0;
                border-radius: 2px;
                transition: width 0.3s ease;
            }
            
            .section-title:hover::after {
                width: 80px;
            }
            /* Improved Touch Targets */
            .criteria-list li,
            .highlight-box,
            .certification-card {
                -webkit-tap-highlight-color: rgba(0, 0, 0, 0.05);
            }

            /* Responsive Design */
            @media (max-width: 1200px) {
                .certification-card {
                    padding: 30px 25px;
                }
            }
            
            @media (max-width: 992px) {
                .certification-header h2 {
                    font-size: 2rem;
                    margin-bottom: 20px;
                }
                
                .section-title {
                    font-size: 1.6rem;
                    margin: 35px 0 20px;
                }
                
                .certification-card {
                    padding: 28px 22px;
                    margin: 20px 0;
                }
                
                .highlight-box {
                    padding: 22px;
                    margin: 22px 0;
                }
            }
            
            @media (max-width: 768px) {
                .certification-section {
                    padding: 40px 0 50px;
                }
                
                .certification-header {
                    margin-bottom: 30px;
                    padding-bottom: 15px;
                }
                
                .certification-header h2 {
                    font-size: 1.75rem;
                    line-height: 1.3;
                }
                
                .section-title {
                    font-size: 1.45rem;
                    margin: 32px 0 18px;
                }
                
                .certification-card {
                    padding: 24px 20px;
                    margin: 16px 0;
                    border-radius: 12px;
                }
                
                .highlight-box {
                    padding: 20px;
                    margin: 20px 0;
                    border-radius: 10px;
                }
                
                .criteria-list li {
                    padding: 12px 0 12px 42px;
                    font-size: 0.95rem;
                    line-height: 1.5;
                    margin-bottom: 8px;
                }
                
                .certification-content p {
                    font-size: 1rem;
                    line-height: 1.7;
                    margin-bottom: 20px;
                }
            }
            
            @media (max-width: 576px) {
                .certification-header h2 {
                    font-size: 1.6rem;
                    letter-spacing: -0.03em;
                }
                
                .section-title {
                    font-size: 1.35rem;
                    margin: 28px 0 16px;
                    padding-bottom: 10px;
                }
                
                .certification-card {
                    padding: 22px 18px;
                    margin: 14px -10px;
                    border-radius: 10px;
                    width: calc(100% + 20px);
                }
                
                .highlight-box {
                    padding: 18px;
                    margin: 18px 0;
                    border-radius: 8px;
                }
                
                .criteria-list li {
                    padding: 10px 0 10px 38px;
                    font-size: 0.93rem;
                    line-height: 1.5;
                }
                
                .certification-content p {
                    font-size: 0.97rem;
                    line-height: 1.65;
                    margin-bottom: 18px;
                }
                
                .container {
                    padding-left: 15px;
                    padding-right: 15px;
                }
            }
            
            @media (max-width: 400px) {
                .certification-header h2 {
                    font-size: 1.5rem;
                }
                
                .section-title {
                    font-size: 1.3rem;
                }
                
                .certification-card {
                    padding: 20px 16px;
                    margin: 12px -10px;
                }
                
                .criteria-list li {
                    padding: 9px 0 9px 36px;
                    font-size: 0.9rem;
                }
            }
            
            /* Animation */
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .animate-fade-in-up {
                animation: fadeInUp 0.6s ease-out forwards;
            }
            
            /* Add some delay for staggered animations */
            .delay-1 { animation-delay: 0.1s; }
            .delay-2 { animation-delay: 0.2s; }
            .delay-3 { animation-delay: 0.3s; }
            .delay-4 { animation-delay: 0.4s; }
        </style>
    </head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <head>
        <?php include './headerSource.php'; ?>
    </head>
    <!-- Start Banner -->
    <div class="ulockd-home-slider">
    </div>
    <div class="certification-section">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="certification-header text-center mb-5">
                        <h1 class="fw-bold mb-4" style="color: #ffffff; font-family: 'Montserrat', 'Poppins', sans-serif; font-size: 2.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; position: relative; display: inline-block; padding-bottom: 15px;">
                            <span style="position: relative; z-index: 1;">
                                CERTIFICATION CRITERIA
                            </span>
                            <span style="position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 120px; height: 4px; background: #ffffff; border-radius: 2px;"></span>
                        </h1>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="certification-card">
                        <div class="certification-content">
                            <p>AITF establishes a Certification Decision Committee (CDC) for making decisions regarding the granting of certificates. The CDC includes the Director, Certification Manager, auditors, and experts working with AITF-MCL to consider specific recommendations related to granting, maintaining, extending, suspending, and withdrawing certification.</p>
                            <div class="highlight-box">
                                <p>Members of the CDC include technical personnel, and all CDC members are independent from the auditing activity.</p>
                                <p>Audit packs forwarded by lead auditors for Initial/Re-Certification Audit/Surveillance/Special Audit are reviewed by the Certificate Decision Committee (CDC).</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="certificate-image text-center p-3">
                        <img src="aitfImages/certified-iso-01.png" alt="ISO Certification" class="img-fluid" style="max-height: 300px; width: auto; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);" onerror="this.src='aitfImages/placeholder-certificate.jpg'" />
                    </div>
                </div>
            </div>
        </div>
        <!-- end section -->

        <div class="section" style="padding: 60px 0;">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="certification-card">
                            <h3 class="section-title">CDC Review Process</h3>
                            <p>The CDC, an independent and competent authority comprising the DIRECTOR, CERTIFICATION MANAGER, and qualified auditors who are not members of the audit team, reviews the audit pack along with contract review records to ensure:</p>
                            
                            <ul class="criteria-list">
                                <li>Assessment has been conducted to cover the scope of certification.</li>
                                <li>Assessment has covered all the requirements of the audit program.</li>
                                <li>Audit checklist has been completed with adequate details by each auditor.</li>
                                <li>Auditor/technical experts audit respective processes based upon the competence as per the audit program defined in the Assessment process.</li>
                                <li>Assessment conducted provides confidence in the justification for recommendation for certification.</li>
                                <li>Audit team has reviewed, accepted, and verified the effectiveness of correction and corrective actions for all non-conformities which represent:</li>
                                <li>Failure to fulfill one or more requirements of the management system standard or a situation that raises significant doubt about the ability of the client's management system to achieve its intended outputs.</li>
                                <li>Client's planned correction/corrective action for any other nonconformity is reviewed and accepted by the audit team.</li>
                                <li>Any other relevant information is considered.</li>
                                <li>If any branch/site covered under the scope of certification has a nonconformity, certification shall be defined to the whole network of listed sites pending satisfactory corrective action.</li>
                                <li>Independence of audit member is maintained.</li>
                            </ul>
                        </div>
                                <div class="highlight-box">
                                    <h4>For Scope Extension Audits</h4>
                                    <p>If the review is for a scope extension audit, the following checkpoints need to be considered:</p>
                                    <h1 class="section-title">Certification <span>Criteria</span></h1>
                        <p class="section-subtitle">Understanding the requirements for ISO certification</p>
                    </div>
                </div>
                <div class="col-12">
                    <div class="certification-card">
                        <ul class="criteria-list">
                                        <li>Assessment has been conducted to cover the additional scope of certification</li>
                                        <li>Audit checklist has been completed with adequate details by each auditor for additional scope</li>
                                        <li>Assessment conducted provides confidence in the justification for recommendation for certification of additional scope</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end section -->

        <div class="section" style="background: #f8f9fa; padding: 60px 0;">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="certification-card">
                            <h3 class="section-title">CERTIFICATE OF COMPLIANCE (COC)</h3>
                            <p>Certificate of Compliance is issued to provide confidence in the product, process, and services of the client organization.</p>
                            
                            <div class="highlight-box">
                                <p><strong>Note:</strong> The Certificate of Compliance shall be issued within a time frame of one month, unless a major concern is expressed by the CDC (Certification Decision Committee).</p>
                            </div>
                            
                            <p>The Certificate of Compliance (Certificate of Registration) shall include, but is not limited to, the following information:</p>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="criteria-list">
                                        <li>Certificate Number</li>
                                        <li>Scope of Certificate</li>
                                        <li>Physical location of the client organization</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="criteria-list">
                                        <li>Registration Date</li>
                                        <li>Condition for validity</li>
                                        <li>Certificate Revision</li>
                                    </ul>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        
        <div class="section" style="padding: 60px 0;">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="certification-card">
                            <h3 class="section-title">SUSPENSION</h3>
                            <div class="certification-content">
                                <p>The certificate may be suspended for the following reasons:</p>
                                
                                <ul class="criteria-list">
                                    <li>Requested corrective actions are not performed within agreed time limits.</li>
                                    <li>Improper use of the certificate of conformity or accreditation mark, e.g., misleading publication or advertisement.</li>
                                    <li>The holder of the certificate neglects to inform AITF about matters of vital importance for the certified management system.</li>
                                    <li>Failure to settle the outstanding dues due to AITF.</li>
                                </ul>
                                
                                <div class="highlight-box">
                                    <p>An official suspension of the certificate will be confirmed in writing to the certificate holder. AITF shall indicate under what conditions the suspension will be removed, e.g., corrective actions to be taken. The accreditation authority will be informed.</p>
                                    <p>At the end of the suspension period, AITF will determine if the indicated conditions for re-instituting the certificate have been fulfilled. On fulfillment, the suspension shall be removed by notifying the certificate holder that the certificate has been reinstated.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                    </div>
                </div>
            </div>
        </div>
        
        <div class="section" style="background: #f8f9fa; padding: 60px 0;">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div class="certification-card">
                            <h3 class="section-title">WITHDRAWAL</h3>
                            <div class="certification-content">
                                <p>The certificate may be withdrawn for the following reasons:</p>
                                
                                <ul class="criteria-list">
                                    <li>If periodical audits reveal nonconformities of serious nature.</li>
                                    <li>If the certificate holder fails to comply with the due settlement of their financial obligation.</li>
                                    <li>If inadequate measures are taken by the certificate holder in case of suspension.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 layout_padding_2">
                        <div class="full">
                            <div class="full">
                                <h3><b>CANCELLATION</b></h3>
                            </div>
                            <div class="full">
                                <p>The certificate will be cancelled if the certificate holder does not wish to prolong the agreement. AITF will withdraw/ cancel the certificate by informing the certificate holder in writing.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 layout_padding_2">
                        <div class="full">
                            <h3 class="section-title">REDUCING SCOPE OF CERTIFICATION</h3>
                            <div class="certification-content">
                                <p>If a client requests it or if during assessment it is found that the client no longer maintains the activities covered by the current scope, the scope will be reduced during any type of assessment.</p>
                                
                                <div class="highlight-box">
                                    <p>The above-established criteria for suspension, withdrawal, cancellation, and reducing the scope of certification shall be followed. The Certificate will be withdrawn in its entirety if the client, or in the case of a multisite organization, the central office or any of the sites does not fulfill the necessary provisions for the maintenance of the certification.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end section -->
        <?php include './footerSource.php'; ?>
    </body>
</html>