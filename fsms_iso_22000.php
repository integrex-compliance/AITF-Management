<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/headsource.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #2e7d32;
            --secondary-color: #1b5e20;
            --accent-color: #4caf50;
            --light-bg: #f8f9fa;
            --dark-text: #212529;
            --light-text: #f8f9fa;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.8;
            color: var(--dark-text);
            background-color: #f5f9f5;
        }
        
        /* Hero Section */
        .hero-section {
            position: relative;
            height: 60vh;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            padding: 2rem;
            background: rgba(0, 0, 0, 0.6);
            border-radius: 15px;
            backdrop-filter: blur(5px);
            animation: fadeInDown 1s ease-out;
        }
        
        .hero-content h1 {
            font-size: 2.8rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(90deg, #fff, #a5d6a7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            opacity: 0.9;
        }
        
        /* Content Sections */
        .content-section {
            padding: 5rem 0;
            background-color: white;
            position: relative;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }
        
        .section-title h2 {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary-color);
            display: inline-block;
            position: relative;
            padding-bottom: 15px;
        }
        
        .section-title h2:after {
            content: '';
            position: absolute;
            width: 60px;
            height: 3px;
            background: var(--accent-color);
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }
        
        /* Card Styling */
        .info-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid var(--accent-color);
        }
        
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }
        
        .info-card h3 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.4rem;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .info-card h3 i {
            margin-right: 10px;
            color: var(--accent-color);
        }
        
        .info-card ul {
            list-style: none;
            padding-left: 0;
        }
        
        .info-card ul li {
            margin-bottom: 0.8rem;
            position: relative;
            padding-left: 30px;
        }
        
        .info-card ul li:before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #4caf50;
            font-weight: bold;
        }
        
        /* Animated Elements */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }
        
        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Button Styling */
        .btn-apply {
            display: inline-block;
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }
        
        .btn-apply:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(76, 175, 80, 0.4);
            color: white;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .section-title h2 {
                font-size: 1.8rem;
            }
        }
        
        /* Animation Keyframes */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .floating {
            animation: float 3s ease-in-out infinite;
        }

        /* Custom bullet points */
        .custom-list {
            list-style: none;
            padding-left: 0;
        }
        
        .custom-list li {
            position: relative;
            padding-left: 30px;
            margin-bottom: 10px;
        }
        
        .custom-list li:before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #4caf50;
            font-weight: bold;
        }
    </style>
</head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
        <div class="hero-content animate__animated animate__fadeIn">
            <h1>Food Safety Management System</h1>
            <p class="lead">ISO 22000:2018 Lead Auditor Training</p>
            <a href="#apply" class="btn-apply mt-3">Enroll Now</a>
        </div>
    </section>

    <!-- Course Overview -->
    <section class="content-section">
        <div class="container">
            <div class="section-title">
                <h2>Course Overview</h2>
                <p class="lead">Become a certified FSMS Lead Auditor with our comprehensive training program</p>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="info-card animate-on-scroll">
                        <h3><i class="fas fa-certificate"></i> Course Details</h3>
                        <p class="lead">ISO 22000:2018 Lead Auditor Training Course</p>
                        <p>This course is certified by the registration governing board and satisfies part of the formal training requirements for individuals seeking registration under the FSMS Lead Auditor Registration Scheme.</p>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="info-card animate-on-scroll">
                        <h3><i class="far fa-calendar-alt"></i> Duration</h3>
                        <div class="d-flex align-items-center mt-3">
                            <div class="text-center p-3 bg-light rounded mr-4">
                                <h2 class="mb-0 text-success">5</h2>
                                <small>Days</small>
                            </div>
                            <div>
                                <p>The below duration training is customised as per organizational need. Please contact us in case of requirement.</p>
                                <p class="mb-1"><i class="far fa-clock text-success mr-2"></i> Full-time intensive training</p>
                                <p class="mb-1"><i class="fas fa-users text-success mr-2"></i> Interactive sessions</p>
                                <p class="mb-1"><i class="fas fa-certificate text-success mr-2"></i> Certification included</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Course Objectives -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="info-card animate-on-scroll">
                        <h3><i class="fas fa-bullseye"></i> Course Objectives</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="custom-list">
                                    <li>Introduction to auditing Food Safety Management Systems (FSMS)</li>
                                    <li>FSMS scope, Terms & Definitions requirements and its Application</li>
                                    <li>Overview of ISO 19011 – Guidelines for Auditing Management Systems</li>
                                    <li>Types of Audit, Audit Principles and Auditor Attributes</li>
                                    <li>Auditing the Organization and its Context</li>
                                    <li>Auditing Leadership and Worker participation</li>
                                    <li>FSMS Policy, PRPs, and HACCP principles</li>
                                    <li>Establishing the HACCP plan, CCPs, and OPRPs</li>
                                    <li>Verification, Validation, and product identification</li>
                                    <li>Product recall system implementation</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="custom-list">
                                    <li>Food safety Legal & regulatory compliance</li>
                                    <li>FSMS Documentation requirements and internal Auditing</li>
                                    <li>Monitoring and measurements of FSMS performance</li>
                                    <li>Food safety Risk Analysis and management</li>
                                    <li>NC Finding and report writing techniques</li>
                                    <li>Audit Checklist Preparation</li>
                                    <li>Audit Team selection and planning</li>
                                    <li>Conducting opening and closing meetings</li>
                                    <li>Document Review and Onsite Audit techniques</li>
                                    <li>Audit report writing and follow-up</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Who Should Attend -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="info-card animate-on-scroll">
                        <h3><i class="fas fa-users"></i> Who Should Attend?</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="custom-list">
                                    <li>Individuals pursuing Exemplar Global Certified FSMS Auditor status</li>
                                    <li>Professionals conducting first, second, and third-party audits</li>
                                    <li>Leaders guiding companies to ISO 22000 certification</li>
                                    <li>Health and safety managers</li>
                                    <li>Industrial hygienists and safety professionals</li>
                                    <li>Food safety managers and coordinators</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="custom-list">
                                    <li>Internal audit coordinators</li>
                                    <li>Corporate or division FSMS auditors</li>
                                    <li>Corporate loss control/risk managers</li>
                                    <li>Certification body auditors</li>
                                    <li>Food safety management personnel</li>
                                    <li>Professionals building a career in Food Safety Management</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Course Content -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="info-card animate-on-scroll">
                        <h3><i class="fas fa-book"></i> Course Content</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="custom-list">
                                    <li>Understanding Food Safety Management Systems</li>
                                    <li>ISO 22000:2018 Standard Requirements</li>
                                    <li>HACCP Principles and Implementation</li>
                                    <li>Prerequisite Programs (PRPs) and Operational PRPs</li>
                                    <li>Hazard Analysis and Risk Assessment</li>
                                    <li>Establishing Critical Control Points (CCPs)</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="custom-list">
                                    <li>Audit Planning and Preparation</li>
                                    <li>Conducting Document Reviews</li>
                                    <li>On-site Audit Techniques</li>
                                    <li>Non-conformity Reporting</li>
                                    <li>Audit Reporting and Follow-up</li>
                                    <li>Certification Process and Maintenance</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Learning Outcomes -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="info-card animate-on-scroll">
                        <h3><i class="fas fa-graduation-cap"></i> Learning Outcomes</h3>
                        <p>Upon completion of this course, participants will be able to:</p>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="custom-list">
                                    <li>Understand the purpose and benefits of a Food Safety Management System</li>
                                    <li>Interpret the requirements of ISO 22000:2018</li>
                                    <li>Plan and conduct FSMS audits effectively</li>
                                    <li>Identify and document non-conformities</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="custom-list">
                                    <li>Prepare comprehensive audit reports</li>
                                    <li>Understand the certification process</li>
                                    <li>Implement continuous improvement in FSMS</li>
                                    <li>Lead audit teams and manage the audit process</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- How to Apply -->
            <div id="apply" class="row mt-5">
                <div class="col-12">
                    <div class="info-card animate-on-scroll" style="background: #f8f9fa; border-left: 4px solid #4caf50;">
                        <h3 class="text-success"><i class="fas fa-paper-plane"></i> How to Apply</h3>
                        <div class="alert alert-success">
                            <h5><i class="fas fa-info-circle"></i> Application Process</h5>
                            <ol>
                                <li>Download the Participant Nomination Form</li>
                                <li>Fill in your details completely</li>
                                <li>Email the completed form to <strong>training1@bsc-icc.com</strong> or <strong>training2@bsc-icc.com</strong></li>
                                <li>Make the advance payment to confirm your seat</li>
                            </ol>
                        </div>
                        
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-exclamation-triangle"></i> Important Notes</h5>
                            <ul class="mb-0">
                                <li>Limited seats available (maximum 20 participants per batch)</li>
                                <li>48-hour cancellation policy applies</li>
                                <li>Nomination transfers are acceptable with prior notice</li>
                                <li>Early registration is recommended to secure your spot</li>
                            </ul>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="#" class="btn-apply btn-lg">Download Application Form</a>
                            <p class="mt-3 text-muted">For any queries, please contact us at <a href="mailto:training@bsc-icc.com">training@bsc-icc.com</a></p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
    
    <!-- Footer -->
    <?php include './footerSource.php'; ?>
    
    <!-- JavaScript for animations -->
    <script>
        // Animate on scroll functionality
        document.addEventListener('DOMContentLoaded', function() {
            const animateElements = document.querySelectorAll('.animate-on-scroll');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, {
                threshold: 0.1
            });
            
            animateElements.forEach(element => {
                observer.observe(element);
            });
            
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            // Add animation to cards on page load
            setTimeout(() => {
                document.querySelectorAll('.info-card').forEach((card, index) => {
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100 * index);
                });
            }, 500);
        });
    </script>
</body>
</html>
