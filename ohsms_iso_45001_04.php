<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <style> 
            :root {
                --primary-color: #1a2b3c;
                --secondary-color: #2c82e0;
                --accent-color: #ff6b6b;
                --light-bg: #f8fafc;
                --dark-text: #1e293b;
                --light-text: #64748b;
                --gradient: linear-gradient(135deg, #3b82f6, #1e40af);
                --gradient-hover: linear-gradient(135deg, #2563eb, #1e40af);
                --box-shadow: 0 10px 20px rgba(0,0,0,0.08);
                --box-shadow-hover: 0 15px 30px rgba(0,0,0,0.12);
                --transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
                --border-radius: 12px;
            }

            body {
                font-family: 'Poppins', sans-serif;
                color: var(--dark-text);
                line-height: 1.7;
                background-color: #fff;
                overflow-x: hidden;
            }

            /* Hero Section */
            .hero-banner {
                position: relative;
                height: 70vh;
                min-height: 500px;
                overflow: hidden;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.7));
            }

            .gfg {
                position: absolute;
                width: 100%;
                height: 100%;
                object-fit: cover;
                filter: brightness(0.6);
                transition: transform 8s cubic-bezier(0.2, 0.8, 0.2, 1);
                z-index: -1;
            }

            .hero-banner:hover .gfg {
                transform: scale(1.05);
            }

            .hero-content {
                position: relative;
                text-align: center;
                color: white;
                width: 90%;
                max-width: 1000px;
                padding: 2rem;
                opacity: 0;
                animation: fadeInUp 1s ease forwards 0.3s;
                transform: translateY(20px);
            }

            @keyframes fadeInUp {
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .hero-content h1 {
                font-size: 3.5rem;
                font-weight: 800;
                line-height: 1.2;
                margin-bottom: 1.5rem;
                text-shadow: 0 2px 15px rgba(0,0,0,0.2);
                background: linear-gradient(to right, #fff, #e2e8f0);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                animation-delay: 0.2s;
                margin-top: 0;
            }

            /* Main Content */
            .section {
                padding: 5rem 0;
                position: relative;
            }

            .section-title {
                text-align: center;
                margin-bottom: 4rem;
                position: relative;
            }

            .section-title h2 {
                font-size: 2.8rem;
                font-weight: 800;
                color: var(--primary-color);
                position: relative;
                display: inline-block;
                padding-bottom: 1.2rem;
                margin-bottom: 1.5rem;
                line-height: 1.2;
            }

            .section-title h2:after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 50%;
                transform: translateX(-50%);
                width: 100px;
                height: 4px;
                background: var(--gradient);
                border-radius: 4px;
                transition: all 0.4s ease;
            }

            .section-title h2:hover:after {
                width: 150px;
                background: var(--gradient-hover);
            }

            .section-title h2 span {
                color: var(--secondary-color);
            }

            /* Card Styling */
            .feature-card {
                background: white;
                border-radius: var(--border-radius);
                padding: 2.5rem;
                margin-bottom: 2rem;
                box-shadow: var(--box-shadow);
                transition: var(--transition);
                border: 1px solid rgba(0,0,0,0.05);
                position: relative;
                overflow: hidden;
                z-index: 1;
            }

            .feature-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 5px;
                background: var(--gradient);
                transform: scaleX(0);
                transform-origin: left;
                transition: transform 0.6s cubic-bezier(0.65, 0, 0.35, 1);
                z-index: -1;
            }

            .feature-card:hover::before {
                transform: scaleX(1);
            }

            .feature-card:hover {
                transform: translateY(-10px);
                box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            }

            .feature-card h3 {
                color: var(--primary-color);
                margin-bottom: 1.5rem;
                font-weight: 700;
                position: relative;
                padding-bottom: 1.2rem;
                font-size: 1.5rem;
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .feature-card h3 i {
                font-size: 1.8rem;
                background: var(--gradient);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                width: 50px;
                height: 50px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(59, 130, 246, 0.1);
                border-radius: 12px;
                -webkit-text-fill-color: var(--secondary-color);
            }

            .feature-card h3:after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                width: 50px;
                height: 3px;
                background: var(--gradient);
                border-radius: 2px;
            }

            .feature-card p, .feature-card li {
                color: var(--light-text);
                margin-bottom: 1rem;
            }

            /* List Styling */
            .styled-list {
                list-style: none;
                padding-left: 0;
            }

            .styled-list li {
                position: relative;
                padding-left: 2.5rem;
                margin-bottom: 1rem;
            }

            .styled-list li {
                position: relative;
                padding-left: 2.5rem;
                margin-bottom: 1rem;
                transition: all 0.3s ease;
            }

            .styled-list li:before {
                content: '✓';
                position: absolute;
                left: 0;
                top: 0;
                width: 24px;
                height: 24px;
                background: rgba(34, 197, 94, 0.1);
                color: #22c55e;
                font-weight: bold;
                font-size: 0.9rem;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                transition: all 0.3s ease;
            }

            .styled-list li:hover {
                transform: translateX(5px);
            }

            .styled-list li:hover:before {
                background: #22c55e;
                color: white;
                transform: scale(1.1);
            }

            /* Responsive Design */
            @media (max-width: 991px) {
                .hero-content h1 {
                    font-size: 2.5rem;
                }
                
                .section-title h2 {
                    font-size: 2rem;
                }
            }

            @media (max-width: 767px) {
                .hero-content h1 {
                    font-size: 2rem;
                }
                
                .section {
                    padding: 3rem 0;
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

            /* Utility Classes */
            .text-gradient {
                background: var(--gradient);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            .btn-primary {
                background: var(--gradient);
                border: none;
                padding: 14px 36px;
                color: white;
                border-radius: 50px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 1.2px;
                transition: var(--transition);
                box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
                position: relative;
                overflow: hidden;
                z-index: 1;
                font-size: 0.9rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }

            .btn-primary::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: var(--gradient-hover);
                opacity: 0;
                transition: opacity 0.4s ease;
                z-index: -1;
            }

            .btn-primary:hover {
                transform: translateY(-3px) scale(1.02);
                box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
                color: white;
            }

            .btn-primary:hover::before {
                opacity: 1;
            }

            .btn-primary i {
                transition: transform 0.3s ease;
            }

            .btn-primary:hover i {
                transform: translateX(3px);
            }
        </style>
    <head>
        <?php include './headSource.php'; ?>
    </head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <head>
        <?php include './headerSource.php'; ?>
    </head>
    <!-- Start Banner -->
    <div class="hero-banner">
        <img class="gfg" src="aitfImages/occupitional_safety_iso01.jpg" alt="Occupational Health & Safety">
        <div class="hero-content">
            <h1 class="mb-4">Occupational Health & Safety Management System <br><span class="text-gradient">ISO 45001:2018</span></h1>
            <p class="lead mb-4" style="color: #fff; font-size: 1.25rem; max-width: 800px; margin: 0 auto;">Enhance workplace safety and ensure compliance with international standards through our comprehensive training programs</p>
            <a href="#contact" class="btn btn-primary mt-3">Enroll Now</a>
        </div>
    </div>
    <!-- End Banner -->

    <section class="section" style="background-color: #f8f9fa;">
        <div class="container">
            <div class="section-title">
                <h2>Occupational Health & Safety <span>Management System</span></h2>
                <p class="lead text-muted">ISO 45001:2018 Standard Implementation & Training</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="feature-card">
                        <p class="text-center mb-4">ISO 45001 is an international standard for occupational health and safety (OH&S) management systems. It provides a framework to improve employee safety, reduce workplace risks, and create better, safer working conditions worldwide.</p>
                        <div class="text-center mt-4">
                            <a href="#course-details" class="btn btn-primary">View Course Details</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- end section -->
    <!-- section -->
    <section id="course-details" class="section">
        <div class="container">
            <div class="section-title">
                <h2>Course <span>Details</span></h2>
                <p class="text-muted">Comprehensive training for occupational health and safety professionals</p>
            </div>
            
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="feature-card h-100">
                        <h3><i class="fas fa-graduation-cap mr-2 text-primary"></i> Training Program</h3>
                        <p>Our comprehensive training program covers all aspects of ISO 45001:2018 implementation and auditing.</p>
                        <ul class="styled-list">
                            <li>Internal Auditor Course: OHSAS 18001</li>
                            <li>ISO 45001:2018 Transition Training</li>
                            <li>Lead Auditor Certification</li>
                            <li>Implementation Workshop</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <div class="feature-card h-100">
                        <h3><i class="fas fa-clock mr-2 text-primary"></i> Duration</h3>
                        <p>Our flexible training programs are designed to fit your schedule:</p>
                        <ul class="styled-list">
                            <li>Standard Course: 2 Days</li>
                            <li>Intensive Program: 5 Days</li>
                            <li>Weekend Batches Available</li>
                            <li>Customized Training Solutions</li>
                        </ul>
                        <p style="font-size: 0.90rem; color: #6c757d; font-style: italic; margin-top: 12px; padding: 10px; background-color: #f8f9fa; border-radius: 5px;">The above duration training is customised as per organizational need. Please contact us in case of requirement.</p>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="feature-card">
                        <h3><i class="fas fa-gift mr-2 text-primary"></i> What Participants Receive</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="styled-list">
                                    <li><i class="fas fa-check-circle text-success mr-2"></i> Comprehensive OHSAS Training Slides</li>
                                    <li><i class="fas fa-check-circle text-success mr-2"></i> OHSAS 18001 Standard Documentation</li>
                                    <li><i class="fas fa-check-circle text-success mr-2"></i> Sample OH&S Management System Manual</li>
                                    <li><i class="fas fa-check-circle text-success mr-2"></i> Practical Case Studies & Exercises</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="styled-list">
                                    <li><i class="fas fa-check-circle text-success mr-2"></i> Interactive Workbook & Handouts</li>
                                    <li><i class="fas fa-check-circle text-success mr-2"></i> Audit Checklists & Templates</li>
                                    <li><i class="fas fa-check-circle text-success mr-2"></i> Certificate of Completion</li>
                                    <li><i class="fas fa-check-circle text-success mr-2"></i> Post-Training Support</li>
                                </ul>
                            </div>
                        </div>
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-certificate mr-2"></i> <strong>Certification:</strong> A test will be conducted at the end of the training, and all successful candidates will receive an Internal Auditor Certificate for OHSAS 18001.
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3 layout_padding_2">
                    <div class="full" >
                        <p style="color: #095a83; font-weight: bold;">Learning Objective:</p>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="full">
                        <p style="font-weight: bold">Delegates will be able to understand & interpret the requirements of OHSAS 18001 and recognize the value of effective Occupational Health & Safety M S.</p>
                        <p><span class="fa fa-arrow-circle-right"> &nbsp;</span>Understand the requirements of an Occupational Health & Safety MS</p>
                        <p><span class="fa fa-arrow-circle-right"> &nbsp;</span>Plan, Conduct, Evaluate & Close out the internal audits of an Occupational Health & Safety management system</p>
                        <p><span class="fa fa-arrow-circle-right"> &nbsp;</span>Develop a skill to cascade these valuable information to your Core Occupational Health & Safety Co-ordinators with in your organization and develop them as good Internal Auditors.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 layout_padding_2">
                    <div class="full" >
                        <p style="color: #095a83; font-weight: bold;">Course Facilitators:</p>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="full">
                        <p>All course facilitators are highly qualified and experienced both in training and assessment of OH&S Management systems. We strongly believe that the experience in OH&S Management Assessment and Training skills is a right combination to deliver a practical training course. They are selected on their ability to make BSCIC courses both practical and enjoyable.</p>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="feature-card">
                        <h3><i class="fas fa-users mr-2 text-primary"></i> Who Should Attend?</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="styled-list">
                                    <li><i class="fas fa-user-tie text-primary mr-2"></i> Health & Safety Managers</li>
                                    <li><i class="fas fa-hard-hat text-primary mr-2"></i> Safety Officers & Representatives</li>
                                    <li><i class="fas fa-user-shield text-primary mr-2"></i> Risk Management Personnel</li>
                                    <li><i class="fas fa-clipboard-check text-primary mr-2"></i> Internal Auditors</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="styled-list">
                                    <li><i class="fas fa-building text-primary mr-2"></i> Management Representatives</li>
                                    <li><i class="fas fa-project-diagram text-primary mr-2"></i> Process Owners</li>
                                    <li><i class="fas fa-user-graduate text-primary mr-2"></i> Students pursuing HSE courses</li>
                                    <li><i class="fas fa-briefcase text-primary mr-2"></i> Professionals seeking career growth in HSE</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                <div class="col-md-9">
                    <div class="full">
                        <p>This two (2) days course has been developed to provide delegates with the knowledge to begin performing internal audits. Quality Managers, Quality Engineers, Departmental Managers, and any other professional, technical or administrative personnel who have responsibility for managing an internal audit system or who are required to carry out internal audits in accordance with OHSAS 18001 Occupational Health & Safety Management System.</p>
                    </div>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-3 layout_padding_2">
                    <div class="full" >
                        <p style="color: #095a83; font-weight: bold;">Course Content Structure:</p>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="full">
                        <p>Course starts at 9:00 A.M. and ends approximately at 6:00 P.M. The course includes a series of presentations, Examples, Exercises involving group activities, Brain Storming, Case Studies, Readings and Sharing of Practical Experiences. Maximum attendance 10 Delegates.</p>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3 layout_padding_2">
                    <div class="full" >
                        <p style="color: #095a83; font-weight: bold;">Eligibility:</p>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="full">
                        <p>Course delegates should have some knowledge of the principles of Quality Assurance and the content and purpose of the OHSAS 18001 series of standards.</p>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-3 layout_padding_2">
                    <div class="full" >
                        <p style="color: #095a83; font-weight: bold;">How To Apply?:</p>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="full">
                        <p>To apply for this course please obtain/download our Participant Nomination Form and mail to training1@aitf.co.in/training2@aitf.co.in. Please make advance payment* to reserve a seat in our course. For any cancellation please request at least 2 days in advance to the commencement of the course. Transfer of Nomination is acceptable.</p>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
   <!-- end section -->
    
    <?php include './footerSource.php'; ?>
</body>
</html>
