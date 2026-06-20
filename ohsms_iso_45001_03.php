<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Occupational Health & Safety Management System (ISO 45001:2018) | AITF</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a56db;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
            --text-color: #1f2937;
            --light-bg: #f3f4f6;
            --white: #ffffff;
            --success: #10b981;
            --warning: #f59e0b;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-color);
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        /* Hero Section */
        .hero-section {
            position: relative;
            height: 400px;
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            overflow: hidden;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            padding: 0 20px;
        }
        
        .hero-content h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .hero-content p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 1.5rem;
        }
        
        /* Main Content */
        .section {
            padding: 4rem 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }
        
        .section-title h2 {
            font-family: 'Montserrat', sans-serif;
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary-color);
            display: inline-block;
            position: relative;
            padding-bottom: 1rem;
        }
        
        .section-title h2:after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }
        
        /* Info Cards */
        .info-card {
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            padding: 2rem;
            margin-bottom: 2rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }
        
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .info-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
        }
        
        .info-title i {
            margin-right: 10px;
            color: var(--accent-color);
        }
        
        .info-content p {
            margin-bottom: 0.8rem;
            position: relative;
            padding-left: 1.5rem;
        }
        
        .info-content p:before {
            content: '•';
            color: var(--accent-color);
            font-weight: bold;
            position: absolute;
            left: 0;
        }
        
        .highlight {
            background: linear-gradient(120deg, rgba(59, 130, 246, 0.2) 0%, rgba(59, 130, 246, 0) 100%);
            border-radius: 6px;
            padding: 0.2rem 0.5rem;
            font-weight: 500;
        }
        
        .stat-card {
            background: var(--white);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border-top: 4px solid var(--warning);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--warning);
            margin-bottom: 0.5rem;
            line-height: 1;
        }
        
        .stat-label {
            color: var(--text-color);
            font-size: 1rem;
            opacity: 0.9;
        }
        
        /* Custom List */
        .custom-list {
            list-style: none;
            padding-left: 0;
        }
        
        .custom-list li {
            position: relative;
            padding-left: 2rem;
            margin-bottom: 0.8rem;
        }
        
        .custom-list li:before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--success);
            font-weight: bold;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .section-title h2 {
                font-size: 1.8rem;
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
        
        .animate-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }
    </style>
    <?php include './headSource.php'; ?>
</head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <head>
        <?php include './headerSource.php'; ?>
    </head>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content animate-up">
            <h1>Occupational Health & Safety Management System</h1>
            <p class="lead">ISO 45001:2018 Certification Training Program</p>
            <div style="margin-top: 2rem;">
                <span class="highlight" style="padding: 0.5rem 1rem; font-size: 1.1rem;">
                    <i class="fas fa-shield-alt"></i> Creating Safer Workplaces Worldwide
                </span>
            </div>
        </div>
        <div style="position: absolute; bottom: 0; left: 0; width: 100%; overflow: hidden; line-height: 0;">
            <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M1200 120L0 16.48 0 0 1200 0 1200 120z" fill="#ffffff"></path>
            </svg>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="section" style="background-color: #f8fafc; padding: 2rem 0;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center mb-4">
                    <h3 class="mb-4" style="color: var(--primary-color);">The Need for Workplace Safety</h3>
                    <div class="stat-card">
                        <div class="stat-number">2.78 Million+</div>
                        <div class="stat-label">Annual work-related deaths worldwide</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="section">
        <div class="container">
            <div class="info-card animate-up">
                <div class="info-title">
                    <i class="fas fa-info-circle"></i> About ISO 45001:2018
                </div>
                <div class="info-content">
                    <p>ISO 45001:2018 is the international standard for occupational health and safety management systems. It provides a framework to improve employee safety, reduce workplace risks, and create better, safer working conditions worldwide.</p>
                    <p>The burden of occupational injuries and diseases is significant, both for employers/workers and the wider economy, resulting in losses from early retirements, staff absence, and rising insurance premiums.</p>
                    <p>ISO 45001 has replaced the previous OHSAS 18001 standard and follows the Annex SL structure, making it compatible with other ISO management system standards.</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="info-card animate-up" style="animation-delay: 0.1s;">
                        <div class="info-title">
                            <i class="fas fa-user-check"></i> Who Should Attend?
                        </div>
                        <div class="info-content">
                            <ul class="custom-list">
                                <li>Professionals new to ISO 45001 looking to understand the standard</li>
                                <li>OH&S practitioners and consultants</li>
                                <li>Health and safety representatives</li>
                                <li>Internal auditors requiring a refresher</li>
                                <li>Managers responsible for workplace safety</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="info-card animate-up" style="animation-delay: 0.2s;">
                        <div class="info-title">
                            <i class="fas fa-clipboard-check"></i> Course Benefits
                        </div>
                        <div class="info-content">
                            <ul class="custom-list">
                                <li>Understand the requirements of ISO 45001:2018</li>
                                <li>Learn to implement an effective OH&S management system</li>
                                <li>Reduce workplace incidents and improve safety</li>
                                <li>Enhance your organization's reputation</li>
                                <li>Meet legal and regulatory requirements</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="info-card animate-up" style="animation-delay: 0.3s;">
                <div class="info-title">
                    <i class="fas fa-book"></i> Course Content
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mt-3" style="color: var(--primary-color);">Fundamentals</h5>
                        <ul class="custom-list">
                            <li>Introduction to Occupational Health & Safety Management Systems</li>
                            <li>OH&S principles and terminology</li>
                            <li>High-level structure (Annex SL)</li>
                            <li>Plan-Do-Check-Act (PDCA) cycle</li>
                        </ul>
                        
                        <h5 class="mt-4" style="color: var(--primary-color);">Key Requirements</h5>
                        <ul class="custom-list">
                            <li>Context of the organization</li>
                            <li>Leadership and worker participation</li>
                            <li>Planning and risk assessment</li>
                            <li>Support and resources</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mt-3" style="color: var(--primary-color);">Implementation</h5>
                        <ul class="custom-list">
                            <li>Operational planning and control</li>
                            <li>Emergency preparedness and response</li>
                            <li>Performance evaluation</li>
                            <li>Internal audit and management review</li>
                        </ul>
                        
                        <h5 class="mt-4" style="color: var(--primary-color);">Transition from OHSAS 18001</h5>
                        <ul class="custom-list">
                            <li>Key differences and new requirements</li>
                            <li>Transition process and timeline</li>
                            <li>Integration with other management systems</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="info-card animate-up" style="animation-delay: 0.4s; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);">
                <div class="info-title" style="color: var(--primary-color);">
                    <i class="fas fa-graduation-cap"></i> Course Delivery & Certification
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h5 style="color: var(--primary-color);">Course Details</h5>
                        <p><strong>Duration:</strong> 1 Day Intensive Training</p>
                        <p>The above duration training is customised as per organizational need. Please contact us in case of requirement.</p> 
                        <p><strong>Format:</strong> Interactive workshop with practical exercises</p>
                        <p><strong>Materials:</strong> Comprehensive course manual and handouts</p>
                        <p><strong>Assessment:</strong> End-of-course examination</p>
                    </div>
                    <div class="col-md-6">
                        <h5 style="color: var(--primary-color);">Certification</h5>
                        <p>Upon successful completion of the course and passing the examination, participants will receive a Certificate of Achievement in ISO 45001:2018 Foundation Training.</p>
                        <p>This course satisfies the training requirements of ISO 45001 for personnel involved in OH&S management systems.</p>
                    </div>
                </div>
            </div>
            
            <div class="info-card animate-up mt-4" style="animation-delay: 0.5s; background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%); color: white;">
                <div class="text-center p-4">
                    <h3 style="color: white;">Ready to Improve Workplace Safety?</h3>
                    <p class="lead mb-4">Join our ISO 45001:2018 training program and learn how to create a safer work environment.</p>
                    <a href="#contact" class="btn btn-light btn-lg" style="background: white; color: var(--primary-color); font-weight: 600; padding: 0.8rem 2.5rem; border: none; border-radius: 50px;">
                        Enroll Now <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
            
            <div class="info-card animate-up mt-4" style="animation-delay: 0.6s;">
                <div class="info-title">
                    <i class="fas fa-question-circle"></i> How To Apply
                </div>
                <div class="info-content">
                    <p>To apply for this course, please follow these steps:</p>
                    <ol>
                        <li>Download the Participant Nomination Form</li>
                        <li>Complete the form with your details</li>
                        <li>Email the form to: training1@bsc-icc.com or training2@bsc-icc.com</li>
                        <li>Make the required payment to confirm your registration</li>
                    </ol>
                    <p class="mt-3"><strong>Note:</strong> Please make payment in advance to reserve your seat. For cancellations, please notify us at least 2 days before the course start date. Transfer of nomination to another participant is acceptable.</p>
                </div>
            </div>
        </div>
    </section>
   <!-- end section -->
    
    <?php include './footerSource.php'; ?>
</body>
</html>