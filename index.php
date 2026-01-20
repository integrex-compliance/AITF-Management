<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
    <!-- Basic -->
    <head>
        <?php include './headSource.php'; ?>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="/perfect-learn/css/methodology-styles.css">
        <style>
            /* Box Navigation */
            /* Modern Navigation Container */
            .box-nav-container {
                display: flex;
                justify-content: center;
                flex-wrap: wrap;
                gap: 25px;
                margin: 0 auto;
                max-width: 1300px;
                padding: 0 30px;
                transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
                perspective: 1000px;
            }
            
            /* Box Navigation Styles */
            .box-nav-container {
                display: flex;
                justify-content: center;
                flex-wrap: wrap;
                gap: 14px;
                margin: 24px auto 10px;
                padding: 0 16px;
                width: 100%;
                position: relative;
                z-index: 5;
                max-width: 1200px;
                margin-left: auto;
                margin-right: auto;
                transform: none;
            }

            /* Stylish Navigation Items */
            .box-nav-item {
                width: 220px;
                background: linear-gradient(145deg, rgba(255,255,255,0.12), rgba(255,255,255,0.05));
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border-radius: 20px;
                padding: 30px 20px;
                border: 1px solid rgba(255, 255, 255, 0.15);
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2), 0 5px 15px rgba(0,0,0,0.1);
                transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
                cursor: pointer;
                position: relative;
                overflow: hidden;
                transform: translateY(0) rotateX(0) rotateY(0);
                transform-style: preserve-3d;
                will-change: transform, box-shadow;
                text-align: center;
                cursor: pointer;
                transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
                box-shadow: 0 4px 15px rgba(26, 58, 106, 0.1);
                border: 2px solid rgba(26, 58, 106, 0.1);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                height: 100px;
                position: relative;
                z-index: 5;
                overflow: hidden;
            }
            
            .box-nav-item {
                background: #ffffff;
                color: #0f172a;
                transform: translateY(0);
                box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
                border: 1px solid rgba(148, 163, 184, 0.35);
            }
            
            /* Active State */
            .box-nav-item.active {
                transform: translateY(-4px);
                box-shadow: 0 18px 35px rgba(15, 23, 42, 0.18);
                border-width: 2px;
                animation: none;
            }
            
            /* Icon Styling */
            .box-nav-icon {
                font-size: 46px;
                margin-bottom: 20px;
                color: #f97316;
                transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
                position: relative;
                z-index: 2;
                text-shadow: none;
                background: none;
                -webkit-background-clip: initial;
                -webkit-text-fill-color: currentColor;
                display: inline-block;
                padding: 10px;
            }
            
            .box-nav-item:hover .box-nav-icon {
                transform: scale(1.1);
                color: #2a5a9a;
            }
            
            /* Title Styling */
            .box-nav-title {
                font-family: 'Poppins', sans-serif;
                font-size: 16px;
                font-weight: 600;
                color: #0f172a;
                line-height: 1.6;
                margin: 10px 0 0;
                position: relative;
                z-index: 2;
                text-shadow: none;
                transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
                letter-spacing: 0.5px;
                text-transform: uppercase;
                background: none;
                -webkit-background-clip: initial;
                -webkit-text-fill-color: currentColor;
                transition: color 0.3s ease;
                position: relative;
                z-index: 2;
            }
            
            .box-nav-item .box-nav-icon,
            .box-nav-item .box-nav-title {
                color: inherit;
            }
            
            /* Add a subtle shine effect on hover */
            /* Shine Effect */
            .box-nav-item::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(
                    90deg,
                    transparent,
                    rgba(255, 255, 255, 0.1),
                    rgba(255, 255, 255, 0.3),
                    rgba(255, 255, 255, 0.1),
                    transparent
                );
                transition: 0.8s;
                animation: shine 8s infinite;
            }
            
            @keyframes shine {
                0% { transform: translateX(-100%) skewX(-20deg); }
                20% { transform: translateX(200%) skewX(-20deg); }
                100% { transform: translateX(200%) skewX(-20deg); }
            }
            
            .box-nav-item:hover::before {
                left: 100%;
            }
            
            /* Responsive adjustments */
            /* Responsive Design */
            @media (max-width: 1200px) {
                .box-nav-item {
                    width: 180px;
                    padding: 25px 15px;
                }
                
                .box-nav-icon {
                    font-size: 38px;
                }
                
                .box-nav-title {
                    font-size: 15px;
                }
            }
            
            @media (max-width: 992px) {
                .box-nav-container {
                    gap: 15px;
                }
                
                .box-nav-item {
                    width: 160px;
                    padding: 20px 12px;
                    border-radius: 16px;
                }
                
                .box-nav-icon {
                    font-size: 36px;
                    margin-bottom: 15px;
                }
                
                .box-nav-title {
                    font-size: 14px;
                    line-height: 1.5;
                }
            }
            
            @media (max-width: 768px) {
                .box-nav-container {
                    gap: 15px;
                    transform: none;
                    margin-top: 24px;
                }
                
                .box-nav-item {
                    width: calc(50% - 10px);
                    max-width: 180px;
                    padding: 20px 15px;
                }
            }
            
            @media (max-width: 992px) {
                .box-nav-container {
                    margin: 22px 0 14px;
                }
                
                .box-nav-item {
                    width: calc(50% - 8px);
                    padding: 18px 10px;
                    border-radius: 14px;
                }
                
                .box-nav-icon {
                    font-size: 32px;
                    margin-bottom: 12px;
                }
                
                .box-nav-title {
                    font-size: 13px;
                    line-height: 1.4;
                }
            }
            
            @media (max-width: 768px) {
                .management-slider-section {
                    padding: 32px 0 44px;
                }
                
                .slider-container {
                    min-height: 280px;
                    padding: 0 15px;
                }
                
                .slide-content {
                    padding: 22px 16px;
                    min-height: 230px;
                    width: 95%;
                    border-radius: 20px;
                }
                
                .slide h3 {
                    font-size: 22px;
                    margin-bottom: 15px;
                    padding-bottom: 10px;
                    letter-spacing: 0.5px;
                }
                
                .slide-description {
                    font-size: 14px;
                    margin-bottom: 20px;
                    padding: 15px 20px;
                    line-height: 1.6;
                    border-radius: 12px;
                }
                
                .slide-icon {
                    width: 70px;
                    height: 70px;
                    margin-top: 20px;
                    font-size: 28px;
                    margin-bottom: 15px;
                    border-radius: 20px;
                }
                
                .slider-btn {
                    padding: 10px 20px;
                    font-size: 12px;
                    min-width: 140px;
                    margin-top: 20px;
                }
                
                .slider-arrow {
                    width: 44px;
                    height: 44px;
                    font-size: 16px;
                    top: 45%;
                }
                
                .prev-arrow {
                    left: 5px;
                }
                
                .next-arrow {
                    right: 5px;
                }
                
                .slider-dots {
                    bottom: 14px;
                    padding: 10px 15px;
                    gap: 8px;
                }
                
                .slider-dot {
                    width: 12px;
                    height: 12px;
                }
                
                .box-nav-container {
                    gap: 8px;
                    padding: 0 10px;
                    transform: none;
                    margin: 24px 0 15px;
                }
                
                .box-nav-item {
                    width: calc(50% - 6px);
                    padding: 12px 8px;
                    border-radius: 12px;
                    height: 80px;
                }
                
                .box-nav-icon {
                    font-size: 24px;
                    margin-bottom: 6px;
                    padding: 3px;
                }
                
                .box-nav-title {
                    font-size: 11px;
                    line-height: 1.2;
                    letter-spacing: 0.2px;
                }
                
                /* Enhanced touch interactions for mobile */
                .slide-content {
                    transform: translateZ(0) !important;
                }
                
                .slide-content:hover {
                    transform: translateY(-10px) scale(1.01) !important;
                }
                
                .slide-content:active {
                    transform: translateY(-5px) scale(0.99) !important;
                    transition: transform 0.1s ease !important;
                }
            }
            
            @media (max-width: 480px) {
                .management-slider-section {
                    padding: 22px 0 34px;
                }
                
                .slide-content {
                    padding: 20px 12px;
                    min-height: 210px;
                }
                
                .slide h3 {
                    font-size: 18px;
                    margin-bottom: 12px;
                }
                
                .slide-description {
                    font-size: 13px;
                    padding: 12px 15px;
                    margin-bottom: 15px;
                }
                
                .slide-icon {
                    width: 60px;
                    height: 60px;
                    margin-top: 15px;
                    font-size: 24px;
                    margin-bottom: 12px;
                }
                
                .slider-btn {
                    padding: 8px 16px;
                    font-size: 11px;
                    min-width: 120px;
                }
                
                .slider-arrow {
                    width: 45px;
                    height: 45px;
                    font-size: 16px;
                }
                
                .box-nav-item {
                    width: calc(50% - 4px);
                    padding: 10px 6px;
                    height: 70px;
                    border-radius: 10px;
                }
                
                .box-nav-icon {
                    font-size: 20px;
                    margin-bottom: 4px;
                }
                
                .box-nav-title {
                    font-size: 10px;
                    line-height: 1.1;
                }
            }
            
            .slider-dots {
                position: absolute;
                bottom: 50px;
                left: 0;
                right: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 10px;
                z-index: 10;
                padding: 15px 20px;
                background: rgba(255, 255, 255, 0.7);
                backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);
                border-radius: 50px;
                width: max-content;
                margin: 0 auto;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
                border: 1px solid rgba(255, 255, 255, 0.8);
            }
            
            .slider-container.loaded .slider-dots {
                opacity: 1;
                transform: translateY(0);
            }

            .login-error-banner {
                position: fixed;
                top: 80px;
                left: 50%;
                transform: translateX(-50%) translateY(-10px);
                padding: 12px 18px;
                border-radius: 14px;
                background: linear-gradient(135deg, #fee2e2, #fecaca);
                border: 1px solid #fecaca;
                box-shadow: 0 14px 35px rgba(127, 29, 29, 0.35);
                display: none;
                align-items: center;
                gap: 12px;
                z-index: 9999;
                max-width: 420px;
                width: calc(100% - 32px);
                opacity: 0;
                transition: opacity 0.2s ease, transform 0.2s ease;
            }

            .login-error-banner.visible {
                display: flex;
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }

            .login-error-icon {
                width: 32px;
                height: 32px;
                border-radius: 999px;
                background: #b91c1c;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fee2e2;
                font-weight: 700;
                font-size: 18px;
            }

            .login-error-text-main {
                font-size: 14px;
                font-weight: 600;
                color: #7f1d1d;
            }

            .login-error-text-sub {
                font-size: 12px;
                color: #b91c1c;
                margin-top: 2px;
            }

            .login-error-close {
                margin-left: auto;
                background: transparent;
                border: none;
                color: #7f1d1d;
                font-size: 16px;
                cursor: pointer;
                padding: 0 4px;
                line-height: 1;
            }
        </style>
    </head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <head>
        <?php include './headerSource.php'; ?>
    </head>
    <script type="text/javascript">
/// some script
// jquery ready start
        $(document).ready(function () {
<?php if (isset($_SESSION['login_error']) && $_SESSION['login_error']) { unset($_SESSION['login_error']); ?>
        $('#login-error-banner').addClass('visible');
<?php } ?>

            $('.login-error-close').on('click', function () {
                $('#login-error-banner').fadeOut(200);
            });

            // jQuery code
            //////////////////////// Prevent closing from click inside dropdown
            $(document).on('hover', '.dropdown-menu', function (e) {
                e.stopPropagation();
            });
        }); // jquery end
    </script>

    <div id="login-error-banner" class="login-error-banner">
        <div class="login-error-icon">!</div>
        <div>
            <div class="login-error-text-main">Login failed</div>
            <div class="login-error-text-sub">Invalid username or password. Please try again.</div>
        </div>
        <button type="button" class="login-error-close" aria-label="Close">&times;</button>
    </div>

    <style>
        .hero-section {
            position: relative;
            z-index: 1;
        }
        .wave-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }
        .wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 200%;
            height: 100%;
            background-repeat: repeat-x;
            background-size: 50% 100%;
        }
        .wave1 {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%231d4ed8" fill-opacity="0.2" d="M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,181.3C960,181,1056,235,1152,234.7C1248,235,1344,181,1392,154.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            animation: wave 15s linear infinite;
        }
        .wave2 {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%231d4ed8" fill-opacity="0.3" d="M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,181.3C960,181,1056,235,1152,234.7C1248,235,1344,181,1392,154.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            animation: wave 12s linear infinite reverse;
        }
        .wave3 {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%231d4ed8" fill-opacity="0.4" d="M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,181.3C960,181,1056,235,1152,234.7C1248,235,1344,181,1392,154.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
            background-size: 50% 90%;
            animation: wave 20s linear infinite;
            animation-delay: -5s;
        }
        @keyframes wave {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
    </style>
    <div class="modern-hero-section" style="padding-top: 80px;">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text" style="margin-top: 20px;">
                    <h1 style="margin-top: 0;">Welcome to AITF</h1>
                    <h2>Your Trusted Certification Partner</h2>
                    <div class="hero-highlights" style="flex-wrap: nowrap;">
                        <span class="highlight-badge">
                            <i class="fas fa-medal" style="color: #FFD700;"></i>
                            <span>NABCB Accredited</span>
                        </span>
                        <span class="highlight-badge iso-cluster">
                            <i class="fas fa-shield-alt"></i>
                            <div class="iso-chip-row">
                                <span class="iso-chip">ISO 9001</span>
                                <span class="iso-chip">ISO 45001</span>
                                <span class="iso-chip">ISO 14001</span>
                            </div>
                        </span>
                    </div>
                    <p class="hero-description">Empowering organizations through accredited, authentic, impartial, and ethical certification services for quality management standards such as QMS, EMS, and OHSMS.</p>
                    <div class="hero-cta" style="width: 100%; justify-content: flex-end; margin-top: 20px;">
                        <a href="text/audit_application_form.pdf" class="cta-button secondary " style="margin-left: auto;" download>
                            <i class="fas fa-arrow-down"></i>
                            <span> Download Application Form</span>
                        </a>
                        <a href="application.php#online-application-form" class="cta-button primary">
                            <i class="fas fa-edit"></i>
                            <span>Fill Application Form Online</span>
                        </a>
                    </div>
                </div>
                <div class="dashboard-certifications">
                    <div class="certification-border">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <div class="dashboard-header">
                        <h3>OUR CERTIFICATIONS</h3>
                        <div class="dashboard-badge">
                            <i class="fas fa-medal" style="color: #FFD700; margin-right: 5px;"></i>
                            <span>Accredited</span>
                        </div>
                    </div>
                    <div class="certification-dashboard">
                        <a href="contactUS.php" style="text-decoration: none; color: inherit; display: block;">
                        <div class="certification-card" style="--card-color: #c64bff">
                            <div class="cert-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="cert-details">
                                <span class="cert-code">ISO 9001</span>
                                <span class="cert-name">QMS</span>
                            </div>
                            <div class="cert-status">
                                <span class="status-dot"></span>
                                <span>Apply</span>
                            </div>
                        </div>
                        </a>
                        <a href="contactUS.php" style="text-decoration: none; color: inherit; display: block;">
                        <div class="certification-card" style="--card-color: #4f83ff">
                            <div class="cert-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="cert-details">
                                <span class="cert-code">ISO 45001</span>
                                <span class="cert-name">OHSMS</span>
                            </div>
                            <div class="cert-status">
                                <span class="status-dot"></span>
                                <span>Apply</span>
                            </div>
                        </div>
                        </a>
                        <a href="contactUS.php" style="text-decoration: none; color: inherit; display: block;">
                        <div class="certification-card" style="--card-color: #8a5cff">
                            <div class="cert-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="cert-details">
                                <span class="cert-code">ISO 14001</span>
                                <span class="cert-name">EMS</span>
                            </div>
                            <div class="cert-status">
                                <span class="status-dot"></span>
                                <span>Apply</span>
                            </div>
                        </div>
                        </a>
                    </div>
                   
                    </div>
                </div>
                
                <!-- Audit Application Flow Section -->
                <div class="audit-flow-section">
                    <div class="flow-header">
                        <h3 class="flow-title">
                            <i class="fas fa-stream"></i>
                            Audit Application Process Flow
                        </h3>
                        <div class="flow-badge">
                            <span class="pulse-dot"></span>
                            <span>Live Process</span>
                        </div>
                    </div>
                    
                    <div class="audit-flow-container">
                        <div class="flow-step" data-step="1">
                            <div class="step-header" onclick="toggleStep(this)">
                                <div class="step-number">1</div>
                                <div class="step-content">
                                    <h4>Audit Application</h4>
                                    <p>Initial application submission</p>
                                </div>
                                <div class="step-toggle">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <div class="step-details">
                                <div class="step-info">
                                    <h5>Key Application Requirements</h5>
                                    <ul>
                                        <li>Download application form from www.aitf.co.in (Downloads section)</li>
                                        <li>Available in PDF and MS Word formats</li>
                                        <li>Fill client requirements and certification details</li>
                                        <li>Provide activities carried out by client</li>
                                        <li>Include number of employees and sites information</li>
                                        <li>Add certification-specific information</li>
                                        <li>Application must be stamped and signed</li>
                                        <li>Submit by authorized person only</li>
                                        <li>Send via mail, courier, or handover to AITF office</li>
                                    </ul>
                                </div>
                                <div class="step-timeline">
                                    <span class="timeline-badge">2-3 Days</span>
                                </div>
                            </div>
                            <div class="step-connector"></div>
                        </div>
                        
                        <div class="flow-step" data-step="2">
                            <div class="step-header" onclick="toggleStep(this)">
                                <div class="step-number">2</div>
                                <div class="step-content">
                                    <h4>Application Review</h4>
                                    <p>Comprehensive evaluation process</p>
                                </div>
                                <div class="step-toggle">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <div class="step-details">
                                <div class="step-info">
                                    <h5>Application Review Process</h5>
                                    <ul>
                                        <li>Review of application information by AITF-MCL</li>
                                        <li>Economic offer preparation for certification</li>
                                        <li>Based on required man-days for auditing</li>
                                        <li>Analysis of organization scope criticality</li>
                                        <li>Evaluation of sites to be audited</li>
                                        <li>Competency requirements analysis</li>
                                        <li>Appointing competent auditor for assessment</li>
                                        <li>Detailed cost estimation and timeline</li>
                                    </ul>
                                </div>
                                <div class="step-timeline">
                                    <span class="timeline-badge">5-7 Days</span>
                                </div>
                            </div>
                            <div class="step-connector"></div>
                        </div>
                        
                        <div class="flow-step" data-step="3">
                            <div class="step-header" onclick="toggleStep(this)">
                                <div class="step-number">3</div>
                                <div class="step-content">
                                    <h4>Certification Proposal</h4>
                                    <p>Detailed certification plan</p>
                                </div>
                                <div class="step-toggle">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <div class="step-details">
                                <div class="step-info">
                                    <h5>Certification Proposal Details</h5>
                                    <ul>
                                        <li>Review of completed questionnaire information</li>
                                        <li>AITF reviews available scopes with AITF-MCL</li>
                                        <li>Preparation of detailed proposal/agreement</li>
                                        <li>Letter for registration service agreement included</li>
                                        <li>Sent along with application for registration</li>
                                        <li>Specifies surveillance visit frequency</li>
                                        <li>Terms and conditions for certification grant</li>
                                        <li>Maintenance of certification requirements</li>
                                        <li>Certification criteria clearly defined</li>
                                    </ul>
                                </div>
                                <div class="step-timeline">
                                    <span class="timeline-badge">3-5 Days</span>
                                </div>
                            </div>
                            <div class="step-connector"></div>
                        </div>
                        
                        <div class="flow-step" data-step="4">
                            <div class="step-header" onclick="toggleStep(this)">
                                <div class="step-number">4</div>
                                <div class="step-content">
                                    <h4>Certification Service Agreement</h4>
                                    <p>Service terms and conditions</p>
                                </div>
                                <div class="step-toggle">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <div class="step-details">
                                <div class="step-info">
                                    <h5>Service Agreement Process</h5>
                                    <ul>
                                        <li>Client acceptance of proposal/agreement required</li>
                                        <li>Submit signed agreement along with application fees</li>
                                        <li>Receipt of agreement enables client registration</li>
                                        <li>Unique identification number allotted to organization</li>
                                        <li>Application for registration received and reviewed</li>
                                        <li>Confirms exact client company name display</li>
                                        <li>Verifies site address details</li>
                                        <li>Defines scope description (products/services)</li>
                                        <li>Certification audit contract made and sent</li>
                                    </ul>
                                </div>
                                <div class="step-timeline">
                                    <span class="timeline-badge">1-2 Days</span>
                                </div>
                            </div>
                            <div class="step-connector"></div>
                        </div>
                        
                        <div class="flow-step" data-step="5">
                            <div class="step-header" onclick="toggleStep(this)">
                                <div class="step-number">5</div>
                                <div class="step-content">
                                    <h4>Contract Review</h4>
                                    <p>Legal and compliance review</p>
                                </div>
                                <div class="step-toggle">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <div class="step-details">
                                <div class="step-info">
                                    <h5>Contract Review Process</h5>
                                    <ul>
                                        <li>Review of signed service agreement received</li>
                                        <li>Analysis of contract terms and conditions</li>
                                        <li>Identify required changes if any</li>
                                        <li>Revision of contract carried out if needed</li>
                                        <li>Client acceptance of contract revisions</li>
                                        <li>Proceed with assessment planning after acceptance</li>
                                        <li>Final contract approval and documentation</li>
                                    </ul>
                                </div>
                                <div class="step-timeline">
                                    <span class="timeline-badge">2-3 Days</span>
                                </div>
                            </div>
                            <div class="step-connector"></div>
                        </div>
                        
                        <div class="flow-step" data-step="6">
                            <div class="step-header" onclick="toggleStep(this)">
                                <div class="step-number">6</div>
                                <div class="step-content">
                                    <h4>Assessment Planning</h4>
                                    <p>Strategic assessment schedule</p>
                                </div>
                                <div class="step-toggle">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <div class="step-details">
                                <div class="step-info">
                                    <h5>Assessment Planning Process</h5>
                                    <ul>
                                        <li>Analysis of auditee organization details (Scope, Name, Address)</li>
                                        <li>Clear understanding between client & AITF-MCL requirements</li>
                                        <li>Audit language requirement assessment</li>
                                        <li>Review of client management system if previously certified</li>
                                        <li>Arrangement of AITF-MCL resources for certification service</li>
                                        <li>Evaluation of assessment readiness of the applicant</li>
                                        <li>Comprehensive assessment plan preparation</li>
                                        <li>Resource allocation and scheduling</li>
                                        <li>Final assessment documentation</li>
                                    </ul>
                                </div>
                                <div class="step-timeline">
                                    <span class="timeline-badge">5-7 Days</span>
                                </div>
                            </div>
                            <div class="step-connector"></div>
                        </div>
                        
                        <div class="flow-step" data-step="7">
                            <div class="step-header" onclick="toggleStep(this)">
                                <div class="step-number">7</div>
                                <div class="step-content">
                                    <h4>Audit Notification with Assignment of Auditor</h4>
                                    <p>Auditor assignment and notification</p>
                                </div>
                                <div class="step-toggle">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <div class="step-details">
                                <div class="step-info">
                                    <h5>Audit Notification Process</h5>
                                    <ul>
                                        <li>AITF-MCL sends audit notification to client</li>
                                        <li>Audit planning details included in notification</li>
                                        <li>Assignment of qualified auditor</li>
                                        <li>Client acceptance required for auditor assignment</li>
                                        <li>Verification of no conflict of interest</li>
                                        <li>Client confirmation for audit scheduling</li>
                                        <li>Final audit date confirmation</li>
                                        <li>Audit team communication established</li>
                                    </ul>
                                </div>
                                <div class="step-timeline">
                                    <span class="timeline-badge">2-3 Days</span>
                                </div>
                            </div>
                            <div class="step-connector"></div>
                        </div>
                        
                        <div class="flow-step" data-step="8">
                            <div class="step-header" onclick="toggleStep(this)">
                                <div class="step-number">8</div>
                                <div class="step-content">
                                    <h4>Stage I Audit</h4>
                                    <p>Initial assessment phase</p>
                                </div>
                                <div class="step-toggle">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <div class="step-details">
                                <div class="step-info">
                                    <h5>Stage I Audit Process</h5>
                                    <ul>
                                        <li>Determine organization's readiness for Stage 2 audit</li>
                                        <li>Review of client's documentation and quality system</li>
                                        <li>Review Quality Manual / Policy documents</li>
                                        <li>Examine Procedures / Guidelines / Work Instructions</li>
                                        <li>Check all required documentation is available</li>
                                        <li>Verify information consistency and controlled state</li>
                                        <li>Confirm documentation distributed for implementation</li>
                                        <li>Partial onsite evaluation of internal audits</li>
                                        <li>Assess management review planning and performance</li>
                                        <li>Evaluate management system implementation level</li>
                                        <li>Submit findings & advice in written report</li>
                                    </ul>
                                </div>
                                <div class="step-timeline">
                                    <span class="timeline-badge">3-5 Days</span>
                                </div>
                            </div>
                            <div class="step-connector"></div>
                        </div>
                        
                        <div class="flow-step" data-step="9">
                            <div class="step-header" onclick="toggleStep(this)">
                                <div class="step-number">9</div>
                                <div class="step-content">
                                    <h4>Stage II Audit</h4>
                                    <p>Final certification audit</p>
                                </div>
                                <div class="step-toggle">
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                            </div>
                            <div class="step-details">
                                <div class="step-info">
                                    <h5>Stage II Audit Process</h5>
                                    <ul>
                                        <li>Conducted onsite to evaluate implementation effectiveness</li>
                                        <li>Assessment of client's management system effectiveness</li>
                                        <li>Audit findings categorized into Minor Non-Conformances</li>
                                        <li>Audit findings categorized into Major Non-Conformances</li>
                                        <li>Minor Non-Compliance: Isolated deficiency cases</li>
                                        <li>Minor: Does not affect product conformity or customer satisfaction</li>
                                        <li>Major Non-Compliance: Total lapse of applicable requirements</li>
                                        <li>Major: Critical to management system operation</li>
                                        <li>Assessment audit after Stage 1 inadequacies removed</li>
                                        <li>Organization demonstrates compliance to international standard</li>
                                        <li>Auditors plan and conduct the assessment audit</li>
                                    </ul>
                                </div>
                                <div class="step-timeline">
                                    <span class="timeline-badge">5-7 Days</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flow-progress">
                        <div class="progress-bar">
                            <div class="progress-fill"></div>
                        </div>
                        <div class="progress-text">
                            <span class="current-step">Step 1</span>
                            <span class="total-steps">of 9</span>
                        </div>
                    </div>
                </div>
                
                <!-- NABCB Accreditation Section -->
                <div class="container mt-4 mb-5">
                    <div class="row justify-content-center">
                        <div class="col-lg-10">
                            <div class="accreditation-section p-4 p-lg-5 text-white rounded-3 position-relative overflow-hidden" style="
                                background: linear-gradient(135deg, #0f172a 0%, #1e293b 40%, #1e3a8a 100%);
                                border: none;
                                position: relative;
                                box-shadow: 
                                    0 25px 80px rgba(15, 23, 42, 0.6),
                                    0 10px 40px rgba(30, 58, 138, 0.4),
                                    inset 0 1px 0 rgba(255, 255, 255, 0.1);
                                transform: translateY(0);
                                transition: all 0.6s cubic-bezier(0.25, 0.8, 0.25, 1);
                                border-radius: 24px;
                            " onmouseover="this.style.transform='translateY(-12px) scale(1.02)'; this.style.boxShadow='0 35px 100px rgba(15, 23, 42, 0.8), 0 15px 50px rgba(30, 58, 138, 0.5)';" onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 25px 80px rgba(15, 23, 42, 0.6), 0 10px 40px rgba(30, 58, 138, 0.4)';">
                                
                                <!-- Animated Top Border -->
                                <div class="position-absolute top-0 start-0 w-100" style="height: 6px; background: linear-gradient(90deg, #f97316, #fb923c, #fbbf24, #f97316); background-size: 300% 100%; animation: gradientFlow 3s ease-in-out infinite; z-index: 10; box-shadow: 0 4px 20px rgba(249, 115, 22, 0.6);"></div>
                                
                                <!-- Enhanced Background Pattern -->
                                <div class="position-absolute top-0 start-0 w-100 h-100 opacity-05" style="
                                    background-image: 
                                        radial-gradient(circle at 25% 25%, rgba(249, 115, 22, 0.1) 0%, transparent 50%),
                                        radial-gradient(circle at 75% 75%, rgba(59, 130, 246, 0.08) 0%, transparent 50%),
                                        repeating-linear-gradient(45deg, transparent, transparent 35px, rgba(255, 255, 255, 0.02) 35px, rgba(255, 255, 255, 0.02) 70px);
                                    pointer-events: none;
                                "></div>
                                
                                <!-- Floating Background Icons -->
                                <div class="position-absolute top-0 end-0 p-4" style="animation: floatSlow 8s ease-in-out infinite;">
                                    <i class="fas fa-award" style="font-size: 4rem; opacity: 0.08; color: #f97316; filter: blur(1px);"></i>
                                </div>
                                <div class="position-absolute top-0 start-0 p-4" style="animation: floatSlow 10s ease-in-out infinite 2s;">
                                    <i class="fas fa-certificate" style="font-size: 3rem; opacity: 0.06; color: #3b82f6; filter: blur(1px);"></i>
                                </div>
                                <div class="position-absolute bottom-0 end-0 p-4" style="animation: floatSlow 9s ease-in-out infinite 1s;">
                                    <i class="fas fa-shield-alt" style="font-size: 2.5rem; opacity: 0.05; color: #10b981; filter: blur(1px);"></i>
                                </div>
                                
                                <!-- Enhanced Central Icon -->
                                <div class="text-center mb-4" style="margin-top: 20px;">
                                    <div class="d-inline-block p-5 rounded-circle position-relative" style="
                                        background: linear-gradient(135deg, rgba(249, 115, 22, 0.15), rgba(249, 115, 22, 0.05));
                                        backdrop-filter: blur(20px);
                                        -webkit-backdrop-filter: blur(20px);
                                        border: 2px solid rgba(249, 115, 22, 0.3);
                                        box-shadow: 
                                            0 20px 40px rgba(249, 115, 22, 0.2),
                                            inset 0 1px 0 rgba(255, 255, 255, 0.2);
                                        animation: iconGlow 4s ease-in-out infinite;
                                        transform: translateY(10px);
                                    ">
                                        <div class="position-absolute top-0 start-0 w-100 h-100 rounded-circle" style="
                                            background: radial-gradient(circle, rgba(249, 115, 22, 0.4) 0%, transparent 70%); 
                                            animation: rippleExpand 4s linear infinite;
                                        "></div>
                                        <i class="fas fa-medal" style="font-size: 5rem; color: #fbbf24; position: relative; z-index: 2; text-shadow: 0 0 20px rgba(251, 191, 36, 0.5);"></i>
                                    </div>
                                </div>
                                
                                <!-- Enhanced Heading -->
                                <div class="text-center mb-4">
                                    <div class="d-inline-block">
                                        <div class="badge mb-3 px-5 py-2 d-inline-flex align-items-center" style="
                                            background: linear-gradient(135deg, rgba(249, 115, 22, 0.9), rgba(251, 146, 60, 0.9));
                                            color: white;
                                            font-size: 0.7rem;
                                            text-transform: uppercase;
                                            letter-spacing: 2px;
                                            font-weight: 700;
                                            border-radius: 50px;
                                            box-shadow: 0 8px 25px rgba(249, 115, 22, 0.4);
                                            border: none;
                                            animation: badgePulse 3s ease-in-out infinite;
                                            position: relative;
                                            overflow: visible;
                                            z-index: 1;
                                            white-space: nowrap;
                                            min-width: 180px;
                                            justify-content: center;
                                        ">
                                            <i class="fas fa-star" style="color: #fbbf24; position: relative; z-index: 2; font-size: 0.9rem; margin-right: 6px;"></i>
                                            <span style="position: relative; z-index: 2; font-size: 0.7rem; letter-spacing: 1.5px;">OFFICIAL RECOGNITION</span>
                                            <i class="fas fa-star" style="color: #fbbf24; position: relative; z-index: 2; font-size: 0.9rem; margin-left: 6px;"></i>
                                        </div>
                                    </div>
                                    <h2 class="h2 fw-bold mb-0 position-relative" style="
                                        z-index: 2; 
                                        text-shadow: 
                                            0 4px 8px rgba(0,0,0,0.5),
                                            0 0 30px rgba(249, 115, 22, 0.3);
                                        font-size: 2.5rem;
                                        font-weight: 800;
                                        background: linear-gradient(135deg, #ffffff, #e0e7ff);
                                        -webkit-background-clip: text;
                                        -webkit-text-fill-color: transparent;
                                        background-clip: text;
                                    ">
                                        <i class="fas fa-certificate me-3" style="
                                            color: #fbbf24; 
                                            font-size: 2rem;
                                            filter: drop-shadow(0 0 10px rgba(251, 191, 36, 0.6));
                                            animation: iconRotate 6s linear infinite;
                                        "></i>
                                        NABCB Accredited Certification Body
                                    </h2>
                                </div>
                                
                                <!-- Enhanced Description -->
                                <div class="text-center mb-5">
                                    <p class="lead mb-0 position-relative" style="
                                        z-index: 2; 
                                        font-size: 1.4rem; 
                                        line-height: 1.8; 
                                        text-shadow: 0 2px 4px rgba(0,0,0,0.4);
                                        max-width: 900px; 
                                        margin-left: auto; 
                                        margin-right: auto;
                                        color: #e0e7ff;
                                        font-weight: 500;
                                    ">
                                        <span style="display: inline-block; padding: 8px 16px; margin: 4px; background: rgba(255, 255, 255, 0.05); border-radius: 8px;">AITF Management Certification Limited is an</span>
                                        <br>
                                        <strong style="
                                            color: #fbbf24; 
                                            font-weight: 800; 
                                            text-shadow: 0 0 20px rgba(251, 191, 36, 0.6);
                                            font-size: 1.6rem;
                                            display: inline-block;
                                            padding: 8px 20px;
                                            margin: 8px 4px;
                                            background: linear-gradient(135deg, rgba(249, 115, 22, 0.2), rgba(251, 146, 60, 0.1));
                                            border-radius: 12px;
                                            border: 1px solid rgba(249, 115, 22, 0.3);
                                            animation: highlightGlow 4s ease-in-out infinite;
                                        ">accredited certification body</strong>
                                        <br>
                                        <span style="display: inline-block; padding: 8px 16px; margin: 4px; background: rgba(255, 255, 255, 0.05); border-radius: 8px;">from NABCB (National Accreditation Board for Certification Bodies)</span>
                                        <br>
                                        <span style="display: inline-block; padding: 8px 16px; margin: 4px; background: rgba(255, 255, 255, 0.05); border-radius: 8px;">for QMS, EMS, and OHSMS Certifications.</span>
                                    </p>
                                </div>
                                
                                <!-- Enhanced Certification Badges -->
                                <div class="text-center mt-5">
                                    <div class="d-inline-flex gap-3 flex-wrap justify-content-center align-items-center">
                                        <div class="certification-badge px-5 py-3 rounded-pill d-flex align-items-center gap-3" style="
                                            background: linear-gradient(135deg, rgba(249, 115, 22, 0.3), rgba(249, 115, 22, 0.1));
                                            backdrop-filter: blur(10px);
                                            -webkit-backdrop-filter: blur(10px);
                                            border: 2px solid rgba(249, 115, 22, 0.5);
                                            color: white; 
                                            font-weight: 700; 
                                            font-size: 1.1rem;
                                            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1); 
                                            box-shadow: 0 8px 25px rgba(249, 115, 22, 0.4);
                                            position: relative;
                                            overflow: hidden;
                                        " onmouseover="this.style.transform='translateY(-5px) scale(1.1)'; this.style.boxShadow='0 15px 40px rgba(249, 115, 22, 0.6)'; this.style.borderColor='rgba(249, 115, 22, 0.8)';" onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 8px 25px rgba(249, 115, 22, 0.4)'; this.style.borderColor='rgba(249, 115, 22, 0.5)';">
                                            <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%); animation: shimmer 3s ease-in-out infinite;"></div>
                                            <i class="fas fa-check-circle" style="color: #fbbf24; font-size: 1.3rem; position: relative; z-index: 2;"></i>
                                            <span style="position: relative; z-index: 2;">QMS</span>
                                        </div>
                                        <div class="certification-badge px-5 py-3 rounded-pill d-flex align-items-center gap-3" style="
                                            background: linear-gradient(135deg, rgba(249, 115, 22, 0.3), rgba(249, 115, 22, 0.1));
                                            backdrop-filter: blur(10px);
                                            -webkit-backdrop-filter: blur(10px);
                                            border: 2px solid rgba(249, 115, 22, 0.5);
                                            color: white; 
                                            font-weight: 700; 
                                            font-size: 1.1rem;
                                            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1); 
                                            box-shadow: 0 8px 25px rgba(249, 115, 22, 0.4);
                                            position: relative;
                                            overflow: hidden;
                                        " onmouseover="this.style.transform='translateY(-5px) scale(1.1)'; this.style.boxShadow='0 15px 40px rgba(249, 115, 22, 0.6)'; this.style.borderColor='rgba(249, 115, 22, 0.8)';" onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 8px 25px rgba(249, 115, 22, 0.4)'; this.style.borderColor='rgba(249, 115, 22, 0.5)';">
                                            <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%); animation: shimmer 3s ease-in-out infinite 0.5s;"></div>
                                            <i class="fas fa-check-circle" style="color: #fbbf24; font-size: 1.3rem; position: relative; z-index: 2;"></i>
                                            <span style="position: relative; z-index: 2;">EMS</span>
                                        </div>
                                        <div class="certification-badge px-5 py-3 rounded-pill d-flex align-items-center gap-3" style="
                                            background: linear-gradient(135deg, rgba(249, 115, 22, 0.3), rgba(249, 115, 22, 0.1));
                                            backdrop-filter: blur(10px);
                                            -webkit-backdrop-filter: blur(10px);
                                            border: 2px solid rgba(249, 115, 22, 0.5);
                                            color: white; 
                                            font-weight: 700; 
                                            font-size: 1.1rem;
                                            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1); 
                                            box-shadow: 0 8px 25px rgba(249, 115, 22, 0.4);
                                            position: relative;
                                            overflow: hidden;
                                        " onmouseover="this.style.transform='translateY(-5px) scale(1.1)'; this.style.boxShadow='0 15px 40px rgba(249, 115, 22, 0.6)'; this.style.borderColor='rgba(249, 115, 22, 0.8)';" onmouseout="this.style.transform='translateY(0) scale(1)'; this.style.boxShadow='0 8px 25px rgba(249, 115, 22, 0.4)'; this.style.borderColor='rgba(249, 115, 22, 0.5)';">
                                            <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.1) 50%, transparent 70%); animation: shimmer 3s ease-in-out infinite 1s;"></div>
                                            <i class="fas fa-check-circle" style="color: #fbbf24; font-size: 1.3rem; position: relative; z-index: 2;"></i>
                                            <span style="position: relative; z-index: 2;">OHSMS</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Enhanced Bottom Gradient Border -->
                                <div class="position-absolute bottom-0 start-0 w-100" style="
                                    height: 8px; 
                                    background: linear-gradient(90deg, #f97316, #fb923c, #fbbf24, #f59e0b, #f97316); 
                                    background-size: 300% 100%; 
                                    animation: gradientFlow 4s ease-in-out infinite; 
                                    box-shadow: 0 4px 20px rgba(249, 115, 22, 0.6);
                                    border-radius: 0 0 24px 24px;
                                "></div>
                                
                                <!-- CSS Animations -->
                                <style>
                                    @keyframes floatSlow {
                                        0%, 100% { transform: translateY(0px) rotate(0deg); }
                                        50% { transform: translateY(-15px) rotate(3deg); }
                                    }
                                    @keyframes iconGlow {
                                        0%, 100% { 
                                            box-shadow: 0 20px 40px rgba(249, 115, 22, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.2);
                                            transform: scale(1);
                                        }
                                        50% { 
                                            box-shadow: 0 25px 50px rgba(249, 115, 22, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.3);
                                            transform: scale(1.05);
                                        }
                                    }
                                    @keyframes rippleExpand {
                                        0% { transform: scale(1); opacity: 0.4; }
                                        100% { transform: scale(2); opacity: 0; }
                                    }
                                    @keyframes gradientFlow {
                                        0%, 100% { background-position: 0% 50%; }
                                        50% { background-position: 100% 50%; }
                                    }
                                    @keyframes badgePulse {
                                        0%, 100% { transform: scale(1); box-shadow: 0 8px 25px rgba(249, 115, 22, 0.4); }
                                        50% { transform: scale(1.05); box-shadow: 0 12px 35px rgba(249, 115, 22, 0.6); }
                                    }
                                    @keyframes iconRotate {
                                        0% { transform: rotateY(0deg); }
                                        100% { transform: rotateY(360deg); }
                                    }
                                    @keyframes highlightGlow {
                                        0%, 100% { box-shadow: 0 0 20px rgba(251, 191, 36, 0.6); }
                                        50% { box-shadow: 0 0 30px rgba(251, 191, 36, 0.8); }
                                    }
                                    @keyframes shimmer {
                                        0% { transform: translateX(-100%); }
                                        100% { transform: translateX(100%); }
                                    }
                                </style>
                            </div>
                        </div>
                    </div>
                </div>
                
                <style>
                    /* Dashboard Style Certifications */
                    .dashboard-certifications {
                        background: rgba(15, 15, 20, 0.8);
                        backdrop-filter: blur(12px);
                        -webkit-backdrop-filter: blur(12px);
                        position: relative;
                        overflow: hidden;
                        z-index: 1;
                        border-radius: 15px;
                        padding: 22px;
                        margin: 0 auto 0;
                        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
                        max-width: 380px;
                        margin-left: auto;
                        margin-right: 0;
                        border: 1px solid rgba(255, 215, 128, 0.45);
                        transition: all 0.3s ease;
                        transform: translateY(0%);
                        margin-top: 5px;
                        z-index: 5;
                    }

                    .certification-border {
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        z-index: -1;
                        overflow: hidden;
                        border-radius: 15px;
                        padding: 1.5px;
                        background: linear-gradient(45deg, 
                            #ff6b6b, #ff8e8e, #ffb3b3,
                            #ffd166, #ffd700, #f4a261,
                            #e9c46a, #2a9d8f, #4cc9f0,
                            #4895ef, #4361ee, #3a0ca3);
                        background-size: 400% 400%;
                        animation: gradientBG 8s ease infinite;
                    }
                    
                    .certification-border::before {
                        content: '';
                        position: absolute;
                        top: 1.5px;
                        left: 1.5px;
                        right: 1.5px;
                        bottom: 1.5px;
                        background: rgba(15, 15, 20, 0.9);
                        border-radius: 12px;
                        z-index: -1;
                    }
                    
                    .certification-border span {
                        position: absolute;
                        display: block;
                        background: linear-gradient(90deg, 
                            #ff6b6b, #ff8e8e, #ffd166, 
                            #f4a261, #e9c46a, #2a9d8f, 
                            #4cc9f0, #4895ef, #4361ee, #3a0ca3);
                        background-size: 300% 100%;
                        animation: borderAnimation 3s linear infinite;
                        box-shadow: 0 0 15px rgba(255, 255, 255, 0.5);
                        z-index: 1;
                    }
                    
                    .certification-border span:nth-child(1) {
                        top: 0;
                        left: -100%;
                        width: 100%;
                        height: 3px;
                        animation-delay: 0s;
                        filter: drop-shadow(0 0 5px #ff6b6b);
                    }
                    
                    .certification-border span:nth-child(2) {
                        top: -100%;
                        right: 0;
                        width: 3px;
                        height: 100%;
                        animation-delay: 0.5s;
                        filter: drop-shadow(0 0 5px #4cc9f0);
                    }
                    
                    .certification-border span:nth-child(3) {
                        bottom: 0;
                        right: -100%;
                        width: 100%;
                        height: 3px;
                        animation-delay: 1s;
                        filter: drop-shadow(0 0 5px #a78bfa);
                    }
                    
                    .certification-border span:nth-child(4) {
                        bottom: -100%;
                        left: 0;
                        width: 3px;
                        height: 100%;
                        animation-delay: 1.5s;
                        filter: drop-shadow(0 0 5px #3a0ca3);
                    }
                    
                    @keyframes borderAnimation {
                        0% {
                            transform: translateX(0) translateY(0);
                            background-position: 0% center;
                            opacity: 0.9;
                        }
                        50% {
                            opacity: 1;
                            filter: brightness(1.2);
                        }
                        100% {
                            transform: translateX(200%) translateY(200%);
                            background-position: 200% center;
                            opacity: 0.9;
                        }
                    }
                    
                    @keyframes gradientBG {
                        0% {
                            background-position: 0% 50%;
                        }
                        50% {
                            background-position: 100% 50%;
                        }
                        100% {
                            background-position: 0% 50%;
                        }
                    }
                    
                    /* Add corner elements */
                    .dashboard-certifications::after {
                        content: '';
                        position: absolute;
                        width: 15px;
                        height: 15px;
                        border: 2px solid #8a5cff;
                        animation: cornerPulse 2s ease-in-out infinite;
                    }
                    
                    .dashboard-certifications::after {
                        top: 10px;
                        left: 10px;
                        border-right: none;
                        border-bottom: none;
                        border-top-left-radius: 5px;
                    }
                    
                    @keyframes cornerPulse {
                        0%, 100% {
                            opacity: 0.7;
                            transform: scale(1);
                        }
                        50% {
                            opacity: 1;
                            transform: scale(1.2);
                        }
                    }

                    .dashboard-certifications::before {
                        content: '';
                        position: absolute;
                        top: 10px;
                        left: 12px;
                        right: 12px;
                        height: 3px;
                        border: none;
                        background: linear-gradient(90deg, #4f83ff, #8a5cff, #c64bff);
                        border-radius: 999px;
                    }

                    .dashboard-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 20px;
                        padding-bottom: 15px;
                        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                    }

                    .dashboard-header h3 {
                        margin: 0;
                        color: #fff;
                        font-size: 18px;
                        font-weight: 600;
                        letter-spacing: 1px;
                    }

                    .dashboard-badge {
                        background: linear-gradient(135deg, #4f83ff, #8a5cff);
                        padding: 6px 12px;
                        border-radius: 20px;
                        display: flex;
                        align-items: center;
                        gap: 6px;
                        font-size: 12px;
                        font-weight: 500;
                        color: white;
                        box-shadow: 0 4px 15px rgba(79, 131, 255, 0.3);
                    }

                    .dashboard-badge i {
                        font-size: 14px;
                    }

                    .certification-dashboard {
                        display: flex;
                        flex-direction: column;
                        gap: 12px;
                        margin-bottom: 20px;
                    }

                    .certification-card {
                        background: rgba(255, 255, 255, 0.9);
                        border-radius: 10px;
                        padding: 15px;
                        display: flex;
                        align-items: center;
                        transition: all 0.3s ease;
                        position: relative;
                        overflow: hidden;
                        border-left: 4px solid var(--card-color);
                        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
                        z-index: 1;
                    }
                    
                    .certification-card::before {
                        content: '';
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: transparent;
                        z-index: -1;
                    }
                    
                    .certification-card::after {
                        content: '';
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 0;
                        height: 100%;
                        background: linear-gradient(90deg,
                            rgba(34, 197, 94, 0.25) 0%,
                            rgba(22, 163, 74, 0.75) 50%,
                            rgba(22, 101, 52, 0.98) 100%);
                        z-index: -1;
                        transition: width 12s linear;
                    }
                    
                    .certification-card::after {
                        animation: fillAnimation 12s infinite linear;
                    }
                    
                    .certification-card:nth-child(1)::after {
                        animation-delay: 0s;
                    }
                    
                    .certification-card:nth-child(2)::after {
                        animation-delay: 0.2s;
                    }
                    
                    .certification-card:nth-child(3)::after {
                        animation-delay: 0.4s;
                    }

                    .certification-card:hover {
                        transform: translateY(-3px);
                        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
                    }
                    
                    .certification-card:hover::after {
                        animation-play-state: paused;
                    }

                    .cert-icon {
                        width: 40px;
                        height: 40px;
                        border-radius: 8px;
                        background: rgba(79, 131, 255, 0.1);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin-right: 15px;
                        color: var(--card-color);
                        font-size: 16px;
                    }

                    .cert-details {
                        flex: 1;
                    }

                    .cert-code {
                        display: block;
                        font-weight: 700;
                        color: #2d3748;
                        font-size: 14px;
                        margin-bottom: 2px;
                    }

                    .cert-name {
                        display: block;
                        font-size: 12px;
                        color: #718096;
                        font-weight: 500;
                    }

                    .cert-status {
                        display: flex;
                        align-items: center;
                        font-size: 12px;
                        color: #48bb78;
                        font-weight: 500;
                    }

                    .status-dot {
                        display: inline-block;
                        width: 8px;
                        height: 8px;
                        background: #48bb78;
                        border-radius: 50%;
                        margin-right: 6px;
                        animation: pulse 2s infinite;
                    }

                    .dashboard-footer {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding-top: 15px;
                        border-top: 1px solid rgba(255, 255, 255, 0.1);
                    }

                    .status-indicator {
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        font-size: 12px;
                        color: rgba(255, 255, 255, 0.7);
                    }

                    .pulse {
                        display: block;
                        width: 8px;
                        height: 8px;
                        border-radius: 50%;
                        background: #48bb78;
                        position: relative;
                    }

                    .pulse::after {
                        content: '';
                        position: absolute;
                        width: 100%;
                        height: 100%;
                        top: 0;
                        left: 0;
                        background: #48bb78;
                        border-radius: 50%;
                        animation: pulse 2s infinite;
                        opacity: 0.7;
                    }

                    .update-time {
                        color: #fff;
                        font-weight: 500;
                    }

                    .refresh-btn {
                        background: rgba(255, 255, 255, 0.1);
                        border: none;
                        color: #fff;
                        padding: 6px 12px;
                        border-radius: 20px;
                        font-size: 12px;
                        display: flex;
                        align-items: center;
                        gap: 6px;
                        cursor: pointer;
                        transition: all 0.3s ease;
                    }

                    .refresh-btn:hover {
                        background: rgba(255, 255, 255, 0.2);
                    }

                    .refresh-btn i {
                        font-size: 12px;
                    }

                    @keyframes fillAnimation {
                        0% { width: 0; }
                        98% { width: 100%; }
                        100% { width: 0; }
                    }

                    @keyframes highlightGlow {
                        0%, 100% {
                            box-shadow: 0 0 0 1px rgba(56, 189, 248, 0.25), 0 0 14px rgba(56, 189, 248, 0.25);
                            border-color: rgba(129, 140, 248, 0.7);
                        }
                        50% {
                            box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.55), 0 0 26px rgba(56, 189, 248, 0.75);
                            border-color: rgba(129, 140, 248, 1);
                        }
                    }
                    
                    @keyframes shine {
                        0% { transform: translateX(-100%); }
                        20% { transform: translateX(100%); }
                        100% { transform: translateX(100%); }
                    }
                    
                    @keyframes pulse {
                        0% {
                            transform: scale(1);
                            opacity: 1;
                        }
                        70% {
                            transform: scale(2);
                            opacity: 0;
                        }
                        100% {
                            transform: scale(1);
                            opacity: 0;
                        }
                    }

                    @media (max-width: 992px) {
                       .dashboard-certifications {
                           margin: 40px auto 0;
                           transform: none;
                           max-width: 100%;
                       }
                    }
                    
                    @media (max-width: 480px) {
                       .dashboard-certifications {
                           padding: 20px 15px;
                           margin: 20px 10px;
                           transform: none;
                       }
                        
                        .certification-card {
                            padding: 12px 15px;
                        }
                        
                        .cert-icon {
                            width: 36px;
                            height: 36px;
                            font-size: 14px;
                        }
                        
                        .cert-code {
                            font-size: 13px;
                        }
                        
                        .cert-name {
                            font-size: 11px;
                        }
                    }
                </style>
                <script>
                    // Update timestamp and add refresh functionality
                    document.addEventListener('DOMContentLoaded', function() {
                        const updateTime = document.querySelector('.update-time');
                        const refreshBtn = document.querySelector('.refresh-btn');
                        
                        function updateTimestamp() {
                            const now = new Date();
                            updateTime.textContent = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            
                            // Add a visual feedback for refresh
                            refreshBtn.innerHTML = '<i class="fas fa-check"></i> Updated';
                            setTimeout(() => {
                                refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
                            }, 2000);
                        }
                        
                        // Initial update
                        updateTimestamp();
                        
                        // Update time every minute
                        setInterval(() => {
                            const now = new Date();
                            updateTime.textContent = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        }, 60000);
                        
                        // Add click event for refresh button
                        if (refreshBtn) {
                            refreshBtn.addEventListener('click', updateTimestamp);
                        }
                    });
                </script>
            </div>
        </div>
    </div>
    
    <style>
        .modern-hero-section {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            color: #fff;
            padding: 0 0 60px;
            margin-top: 0;
            font-family: 'Montserrat', 'Poppins', sans-serif;
            position: relative;
            overflow: hidden;
            position: relative;
            overflow: hidden;
            font-family: 'Montserrat', 'Poppins', sans-serif;
            position: relative;
            overflow: hidden;
        }
        
        .hero-content {
            max-width: 1200px;
            margin: -10px auto 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 60px;
            position: relative;
            z-index: 2;
            padding: 0 20px;
        }
        
        .hero-text {
            flex: 1;
            max-width: 600px;
            padding: 20px 0;
        }
        
        .hero-image {
            flex: 1;
            text-align: center;
        }
        
        .certifications-container {
            background: rgba(255, 255, 255, 0.08);
            padding: 35px 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            max-width: 400px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .certifications-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            border-color: rgba(255, 255, 255, 0.2);
        }
        
        .certifications-container h3 {
            color: #fff;
            font-size: 1.7rem;
            margin-bottom: 25px;
            text-align: center;
            position: relative;
            padding-bottom: 15px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-family: 'Montserrat', sans-serif;
        }
        
        .certifications-container h3:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #4299e1, #48bb78);
            border-radius: 3px;
            transition: all 0.3s ease;
        }
        
        .certifications-container:hover h3:after {
            width: 100px;
        }
        
        .certification-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .certification-list li {
            color: #f8fafc;
            font-size: 1.15rem;
            padding: 14px 16px;
            margin: 5px 0;
            border-radius: 8px;
            border: 1px solid transparent;
            background: rgba(255, 255, 255, 0.03);
            display: flex;
            align-items: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .certification-list li:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(5px);
            border-color: rgba(66, 153, 225, 0.3);
        }
        
        .certification-list li:before {
            content: '✓';
            color: #48bb78;
            font-weight: bold;
            margin-right: 15px;
            font-size: 1.3rem;
            text-shadow: 0 0 10px rgba(72, 187, 120, 0.5);
            transition: all 0.3s ease;
        }
        
        .certification-list li:hover:before {
            transform: scale(1.2);
        }
        
        .certification-list li:last-child {
            border-bottom: none;
        }
        
        h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #fff;
        }
        
        h2 {
            font-size: 1.8rem;
            font-weight: 400;
            margin-bottom: 30px;
            color: #a0aec0;
        }
        
        .hero-highlights {
            display: flex;
            gap: 1.5rem;
            margin: 0 0 2.5rem 0;
            flex-wrap: wrap;
            align-items: stretch;
        }
        
        .highlight-badge {
            display: inline-flex;
            align-items: center;
            gap: 1rem;
            padding: 0.85rem 2.8rem;
            min-width: 260px;
            min-height: 72px;
            border-radius: 999px;
            font-size: 1rem;
            font-weight: 600;
            color: #f8fafc;
            position: relative;
            isolation: isolate;
            overflow: visible;
            letter-spacing: 0.02em;
            transition: transform 0.35s ease, box-shadow 0.35s ease;
        }
        
        .highlight-badge::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: linear-gradient(120deg, rgba(94, 234, 212, 0.9), rgba(56, 189, 248, 0.9), rgba(192, 132, 252, 0.9));
            background-size: 200% 200%;
            animation: borderSweep 6s linear infinite;
            filter: drop-shadow(0 0 12px rgba(59, 130, 246, 0.45));
            z-index: -2;
        }
        
        .highlight-badge::after {
            content: '';
            position: absolute;
            inset: 4px;
            border-radius: inherit;
            background: radial-gradient(circle at 15% 20%, rgba(94, 234, 212, 0.2), transparent 45%),
                        rgba(6, 11, 25, 0.92);
            box-shadow: inset 0 18px 35px rgba(2, 6, 23, 0.8), 0 15px 35px rgba(8, 8, 20, 0.35);
            z-index: -1;
        }
        
        .highlight-badge:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 12px 35px rgba(56, 189, 248, 0.35);
        }
        
        .highlight-badge i {
            font-size: 24px;
            color: #FFD700 !important;
        }
        
        .highlight-badge i.fa-medal {
            font-size: 32px;
        }
        
        .highlight-badge.iso-cluster {
            align-items: center;
            gap: 0.5rem;
            padding-right: 0.5rem;
            max-width: fit-content;
            padding-left: 0.8rem;
        }
        
        .iso-chip-row {
            display: flex;
            gap: 0.3rem;
            flex-wrap: nowrap;
        }
        
        .iso-chip {
            width: 55px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 11px;
            font-weight: 600;
            color: white;
            text-align: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4),
                       inset 0 1px 0 rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .iso-chip::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.6s;
        }
        
        .iso-chip:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6),
                       inset 0 1px 0 rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }
        
        .iso-chip:hover::before {
            left: 100%;
        @keyframes borderSweep {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        
        .hero-description {
            font-size: 1.1rem;
            line-height: 1.8;
            margin: 0 0 2.5rem 0;
            color: #e2e8f0;
            max-width: 90%;
            opacity: 0.9;
        }
        
        .button-group {
            margin-top: 35px;
            text-align: center;
            display: flex;
            gap: 1.2rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .hero-cta {
            display: flex;
            gap: 1.2rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .cta-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            padding: 1rem 2.2rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 1.05rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .cta-button.primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }
        
        .cta-button.secondary {
            background: rgba(255, 255, 255, 0.08);
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(5px);
        }
        
        .cta-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0.05) 100%);
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .cta-button:hover::before {
            opacity: 1;
        }
        
        .cta-button:active {
            transform: translateY(0);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        @media (max-width: 992px) {
            .modern-hero-section {
                padding: 40px 0 60px;
            }
            
            .hero-content {
                flex-direction: column;
                text-align: center;
                gap: 40px;
            }
            
            .hero-text {
                max-width: 100%;
                text-align: center;
                padding: 0;
            }
            
            .hero-text h1 {
                font-size: 3.8rem;
                margin: 0 0 1rem 0;
                font-weight: 800;
                line-height: 1.2;
                background: linear-gradient(to right, #ffffff, #e0e7ff);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                letter-spacing: -0.5px;
            }
            
            .hero-text h2 {
                font-size: 1.2rem;
                margin: 0 auto 2.5rem;
                max-width: 100%;
            }
            
            .hero-highlights {
                justify-content: center;
                margin-bottom: 2.5rem;
            }
            
            .hero-cta {
                justify-content: center;
            }
            
            .cta-button {
                padding: 0.9rem 1.8rem;
            }
        }
        
        @media (max-width: 768px) {
            .modern-hero-section {
                padding: 20px 0 50px;
            }
            
            .hero-text h1 {
                font-size: 2.4rem;
                line-height: 1.3;
            }
            
            .hero-text h2 {
                font-size: 1.1rem;
                line-height: 1.6;
                margin-bottom: 2rem;
            }
            
            .highlight-badge {
                padding: 0.5rem 1.2rem;
                font-size: 0.9rem;
            }
            
            .highlight-badge i {
                background: none !important;
                box-shadow: none !important;
                color: #FFD700 !important;
            }
            
            .hero-cta {
                gap: 1rem;
            }
            
            .cta-button {
                width: 100%;
                justify-content: center;
                padding: 0.9rem 1.5rem;
            }
            
            .hero-description {
                font-size: 1rem;
                max-width: 100%;
            }
        }
    </style>
    <!-- end section -->
    <!-- section -->
    <section class="methodology-section" style="position: relative; padding: 120px 0; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); overflow: hidden;">
        <!-- Decorative Elements -->
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
            background: 
                radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(16, 185, 129, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 50% 50%, rgba(139, 92, 246, 0.05) 0%, transparent 40%);
            z-index: 0;">
        </div>
        
        <div class="container" style="position: relative; z-index: 1;">
            <div class="section-header text-center" style="max-width: 1000px; margin: 0 auto 60px;">
                <span style="display: inline-block; 
                    background: linear-gradient(90deg, #8b5cf6, #10b981);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                    font-weight: 700;
                    letter-spacing: 3px;
                    margin-bottom: 20px;
                    text-transform: uppercase;
                    font-size: 0.95rem;
                    position: relative;
                    padding: 8px 24px;
                    border-radius: 50px;
                    background-color: rgba(139, 92, 246, 0.1);
                    backdrop-filter: blur(5px);
                    border: 1px solid rgba(139, 92, 246, 0.2);">
                    Why Choose AITF
                </span>
                
                <h2 class="section-title" style="font-size: 3rem; 
                    font-weight: 800; 
                    margin: 20px auto 30px; 
                    color: #f8fafc; 
                    line-height: 1.2;
                    position: relative;
                    display: inline-block;
                    letter-spacing: -0.5px;
                    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);">
                    Our Commitment to <span style="position: relative; white-space: nowrap;
                        background: linear-gradient(90deg, #8b5cf6, #10b981);
                        -webkit-background-clip: text;
                        -webkit-text-fill-color: transparent;
                        font-weight: 900;">
                        Excellence
                    </span>
                </h2>
                
                <div style="position: relative; max-width: 1000px; margin: 40px auto 0;">
                    <div style="background: rgba(30, 41, 59, 0.7); 
                        border-radius: 20px; 
                        padding: 60px; 
                        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 
                                    inset 0 1px 0 0 rgba(255, 255, 255, 0.05);
                        border: 1px solid rgba(255, 255, 255, 0.05);
                        position: relative;
                        overflow: hidden;
                        backdrop-filter: blur(10px);">
                        
                        <!-- Decorative Corner Elements -->
                        <div style="position: absolute; 
                            top: 0; 
                            right: 0; 
                            width: 120px; 
                            height: 120px; 
                            background: linear-gradient(135deg, rgba(139, 92, 246, 0.2) 0%, transparent 70%);
                            border-radius: 0 20px 0 0;">
                        </div>
                        
                        <div style="position: absolute;
                            bottom: 0;
                            left: 0;
                            width: 120px;
                            height: 120px;
                            background: linear-gradient(315deg, rgba(16, 185, 129, 0.2) 0%, transparent 70%);
                            border-radius: 0 20px 0 0;
                            transform: rotate(180deg);
                            opacity: 0.8;">
                        </div>
                        
                        <p class="section-description" style="color: #e2e8f0; 
                            font-size: 1.2rem; 
                            line-height: 1.9; 
                            margin: 0 auto;
                            font-weight: 400;
                            letter-spacing: 0.3px;
                            text-align: center;
                            max-width: 800px;
                            position: relative;
                            z-index: 1;
                            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);">
                            At <strong style="color: #8b5cf6; font-weight: 600; text-shadow: 0 0 10px rgba(139, 92, 246, 0.3);">AITF Management Certification Ltd.</strong>, we are dedicated to delivering comprehensive assessment services through our team of highly qualified auditors. Our primary objective is to enhance our clients' business performance by conducting value-added audits of their processes and management systems.
                        </p>
                        
                        <!-- Decorative Bottom Border -->
                        <div style="position: absolute;
                            bottom: 0;
                            left: 50%;
                            transform: translateX(-50%);
                            width: 150px;
                            height: 4px;
                            background: linear-gradient(90deg, #8b5cf6, #10b981);
                            border-radius: 2px;
                            margin: 30px 0;
                            box-shadow: 0 0 15px rgba(139, 92, 246, 0.5);">
                        </div>
                    </div>
                </div>
            </div>
            <div class="methodology-container" style="justify-content: center;">
                <div class="methodology-card" style="max-width: 800px; margin: 0 auto;">
                    <div class="card-icon">
                        <i class="fa fa-search"></i>
                    </div>
                    <div class="card-content">
                        <h3>Our Core Principles</h3>
                        <p>We maintain the highest standards of <strong>competence, independence, and impartiality</strong> in our assessment processes. Our team of certified auditors is committed to helping organizations achieve and demonstrate compliance with international standards, driving continuous improvement and operational excellence across all business functions.</p>
                    </div>
                </div>
            </div>
            
            <div class="cta-container" style="margin-top: 40px;">
                <div class="cta-box" style="position: relative;
                    background: linear-gradient(135deg, #05010c48 0%, #9f5afd 100%);
                    border-radius: 20px;
                    padding: 60px 40px;
                    text-align: center;
                    color: white;
                    overflow: hidden;
                    box-shadow: 0 20px 40px rgba(124, 58, 237, 0.4);
                    border: 1px solid rgba(255, 255, 255, 0.15);">
                    
                    <!-- Decorative elements -->
                    <div style="position: absolute;
                        top: -50px;
                        right: -50px;
                        width: 200px;
                        height: 200px;
                        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
                        border-radius: 50%;">
                    </div>
                    <div style="position: absolute;
                        bottom: -30px;
                        left: -30px;
                        width: 150px;
                        height: 150px;
                        background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 0%, transparent 70%);
                        border-radius: 50%;">
                    </div>
                    
                    <h3 style="font-size: 2.2rem;
                        font-weight: 800;
                        margin: 0 0 15px 0;
                        position: relative;
                        z-index: 2;
                        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                        Ready to Get Certified?
                    </h3>
                    
                    <p style="font-size: 1.15rem;
                        margin: 0 auto 30px;
                        max-width: 600px;
                        line-height: 1.7;
                        position: relative;
                        z-index: 2;
                        color: rgba(255, 255, 255, 0.9);">
                        Take the first step towards excellence with AITF Management Certification Limited.
                    </p>
                    
                    <a href="contactUs.php" 
                       class="cta-button" 
                       style="display: inline-block;
                           background: white;
                           color: #7c3aed;
                           font-weight: 600;
                           padding: 14px 36px;
                           border-radius: 50px;
                           text-decoration: none;
                           font-size: 1.1rem;
                           transition: all 0.3s ease;
                           box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                           position: relative;
                           z-index: 2;
                           border: none;
                           cursor: pointer;
                           text-transform: uppercase;
                           letter-spacing: 1px;
                           &:hover {
                               transform: translateY(-3px);
                               box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
                               background: #f8fafc;
                           }">
                        Get Started
                    </a>
                    
                    <!-- Decorative dots -->
                    <div style="position: absolute;
                        top: 20px;
                        right: 30px;
                        display: flex;
                        gap: 8px;
                        z-index: 1;">
                        <span style="display: inline-block;
                            width: 8px;
                            height: 8px;
                            background: rgba(255, 255, 255, 0.3);
                            border-radius: 50%;"></span>
                        <span style="display: inline-block;
                            width: 8px;
                            height: 8px;
                            background: rgba(255, 255, 255, 0.3);
                            border-radius: 50%;"></span>
                        <span style="display: inline-block;
                            width: 8px;
                            height: 8px;
                            background: rgba(255, 255, 255, 0.3);
                            border-radius: 50%;"></span>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <style>
    /* Moved CSS from body to style tag */
    @media (max-width: 767px) {
        .methodology-card {
            min-width: 280px;
            padding: 25px;
            margin-bottom: 20px;
        }
    }
    </style>
                                </style>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end section -->
    <!-- Premium Management Systems Slider Section -->
    <div class="section management-slider-section" style="position: relative; z-index: 10;">
        <div class="slider-container" style="position: relative;">
            <button class="slider-arrow prev-arrow" aria-label="Previous slide">
                <span class="fa fa-angles-left"></span>
            </button>
            
            <div class="slider-track">
                <!-- Slide 1 - Quality Management System -->
                <div class="slide active">
                    <div class="slide-content">
                        <div class="slide-icon">
                            <i class="fa fa-award"></i>
                        </div>
                        <h3>QUALITY MANAGEMENT SYSTEM</h3>
                        <p class="slide-description">Ensures consistent product and service quality by meeting customer and regulatory requirements through ISO 9001 standards.</p>
                        <a href="contactUs.php" class="slider-btn">APPLY NOW <i class="fa fa-arrow-right"></i></a>
                    </div>
                </div>

                <!-- Slide 2 - Environmental Management System -->
                <div class="slide">
                    <div class="slide-content">
                        <div class="slide-icon">
                            <i class="fa fa-leaf"></i>
                        </div>
                        <h3>ENVIRONMENTAL MANAGEMENT SYSTEM</h3>
                        <p class="slide-description">Helps organizations minimize environmental impact, comply with laws, and improve sustainability under ISO 14001.</p>
                        <a href="contactUs.php" class="slider-btn">APPLY NOW <i class="fa fa-arrow-right"></i></a>
                    </div>
                </div>

                <!-- Slide 3 - Safety Management System -->
                <div class="slide">
                    <div class="slide-content">
                        <div class="slide-icon">
                            <i class="fa fa-shield-halved"></i>
                        </div>
                        <h3>SAFETY MANAGEMENT SYSTEM</h3>
                        <p class="slide-description">Provides a structured approach to workplace safety, reducing risks and ensuring employee well-being under ISO 45001.</p>
                        <a href="contactUs.php" class="slider-btn">APPLY NOW <i class="fa fa-arrow-right"></i></a>
                    </div>
                </div>

            </div>

            <button class="slider-arrow next-arrow" aria-label="Next slide">
                <span class="fa fa-angles-right"></span>
            </button>
            
            <div class="slider-nav">
                <span class="slider-dot" data-slide="0"></span>
                <span class="slider-dot" data-slide="1"></span>
                <span class="slider-dot" data-slide="2"></span>
            </div>
            
            <!-- Box Navigation Controls Removed -->
        </div>
        <div class="slider-glow"></div>
    </div>
    <!-- end section -->
    
    <!-- NABCB Accredited ISO Certification Section -->
    <div class="section layout_padding nabcb-section" style="position: relative; padding: 60px 0 !important; background: linear-gradient(135deg, #0a192f 0%, #172a45 100%);">
        <div class="nabcb-particles" id="nabcb-particles"></div>
        <div class="container" style="position: relative; z-index: 2;">
            <div class="row">
                <div class="col-md-12 text-center">
                    <div class="section-header">
                        <span class="section-tag">Accredited CERTIFICATION</span>
                        <h2 class="section-title">NABCB Accredited <span class="highlight">ISO Certification</span></h2>
                        <div class="title-divider">
                            <span class="divider-line"></span>
                            <span class="divider-icon">✧</span>
                            <span class="divider-line"></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="info-card">
                        <div class="info-card-inner">
                            <div class="info-card-front">
                                <div class="icon-wrapper">
                                    <i class="fa fa-award"></i>
                                </div>
                                <h3>NABCB Accreditation</h3>
                                <p>India's Premier Accreditation Body</p>
                                <div class="hover-indicator">
                                    <span>Hover to Learn More</span>
                                    <i class="fa fa-arrow-down"></i>
                                </div>
                            </div>
                            <div class="info-card-back">
                                <p>The National Accreditation Board for Certification Bodies (NABCB) is a key entity in India's quality infrastructure, serving as a constituent board of the Quality Council of India (QCI).</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-card-inner">
                            <div class="info-card-front">
                                <div class="icon-wrapper">
                                    <i class="fa fa-globe"></i>
                                </div>
                                <h3>International Recognition</h3>
                                <p>Globally Accepted Certifications</p>
                            </div>
                            <div class="info-card-back">
                                <p>A certificate issued by an AITF that is accredited by NABCB and issued with the NABCB & IAF MLA mark is accepted in all economies, as the accreditation body is an IAF MLA signatory.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-card-inner">
                            <div class="info-card-front">
                                <div class="icon-wrapper">
                                    <i class="fa fa-check-circle"></i>
                                </div>
                                <h3>Quality Assurance</h3>
                                <p>Trusted Certification Process</p>
                            </div>
                            <div class="info-card-back">
                                <p>NABCB assesses the competence and impartiality of CABs (AITF) based on international standards and its own criteria, ensuring that the certificates and reports issued are reliable and trustworthy.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 text-center">
                    <div class="certificate-showcase">
                        <h3 class="showcase-title">Our Accredited Certifications</h3>
                        <div class="certificate-showcase-grid">
                            <div class="certificate-item">
                                <div class="certificate-img-container">
                                    <img src="images/certificate_1.png" alt="ISO 9001:2015 Certification" class="certificate-thumb">
                                    <div class="certificate-hover">
                                        <a href="images/certificate_1.png" class="view-cert-btn" data-lightbox="cert-gallery" data-title="ISO 9001:2015 Certification">
                                            <i class="fa fa-search-plus"></i>
                                            <span>View</span>
                                        </a>
                                    </div>
                                </div>
                                <h4>ISO 9001:2015</h4>
                                <p>Quality Management System</p>
                            </div>
                            
                            <div class="certificate-item">
                                <div class="certificate-img-container">
                                    <img src="images/certificate_2.png" alt="ISO 14001:2015 Certification" class="certificate-thumb">
                                    <div class="certificate-hover">
                                        <a href="images/certificate_2.png" class="view-cert-btn" data-lightbox="cert-gallery" data-title="ISO 14001:2015 Certification">
                                            <i class="fa fa-search-plus"></i>
                                            <span>View</span>
                                        </a>
                                    </div>
                                </div>
                                <h4>ISO 14001:2015</h4>
                                <p>Environmental Management System</p>
                            </div>
                            
                            <div class="certificate-item">
                                <div class="certificate-img-container">
                                    <img src="images/certificate_3.png" alt="ISO 45001:2018 Certification" class="certificate-thumb">
                                    <div class="certificate-hover">
                                        <a href="images/certificate_3.png" class="view-cert-btn" data-lightbox="cert-gallery" data-title="ISO 45001:2018 Certification">
                                            <i class="fa fa-search-plus"></i>
                                            <span>View</span>
                                        </a>
                                    </div>
                                </div>
                                <h4>ISO 45001:2018</h4>
                                <p>Occupational Health & Safety</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>
    
        
    <style>
        /* NABCB Section - Larger Certification Text */
        .certificate-item h4 {
            font-size: 24px !important;
            font-weight: 700;
            margin: 15px 0 5px;
        }
        
        .certificate-item p {
            font-size: 20px !important;
            font-weight: 500;
            color: #e0e0e0;
        }
        
        /* AITF Intro Section Styling */
        .aitf-intro {
            font-family: 'Montserrat', 'Poppins', sans-serif;
            font-size: 1.2rem;
            line-height: 1.9;
            margin-bottom: 1.8rem;
            text-align: justify;
            letter-spacing: 0.5px;
            word-spacing: 1.5px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            padding: 25px 30px;
            font-weight: 500;
            color: #ffffff;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5), 
                         -1px -1px 3px rgba(0, 0, 0, 0.3);
            position: relative;
            background: rgba(0, 0, 0, 0.4);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .aitf-intro::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            border-radius: 12px;
            z-index: -1;
            opacity: 0.3;
        }
        
        .aitf-intro strong {
            color: #ffffff;
            font-weight: 700;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7), 
                       -1px -1px 3px rgba(0, 0, 0, 0.5);
            position: relative;
            padding: 0 3px;
        }
        
        @media (max-width: 768px) {
            .aitf-intro {
                font-size: 1.1rem;
                line-height: 1.9;
                padding: 20px 15px;
                letter-spacing: 0.4px;
                color: #ffffff;
                background: rgba(0, 0, 0, 0.5);
                text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
            }
            
            .aitf-intro strong {
                color: #ffffff;
                text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
            }
        }
        
        /* NABCB Section Styling */
        .nabcb-section {
            position: relative;
            background: linear-gradient(135deg, #0a192f 0%, #172a45 100%);
            color: #fff;
            padding: 60px 0 !important;
            margin: 0;
            overflow: hidden;
            z-index: 1;
            width: 100%;
            box-sizing: border-box;
        }
        
        .nabcb-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgcGF0dGVyblRyYW5zZm9ybT0icm90YXRlKDQ1KSI+PHJlY3Qgd2lkdGg9IjUwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJyZ2JhKDI1NSwyNTUsMjU1LDAuMDMpIi8+PC9wYXR0ZXNuPjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJ1cmwoI3BhdHRlcm4pIi8+PC9zdmc+');
            z-index: -1;
            opacity: 0.3;
        }
        
        .section-header {
            margin-bottom: 60px;
            position: relative;
            z-index: 2;
        }
        
        .section-tag {
            display: inline-block;
            background: rgba(0, 173, 239, 0.2);
            color: #00adef;
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 20px;
            text-transform: uppercase;
            border: 1px solid rgba(0, 173, 239, 0.3);
        }
        
        .section-title {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 20px;
            background: linear-gradient(90deg, #fff, #64f0ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 30px rgba(100, 240, 255, 0.3);
            position: relative;
        }
        
        .section-title .highlight {
            color: #ffd700;
            -webkit-text-fill-color: #ffd700;
        }
        
        .title-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 25px auto 40px;
            max-width: 400px;
        }
        
        .divider-line {
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(0, 173, 239, 0.5), transparent);
            flex: 1;
        }
        
        .divider-icon {
            color: #00adef;
            margin: 0 15px;
            font-size: 20px;
            opacity: 0.7;
        }
        
        /* Info Cards */
        .info-card {
            perspective: 1000px;
            margin-bottom: 30px;
            height: 220px;
        }
        
        .info-card-inner {
            position: relative;
            width: 100%;
            height: 100%;
            text-align: center;
            transition: transform 0.8s;
            transform-style: preserve-3d;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border-radius: 15px;
        }
        
        .info-card:hover .info-card-inner {
            transform: rotateY(180deg);
        }
        
        .info-card-front, .info-card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            -webkit-backface-visibility: hidden;
            backface-visibility: hidden;
            border-radius: 15px;
            padding: 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .info-card-front {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .info-card-back {
            background: linear-gradient(135deg, #00adef 0%, #0077b6 100%);
            color: white;
            transform: rotateY(180deg);
            justify-content: center;
            text-align: center;
            padding: 25px;
            font-size: 15px;
            line-height: 1.6;
        }
        
        .icon-wrapper {
            width: 70px;
            height: 70px;
            background: rgba(0, 173, 239, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            border: 2px solid rgba(0, 173, 239, 0.3);
        }
        
        .icon-wrapper i {
            font-size: 30px;
            color: #00adef;
        }
        
        .info-card h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #fff;
        }
        
        .info-card p {
            color: #b3e0ff;
            font-size: 14px;
            margin: 0;
        }
        
        .hover-indicator {
            margin-top: 15px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .hover-indicator i {
            margin-top: 5px;
            animation: bounce 2s infinite;
        }
        
        /* Certificate Showcase */
        .certificate-showcase {
            margin-top: 60px;
            position: relative;
            z-index: 2;
        }
        
        .showcase-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 40px;
            color: #fff;
            position: relative;
            display: inline-block;
        }
        
        .showcase-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #00adef, #00f2fe);
            border-radius: 2px;
        }
        
        .certificate-showcase-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 40px;
            margin: 0 auto;
            max-width: 1300px;
            padding: 30px 20px;
        }
        
        .certificate-item {
            background: rgba(10, 25, 47, 0.7);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(100, 255, 218, 0.1);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(8px);
            position: relative;
            z-index: 1;
        }
        
        .certificate-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 242, 254, 0.1) 0%, rgba(79, 172, 254, 0.1) 100%);
            z-index: -1;
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        
        .certificate-item:hover::before {
            opacity: 1;
        }
        
        .certificate-item:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(100, 255, 218, 0.3);
        }
        
        .certificate-img-container {
            position: relative;
            overflow: hidden;
            height: 280px;
            background: #000;
        }
        
        .certificate-thumb {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            background: #fff;
            padding: 15px;
            box-sizing: border-box;
        }
        
        .certificate-hover {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(10, 25, 47, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(5px);
        }
        
        .certificate-item:hover .certificate-hover {
            opacity: 1;
        }
        
        .certificate-item:hover .certificate-thumb {
            transform: scale(1.05);
        }
        
        .view-cert-btn {
            display: inline-flex;
            align-items: center;
            padding: 12px 30px;
            background: transparent;
            color: #64ffda;
            border: 2px solid #64ffda;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .view-cert-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            background: rgba(100, 255, 218, 0.1);
            transition: width 0.3s ease;
            z-index: -1;
        }
        
        .view-cert-btn:hover::before {
            width: 100%;
        }
        
        .view-cert-btn i {
            margin-right: 10px;
            font-size: 16px;
            transition: transform 0.3s ease;
        }
        
        .view-cert-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(100, 255, 218, 0.3);
            color: #64ffda;
        }
        
        .view-cert-btn:hover i {
            transform: scale(1.2);
        }
        
        .certificate-item h4 {
            color: #64ffda;
            font-size: 20px;
            font-weight: 600;
            margin: 25px 25px 8px;
            letter-spacing: 0.5px;
        }
        
        .certificate-item p {
            color: #ccd6f6;
            font-size: 15px;
            margin: 0 25px 25px;
                font-size: 28px;
            }
            
            .section-tag {
                font-size: 12px;
                padding: 6px 15px;
            }
            
            .certificate-badges {
                flex-direction: column;
                align-items: center;
            }
            
            .badge {
                width: 180px;
            }
        }
    </style>
    
    <script>
        // Initialize particles.js for the NABCB section
        document.addEventListener('DOMContentLoaded', function() {
            // Add any necessary JavaScript for animations or interactions
            const cards = document.querySelectorAll('.info-card');
            
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.querySelector('.info-card-inner').style.transform = 'rotateY(180deg)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.querySelector('.info-card-inner').style.transform = 'rotateY(0)';
                });
            });
            
            // Add parallax effect to floating shapes
            const shapes = document.querySelectorAll('.floating-shapes .shape');
            
            document.addEventListener('mousemove', (e) => {
                const x = e.clientX / window.innerWidth;
                const y = e.clientY / window.innerHeight;
                
                shapes.forEach((shape, index) => {
                    const speed = (index + 1) * 0.5;
                    const xMove = (x - 0.5) * 20 * speed;
                    const yMove = (y - 0.5) * 20 * speed;
                    
                    shape.style.transform = `translate(${xMove}px, ${yMove}px) ${window.getComputedStyle(shape).transform.split(' ').slice(2).join(' ')}`;
                });
            });
            
        });
    </script>
    <!-- End NABCB Section -->
    
    
                            <!-- Research Section Placeholder -->
                            <!-- Add your research section content here -->
                </div>
            </div>

    <?php include './footerSource.php'; ?>
    <script>
        // Management Systems Slider
        document.addEventListener('DOMContentLoaded', function() {
            // Mark slider as loading
            const sliderSection = document.querySelector('.management-slider-section');
            const sliderContainer = document.querySelector('.slider-container');
            
            if (sliderSection && sliderContainer) {
                // Show loading state
                sliderSection.style.opacity = '1';
                
                // Initialize slider after a small delay to ensure styles are applied
                setTimeout(() => {
                    sliderContainer.classList.add('loaded');
                    sliderSection.classList.add('loaded');
                    
                    // Show navigation dots with a slight delay
                    const dots = document.querySelector('.slider-dots');
                    if (dots) {
                        setTimeout(() => {
                            dots.style.opacity = '1';
                            dots.style.transform = 'translateY(0)';
                        }, 300);
                    }
                }, 50);
            }
            const sliderTrack = document.querySelector('.slider-track');
            const slides = document.querySelectorAll('.slide');
            const dots = document.querySelectorAll('.slider-dot');
            const prevBtn = document.querySelector('.prev-arrow');
            const nextBtn = document.querySelector('.next-arrow');
            
            // Start at -1 so first updateActiveSlide(0) fully initializes state
            let currentSlide = -1;
            let slideInterval;
            const slideCount = slides.length;
            
            // Initialize slider with performance optimizations and safety mechanisms
            function initSlider() {
                // Clear all slide classes first
                const allSlides = document.querySelectorAll('.slide');
                allSlides.forEach(slide => {
                    slide.classList.remove('active', 'prev', 'next');
                });
                
                // Reset current slide index
                currentSlide = -1;
                
                // Set initial active states
                updateActiveSlide(0);
                
                // Add aggressive safety mechanism to prevent stuck slider
                setInterval(() => {
                    const slides = document.querySelectorAll('.slide');
                    const activeSlide = document.querySelector('.slide.active');
                    
                    // If no active slide found, reset to first slide
                    if (!activeSlide && slides.length > 0) {
                        console.log('Safety reset: No active slide found, resetting to slide 0');
                        currentSlide = -1;
                        updateActiveSlide(0);
                        // Restart auto-slide if it stopped
                        startAutoSlide();
                    }
                    
                    // Force progression if stuck on last slide for too long
                    if (currentSlide === slides.length - 1) {
                        setTimeout(() => {
                            if (currentSlide === slides.length - 1) {
                                console.log('Force progression from last slide');
                                nextSlide();
                            }
                        }, 3500); // Slightly less than auto-slide interval
                    }
                }, 3000); // Check every 3 seconds
                
                // Start auto-slide with slight delay for initial load
                setTimeout(startAutoSlide, 800);
                
                // Add event listeners for arrows
                prevBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    pauseSlide();
                    prevSlide();
                    startAutoSlide();
                });
                
                nextBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    pauseSlide();
                    nextSlide();
                    startAutoSlide();
                });
                
                // Add click events to dots
                dots.forEach((dot, index) => {
                    dot.addEventListener('click', (e) => {
                        e.preventDefault();
                        goToSlide(index);
                    });
                });
                
                // Add click events to box navigation items
                const boxNavItems = document.querySelectorAll('.box-nav-item');
                boxNavItems.forEach((item, index) => {
                    item.addEventListener('click', (e) => {
                        e.preventDefault();
                        goToSlide(index);
                    });
                });
                
                // Pause on hover, resume on leave (desktop)
                const sliderContainer = document.querySelector('.slider-container');
                sliderContainer.addEventListener('mouseenter', pauseSlide);
                sliderContainer.addEventListener('mouseleave', startAutoSlide);
                
                // Enhanced touch events for mobile with better performance
                let touchStartX = 0;
                let touchEndX = 0;
                let touchStartTime = 0;
                
                sliderTrack.addEventListener('touchstart', (e) => {
                    touchStartX = e.changedTouches[0].screenX;
                    touchStartTime = Date.now();
                    pauseSlide();
                }, { passive: true });
                
                sliderTrack.addEventListener('touchend', (e) => {
                    touchEndX = e.changedTouches[0].screenX;
                    handleSwipe();
                    startAutoSlide();
                }, { passive: true });
                
                function handleSwipe() {
                    const swipeThreshold = 50;
                    const timeThreshold = 300; // Maximum time for a swipe
                    const diff = touchStartX - touchEndX;
                    const timeDiff = Date.now() - touchStartTime;
                    
                    // Only register as swipe if it's fast enough and far enough
                    if (Math.abs(diff) > swipeThreshold && timeDiff < timeThreshold) {
                        if (diff > 0) {
                            nextSlide();
                        } else {
                            prevSlide();
                        }
                    }
                }
            }
            
            // Go to specific slide (user-triggered)
            function goToSlide(index) {
                pauseSlide();
                updateActiveSlide(index);
                startAutoSlide();
            }
            
            // Update active slide with improved transition handling and robust error handling
            function updateActiveSlide(newIndex) {
                // Get all slides and navigation elements
                const allSlides = document.querySelectorAll('.slide');
                const allDots = document.querySelectorAll('.slider-dot');
                const boxNavItems = document.querySelectorAll('.box-nav-item');
                
                // Validate slides exist
                if (!allSlides || allSlides.length === 0) {
                    console.error('No slides found');
                    return;
                }
                
                // Calculate the new index with boundary checks
                const totalSlides = allSlides.length;
                newIndex = newIndex % totalSlides;
                if (newIndex < 0) newIndex = totalSlides - 1;
                
                // Always update for infinite scroll (remove the check that prevents same index)
                console.log('Updating to slide:', newIndex, 'Total slides:', totalSlides);
                
                // Clear all classes first to prevent blur issues
                allSlides.forEach(slide => {
                    slide.classList.remove('active', 'prev', 'next');
                });
                
                // Force immediate update without requestAnimationFrame for reliability
                // Update current slide index
                const oldSlide = currentSlide;
                currentSlide = newIndex;
                
                // Update slide classes for smooth 3D transitions
                allSlides.forEach((slide, index) => {
                    if (index === currentSlide) {
                        slide.classList.add('active');
                    } else if (index === (currentSlide - 1 + totalSlides) % totalSlides) {
                        slide.classList.add('prev');
                    } else if (index === (currentSlide + 1) % totalSlides) {
                        slide.classList.add('next');
                    }
                });
                
                // Update dots with smooth transitions
                allDots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === newIndex);
                });
                
                // Update box navigation
                boxNavItems.forEach((item, index) => {
                    item.classList.toggle('active', index === newIndex);
                });
                
                console.log('Slide transition complete:', currentSlide);
            }
            
            // Next slide with robust boundary handling
            function nextSlide() {
                const totalSlides = document.querySelectorAll('.slide').length;
                const nextIndex = (currentSlide + 1) % totalSlides;
                console.log('Next slide: from', currentSlide, 'to', nextIndex);
                updateActiveSlide(nextIndex);
            }
            
            // Previous slide with robust boundary handling
            function prevSlide() {
                const totalSlides = document.querySelectorAll('.slide').length;
                const prevIndex = (currentSlide - 1 + totalSlides) % totalSlides;
                console.log('Prev slide: from', currentSlide, 'to', prevIndex);
                updateActiveSlide(prevIndex);
            }
            
            // Auto slide with improved performance and robust cycling
            function startAutoSlide() {
                clearInterval(slideInterval);
                const totalSlides = document.querySelectorAll('.slide').length;
                if (totalSlides <= 1) return;
                
                slideInterval = setInterval(() => {
                    const nextIndex = (currentSlide + 1) % totalSlides;
                    console.log('Auto slide: from', currentSlide, 'to', nextIndex);
                    
                    // Force update even if same index (prevents stuck on last slide)
                    if (nextIndex === 0 && currentSlide === totalSlides - 1) {
                        currentSlide = -1; // Force reset
                    }
                    
                    updateActiveSlide(nextIndex);
                }, 4000); // Faster, snappier slide change
            }
            
            // Pause auto slide
            function pauseSlide() {
                clearInterval(slideInterval);
            }
            
            // Initialize slider after ensuring everything is loaded
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initSlider);
            } else {
                // If DOM is already loaded
                setTimeout(initSlider, 300);
            }
        });
        
        // Enhanced welcome text animation
        document.addEventListener('DOMContentLoaded', function() {
            const welcomeText = document.getElementById('welcomeText');
            if (welcomeText) {
                // Add animation class after a short delay
                setTimeout(() => {
                    welcomeText.classList.add('animate-welcome');
                    
                    // Add floating effect
                    setInterval(() => {
                        welcomeText.style.transform = 'translateY(-5px)';
                        setTimeout(() => {
                            welcomeText.style.transform = 'translateY(0)';
                        }, 1000);
                    }, 2000);
                }, 100);
                
                // Enhanced hover effect
                welcomeText.addEventListener('mouseover', function() {
                    this.style.transform = 'scale(1.05) translateY(-5px)';
                    this.style.transition = 'all 0.3s ease';
                });
                
                welcomeText.addEventListener('mouseout', function() {
                    this.style.transform = 'scale(1) translateY(0)';
                });
            }
        });

        /* counter js */

        (function ($) {
            $.fn.countTo = function (options) {
                options = options || {};

                return $(this).each(function () {
                    // set options for current element
                    var settings = $.extend({}, $.fn.countTo.defaults, {
                        from: $(this).data('from'),
                        to: $(this).data('to'),
                        speed: $(this).data('speed'),
                        refreshInterval: $(this).data('refresh-interval'),
                        decimals: $(this).data('decimals')
                    }, options);

                    // how many times to update the value, and how much to increment the value on each update
                    var loops = Math.ceil(settings.speed / settings.refreshInterval),
                            increment = (settings.to - settings.from) / loops;

                    // references & variables that will change with each update
                    var self = this,
                            $self = $(this),
                            loopCount = 0,
                            value = settings.from,
                            data = $self.data('countTo') || {};

                    $self.data('countTo', data);

                    // if an existing interval can be found, clear it first
                    if (data.interval) {
                        clearInterval(data.interval);
                    }
                    data.interval = setInterval(updateTimer, settings.refreshInterval);

                    // initialize the element with the starting value
                    render(value);

                    function updateTimer() {
                        value += increment;
                        loopCount++;

                        render(value);

                        if (typeof (settings.onUpdate) == 'function') {
                            settings.onUpdate.call(self, value);
                        }

                        if (loopCount >= loops) {
                            // remove the interval
                            $self.removeData('countTo');
                            clearInterval(data.interval);
                            value = settings.to;

                            if (typeof (settings.onComplete) == 'function') {
                                settings.onComplete.call(self, value);
                            }
                        }
                    }

                    function render(value) {
                        var formattedValue = settings.formatter.call(self, value, settings);
                        $self.html(formattedValue);
                    }
                });
            };

            $.fn.countTo.defaults = {
                from: 0, // the number the element should start at
                to: 0, // the number the element should end at
                speed: 1000, // how long it should take to count between the target numbers
                refreshInterval: 100, // how often the element should be updated
                decimals: 0, // the number of decimal places to show
                formatter: formatter, // handler for formatting the value before rendering
                onUpdate: null, // callback method for every time the element is updated
                onComplete: null       // callback method for when the element finishes updating
            };

            function formatter(value, options) {
                return value.toFixed(options.decimals);
            }
        }(jQuery));

        jQuery(function ($) {
            // custom formatting example
            $('.count-number').data('countToOptions', {
                formatter: function (value, options) {
                    return value.toFixed(options.decimals).replace(/\B(?=(?:\d{3})+(?!\d))/g, ',');
                }
            });

            // start all the timers
            $('.timer').each(count);

            function count(options) {
                var $this = $(this);
                options = $.extend({}, options || {}, $this.data('countToOptions') || {});
                $this.countTo(options);
            }
        });
    </script>
    
    <style>
        @keyframes wave {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        /* Ultra Creative 3D Welcome Section */
        @keyframes float3d {
            0%, 100% { transform: translateY(0) rotateX(0) rotateY(0); }
            25% { transform: translateY(-10px) rotateX(5deg) rotateY(5deg); }
            75% { transform: translateY(10px) rotateX(-5deg) rotateY(-5deg); }
        }
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        @keyframes aitfLogoBounceIn {
            0% {
                transform: translateY(40px) scale(0.9);
                opacity: 0;
                filter: brightness(1.2);
            }
            60% {
                transform: translateY(-8px) scale(1.06);
                opacity: 1;
            }
            80% {
                transform: translateY(3px) scale(0.99);
            }
            100% {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }
        
        @keyframes aitfWordSlideUp {
            from {
                transform: translateY(14px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes aitfWordFadeScale {
            from {
                transform: scale(0.96);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        @keyframes aitfTypeIn {
            from {
                max-width: 0ch;
            }
            to {
                max-width: 9ch;
            }
        }
        
        @keyframes aitfWordHighlight {
            0% {
                opacity: 0;
                text-shadow: none;
            }
            60% {
                opacity: 1;
                text-shadow: 0 0 10px rgba(129, 140, 248, 0.9), 0 0 24px rgba(56, 189, 248, 0.6);
            }
            100% {
                opacity: 1;
                text-shadow: 0 0 6px rgba(129, 140, 248, 0.6), 0 0 14px rgba(56, 189, 248, 0.4);
            }
        }
        
        @keyframes aitfPillIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes aitfMetaIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes cardSheen {
            0%, 35% {
                transform: translate3d(-30%, 0, 0);
                opacity: 0;
            }
            40%, 55% {
                transform: translate3d(10%, 0, 0);
                opacity: 1;
            }
            60%, 100% {
                transform: translate3d(40%, 0, 0);
                opacity: 0;
            }
        }
        
        /* Subtle continuous motion */
        @keyframes aitfLogoBreath {
            0%, 100% {
                transform: translateY(0) scale(1);
                text-shadow: 0 0 10px rgba(129, 140, 248, 0.7), 0 0 26px rgba(56, 189, 248, 0.5);
            }
            50% {
                transform: translateY(-3px) scale(1.03);
                text-shadow: 0 0 14px rgba(129, 140, 248, 0.9), 0 0 34px rgba(56, 189, 248, 0.7);
            }
        }
        
        @keyframes aitfWordDrift {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-2px);
            }
        }
        
        @keyframes aitfMetaGlow {
            0%, 100% {
                text-shadow: 0 0 0 rgba(56, 189, 248, 0.0);
                opacity: 0.9;
            }
            50% {
                text-shadow: 0 0 10px rgba(56, 189, 248, 0.7);
                opacity: 1;
            }
        }
        
        .welcome-3d-container {
            perspective: 1000px;
            margin: 40px 0;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 200px;
        }
        
        .aitf-hero-text {
            position: relative;
            z-index: 2;
            text-align: center;
            color: #f9fafb;
            font-family: "Poppins", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        
        .aitf-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 4px 16px;
            margin-bottom: 12px;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.5);
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.65), rgba(30, 64, 175, 0.35));
            box-shadow: 0 0 0 1px rgba(15, 23, 42, 0.7);
            font-size: 0.72rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #e5e7eb;
            backdrop-filter: blur(16px);
            opacity: 0;
            transform: translateY(8px);
            animation: aitfPillIn 0.4s 0.1s ease-out forwards;
        }
        
        .aitf-logo-abbr {
            font-size: clamp(2.8rem, 9vw, 5.2rem);
            font-weight: 800;
            line-height: 1.05;
            letter-spacing: -0.02em;
            margin: 0 0 4px 0;
            position: relative;
            display: inline-block;
            opacity: 0;
            transform: translateY(20px) scale(1.1);
            animation: aitfLogoBounceIn 0.9s 0.2s cubic-bezier(0.18, 0.89, 0.32, 1.28) forwards,
                       aitfLogoBreath 7s 1.4s ease-in-out infinite;
            color: transparent;
        }
        
        .aitf-logo-abbr::before {
            content: 'A';
            position: absolute;
            left: -0.1em; /* Slight left adjustment */
            top: 0;
            color: #8B2F2F; /* Solid reddish-brown for A */
            -webkit-text-fill-color: #8B2F2F;
        }
        
        .aitf-logo-abbr::after {
            content: 'IT';
            position: absolute;
            left: 0.95em; /* Position after A */
            top: 0;
            color: #1A2A5A; /* Dark blue for IT */
            -webkit-text-fill-color: #1A2A5A;
            letter-spacing: 0.05em;
        }
        
        .aitf-logo-abbr span {
            position: absolute;
            left: 2.2em; /* Moved F even closer to IT */
            top: 0;
            color: #8B2F2F; /* Reddish-brown for F */
            -webkit-text-fill-color: #8B2F2F;
            margin-left: -0.1em; /* Fine-tune position */
        }
        
        .aitf-full-form-text {
            color: #e5e7eb;
            font-size: 1.1rem;
            margin-top: 0.5rem;
            font-weight: 400;
            letter-spacing: 0.03em;
            text-align: center;
            opacity: 0.9;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        .aitf-full-form {
            margin-top: 1rem;
            display: flex;
            justify-content: center;
            gap: 0.7rem;
            flex-wrap: wrap;
            font-size: clamp(0.95rem, 1.6vw, 1.15rem);
            font-weight: 500;
            color: #e5e7eb;
            letter-spacing: 0.08em;
        }
        
        .aitf-word {
            display: inline-block;
            opacity: 0;
        }
        
        .word-1 {
            animation: aitfWordSlideUp 0.55s 1.1s cubic-bezier(0.16, 0.84, 0.44, 1) forwards,
                       aitfWordDrift 9s 3s ease-in-out infinite;
        }
        
        .word-2 {
            animation: aitfWordFadeScale 0.55s 1.55s cubic-bezier(0.16, 0.84, 0.44, 1) forwards,
                       aitfWordDrift 9s 3.4s ease-in-out infinite;
        }
        
        .word-3 {
            opacity: 1;
            overflow: hidden;
            white-space: nowrap;
            max-width: 0ch;
            animation: aitfTypeIn 0.9s 2s steps(9, end) forwards,
                       aitfWordDrift 9s 3.8s ease-in-out infinite;
        }
        
        .word-4 {
            animation: aitfWordHighlight 0.6s 2.9s cubic-bezier(0.16, 0.84, 0.44, 1) forwards,
                       aitfWordDrift 9s 4.2s ease-in-out infinite;
        }
        
        .aitf-meta {
            margin-top: 1.1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.7rem;
            padding: 0.3rem 0.6rem;
            font-size: 0.78rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(226, 232, 240, 0.85);
            opacity: 0;
            transform: translateY(10px);
            animation: aitfMetaIn 0.55s 3.1s cubic-bezier(0.16, 0.84, 0.44, 1) forwards,
                       aitfMetaGlow 8s 4s ease-in-out infinite;
        }
        
        .meta-dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: radial-gradient(circle, #38bdf8, transparent 70%);
            box-shadow: 0 0 12px rgba(56, 189, 248, 0.9);
        }
        
        .meta-separator {
            width: 42px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(148, 163, 184, 0.8), transparent);
            opacity: 0.8;
        }
        
        .meta-label {
            white-space: nowrap;
        }
        
        .aitf-underline {
            margin-top: 18px;
            width: 72%;
            max-width: 420px;
            margin-left: auto;
            margin-right: auto;
            background: linear-gradient(90deg, transparent, rgba(37, 99, 235, 0.25), rgba(56, 189, 248, 0.9), rgba(37, 99, 235, 0.6), transparent);
        }
        
        .aitf-underline::after {
            animation: slide 2.4s ease-out infinite;
            opacity: 0.95;
        }
        
        .welcome-3d-underline {
            height: 3px;
            width: 100%;
            background: linear-gradient(90deg, transparent, #4fc3f7, transparent);
            margin: 10px auto;
            position: relative;
            overflow: hidden;
        }
        
        .welcome-3d-underline::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
            animation: slide 2s linear infinite;
        }
        
        .welcome-3d-glow {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at center, rgba(79, 195, 247, 0.1), transparent 70%);
            z-index: 1;
            animation: pulse 4s ease-in-out infinite;
        }
        
        .welcome-3d-particles {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1;
            overflow: hidden;
            border-radius: 20px;
        }
        
        .welcome-3d-particles::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            right: -50%;
            bottom: -50%;
            background: 
                radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 80% 70%, rgba(79, 195, 247, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 40% 60%, rgba(255, 255, 255, 0.1) 0%, transparent 20%);
            animation: rotate 60s linear infinite;
            opacity: 0.5;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        @keyframes slide {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        @keyframes colorChange {
            0% { 
                color: #ffffff;
                text-shadow: 0 0 10px #ffffff, 0 0 20px #ffffff, 0 0 30px #00aaff, 0 0 40px #00aaff;
                border-color: #00aaff;
            }
            33% { 
                color: #ffff00;
                text-shadow: 0 0 10px #ffff00, 0 0 20px #ffff00, 0 0 30px #ffaa00, 0 0 40px #ffaa00;
                border-color: #ffaa00;
            }
            66% { 
                color: #ff9900;
                text-shadow: 0 0 10px #ff9900, 0 0 20px #ff9900, 0 0 30px #ff5500, 0 0 40px #ff5500;
                border-color: #ff5500;
            }
            100% { 
                color: #ffffff;
                text-shadow: 0 0 10px #ffffff, 0 0 20px #ffffff, 0 0 30px #00aaff, 0 0 40px #00aaff;
                border-color: #00aaff;
            }
        }
        
        @keyframes borderGlow {
            0%, 100% { 
                box-shadow: 0 0 10px 3px rgba(0, 170, 255, 0.8),
                            0 0 20px 6px rgba(0, 140, 255, 0.5),
                            0 0 30px 9px rgba(0, 110, 255, 0.3);
            }
            50% { 
                box-shadow: 0 0 15px 5px rgba(0, 200, 255, 0.9),
                            0 0 25px 10px rgba(0, 170, 255, 0.6),
                            0 0 35px 15px rgba(0, 140, 255, 0.3);
            }
        }
        
        .animate-welcome {
            animation: colorChange 6s infinite ease-in-out !important;
            display: inline-block !important;
            padding: 20px 40px !important;
            font-weight: 800 !important;
            font-size: 3.2rem !important;
            letter-spacing: 1px;
            border: 3px solid #00aaff;
            border-radius: 15px;
            background: 
                radial-gradient(circle at 10px 10px, rgba(255,255,255,0.1) 2px, transparent 0) 0 0 / 30px 30px,
                radial-gradient(circle at 25px 25px, rgba(255,255,255,0.1) 2px, transparent 0) 15px 15px / 30px 30px,
                rgba(0, 0, 0, 0.3) !important;
            backdrop-filter: blur(5px);
            transition: all 0.5s ease;
            position: relative;
            overflow: hidden;
            animation: borderGlow 4s infinite alternate !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .animate-welcome:hover {
            transform: scale(1.05) !important;
            background: 
                radial-gradient(circle at 10px 10px, rgba(255,255,255,0.15) 2px, transparent 0) 0 0 / 30px 30px,
                radial-gradient(circle at 25px 25px, rgba(255,255,255,0.15) 2px, transparent 0) 15px 15px / 30px 30px,
                rgba(0, 0, 0, 0.4) !important;
            box-shadow: 0 0 30px rgba(0, 200, 255, 0.8) !important;
        }
    </style>
    
    <script>
        // Animate welcome text characters
        document.addEventListener('DOMContentLoaded', function() {
            const chars = document.querySelectorAll('.welcome-3d-char');
            chars.forEach((char, index) => {
                // Random delay for each character
                const delay = Math.random() * 0.5;
                char.style.animation = `float 3s ease-in-out ${delay}s infinite`;
                
                // Add mouse move effect
                char.addEventListener('mousemove', (e) => {
                    const rect = char.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;
                    
                    const angleX = (y - centerY) * 0.05;
                    const angleY = (centerX - x) * 0.05;
                    
                    char.style.transform = `rotateX(${angleX}deg) rotateY(${angleY}deg) translateZ(20px)`;
                });
                
                char.addEventListener('mouseleave', () => {
                    char.style.transform = 'rotateX(0) rotateY(0) translateZ(0)';
                });
            });
            
            // Create floating particles
            const particlesContainer = document.querySelector('.welcome-3d-particles');
            if (particlesContainer) {
                for (let i = 0; i < 20; i++) {
                    const particle = document.createElement('div');
                    particle.className = 'floating-particle';
                    particle.style.cssText = `
                        position: absolute;
                        width: ${Math.random() * 10 + 5}px;
                        height: ${Math.random() * 10 + 5}px;
                        background: rgba(255, 255, 255, ${Math.random() * 0.5 + 0.1});
                        border-radius: 50%;
                        left: ${Math.random() * 100}%;
                        top: ${Math.random() * 100}%;
                        animation: float ${Math.random() * 5 + 5}s ease-in-out infinite;
                        animation-delay: ${Math.random() * 5}s;
                        opacity: ${Math.random() * 0.5 + 0.1};
                    `;
                    particlesContainer.appendChild(particle);
                }
            }
        });
        
        // Add smooth hover effect for all buttons with class btn-light
        document.querySelectorAll('.btn-light').forEach(button => {
            button.addEventListener('mouseover', function() {
                this.style.transform = 'translateY(-3px)';
                this.style.boxShadow = '0 7px 20px rgba(0, 0, 0, 0.2)';
            });
            
            button.addEventListener('mouseout', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 4px 15px rgba(0, 0, 0, 0.1)';
            });
        });
        
        // Audit Flow Dropdown Toggle Function
        function toggleStep(element) {
            const step = element.closest('.flow-step');
            const stepNumber = parseInt(step.dataset.step);
            const allSteps = document.querySelectorAll('.flow-step');
            
            // Check if this step is already active
            const isAlreadyActive = step.classList.contains('active');
            
            // Close all steps first
            allSteps.forEach(otherStep => {
                otherStep.classList.remove('active');
            });
            
            // If the clicked step was not already active, activate it and all previous steps
            if (!isAlreadyActive) {
                allSteps.forEach(otherStep => {
                    const otherStepNumber = parseInt(otherStep.dataset.step);
                    if (otherStepNumber <= stepNumber) {
                        otherStep.classList.add('active');
                    }
                });
            }
            
            // Update progress bar based on highest step number
            updateProgress(isAlreadyActive ? 0 : stepNumber);
        }
        
        // Update progress bar
        function updateProgress(highestStepNumber) {
            const totalSteps = document.querySelectorAll('.flow-step').length;
            const progressPercent = (highestStepNumber / totalSteps) * 100;
            
            const progressFill = document.querySelector('.progress-fill');
            const currentStepText = document.querySelector('.current-step');
            
            if (progressFill) {
                progressFill.style.width = progressPercent + '%';
            }
            
            if (currentStepText) {
                currentStepText.textContent = `Step ${highestStepNumber}`;
            }
        }
        
        // Initialize all steps as closed, only step 1 active
        document.addEventListener('DOMContentLoaded', function() {
            const allSteps = document.querySelectorAll('.flow-step');
            // Remove active class from all steps first
            allSteps.forEach(step => {
                step.classList.remove('active');
            });
            
            // Then add active class only to step 1
            const firstStep = document.querySelector('.flow-step[data-step="1"]');
            if (firstStep) {
                firstStep.classList.add('active');
                updateProgress(1);
            }
        });
    </script>
</body>
</html>