<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Continuity Management System (ISO 22301:2012) | AITF</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #4f46e5;
            --secondary-color: #4338ca;
            --accent-color: #6366f1;
            --text-color: #1f2937;
            --light-bg: #f3f4f6;
            --white: #ffffff;
            --success: #10b981;
            --warning: #f59e0b;
            --info: #3b82f6;
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
            background: linear-gradient(135deg, #4338ca 0%, #3730a3 100%);
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
            background: linear-gradient(120deg, rgba(99, 102, 241, 0.2) 0%, rgba(99, 102, 241, 0) 100%);
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
            border-top: 4px solid var(--info);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--info);
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
        
        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
            <h1>Business Continuity Management System</h1>
            <p class="lead">ISO 22301:2012 Certification Training Program</p>
            <div style="margin-top: 2rem;">
                <span class="highlight" style="padding: 0.5rem 1rem; font-size: 1.1rem;">
                    <i class="fas fa-shield-alt"></i> Ensuring Business Resilience in Times of Disruption
                </span>
            </div>
        </div>
        <div style="position: absolute; bottom: 0; left: 0; width: 100%; overflow: hidden; line-height: 0;">
            <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M1200 120L0 16.48 0 0 1200 0 1200 120z" fill="#ffffff"></path>
            </svg>
        </div>
    </section>

    <!-- Main Content -->
    <section class="section">
        <div class="container">
            <div class="info-card animate-up">
                <div class="info-title">
                    <i class="fas fa-info-circle"></i> About BCMS ISO 22301:2012
                </div>
                <div class="info-content">
                    <p>ISO 22301:2012 specifies requirements to plan, establish, implement, operate, monitor, review, maintain and continually improve a Business Continuity Management System (BCMS). This standard helps organizations prepare for, protect against, and respond to disruptive incidents when they arise.</p>
                    <p>Our comprehensive 1-day awareness course provides participants with the knowledge and skills needed to understand the requirements of ISO 22301 and how to implement an effective BCMS within their organization.</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="info-card animate-up" style="animation-delay: 0.1s;">
                        <div class="info-title">
                            <i class="fas fa-clipboard-list"></i> Course Details
                        </div>
                        <div class="info-content">
                            <p><strong>Training:</strong> Awareness Course: BCMS ISO 22301</p>
                            <p><strong>Duration:</strong> 1 Day (9:00 AM to 6:00 PM)</p>
                            <p style="font-size: 0.90rem; color: #6c757d; font-style: italic; margin-top: 8px;">The above duration training is customised as per organizational need. Please contact us in case of requirement.</p>
                            <p><strong>Maximum Participants:</strong> 20 Delegates</p>
                            <p><strong>Eligibility:</strong> No prior knowledge or experience required</p>
                        </div>
                    </div>
                    
                    <div class="info-card animate-up" style="animation-delay: 0.2s;">
                        <div class="info-title">
                            <i class="fas fa-award"></i> Participants Receive
                        </div>
                        <div class="info-content">
                            <ul class="custom-list">
                                <li>Comprehensive BCMS training slides with exercises</li>
                                <li>ISO 22301:2012 standard overview</li>
                                <li>Certificate of course completion</li>
                                <li>Practical case studies and group activities</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="info-card animate-up" style="animation-delay: 0.3s; height: 100%;">
                        <div class="info-title">
                            <i class="fas fa-graduation-cap"></i> Learning Outcomes
                        </div>
                        <div class="info-content">
                            <p><strong>By the end of this course, you will be able to:</strong></p>
                            <ul class="custom-list">
                                <li>Understand the key concepts and principles of business continuity</li>
                                <li>Identify and prioritize potential threats to business operations</li>
                                <li>Develop and implement a business continuity policy</li>
                                <li>Understand the requirements of ISO 22301:2012</li>
                                <li>Apply business continuity tools and techniques</li>
                                <li>Prepare for and respond to disruptive incidents</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="info-card animate-up" style="animation-delay: 0.4s;">
                <div class="info-title">
                    <i class="fas fa-users"></i> Who Should Attend?
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="custom-list">
                            <li>Business Continuity Managers</li>
                            <li>Risk Management Professionals</li>
                            <li>IT and Security Managers</li>
                            <li>Operations Managers</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="custom-list">
                            <li>Compliance Officers</li>
                            <li>Business Process Owners</li>
                            <li>Anyone involved in business continuity planning</li>
                            <li>Those seeking to understand ISO 22301 requirements</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="info-card animate-up" style="animation-delay: 0.5s; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);">
                <div class="info-title" style="color: var(--primary-color);">
                    <i class="fas fa-chalkboard-teacher"></i> Course Structure & Methodology
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h5 style="color: var(--primary-color);">Course Format</h5>
                        <p>Our training is highly interactive and practical, featuring:</p>
                        <ul class="custom-list">
                            <li>Interactive presentations</li>
                            <li>Group discussions and exercises</li>
                            <li>Real-world case studies</li>
                            <li>Practical examples</li>
                            <li>Brainstorming sessions</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5 style="color: var(--primary-color);">Key Topics Covered</h5>
                        <ul class="custom-list">
                            <li>Introduction to Business Continuity Management</li>
                            <li>ISO 22301:2012 requirements</li>
                            <li>Business Impact Analysis (BIA)</li>
                            <li>Risk Assessment</li>
                            <li>Business Continuity Strategies</li>
                            <li>Incident Response and Recovery</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="info-card animate-up" style="animation-delay: 0.6s;">
                <div class="info-title">
                    <i class="fas fa-user-tie"></i> Our Expert Facilitators
                </div>
                <div class="info-content">
                    <p>Our training is delivered by highly qualified and experienced business continuity professionals who bring real-world expertise to the classroom. Our facilitators are selected based on their:</p>
                    <ul class="custom-list">
                        <li>Extensive experience in BCMS implementation and auditing</li>
                        <li>Proven training and facilitation skills</li>
                        <li>Ability to make complex concepts easy to understand</li>
                        <li>Practical industry knowledge</li>
                        <li>Commitment to participant engagement and learning</li>

                
                    </ul>
                </div>
            </div>
            
            <div class="info-card animate-up mt-4" style="animation-delay: 0.7s; background: linear-gradient(135deg, #4338ca 0%, #3730a3 100%); color: white;">
                <div class="text-center p-4">
                    <h3 style="color: white;">Ready to Build Organizational Resilience?</h3>
                    <p class="lead mb-4">Join our ISO 22301:2012 training program and learn how to protect your business from disruptions.</p>
                   <a href="/contactUS.php" class="btn btn-light btn-lg" style="background: white; color: var(--primary-color); font-weight: 600; padding: 0.8rem 2.5rem; border: none; border-radius: 50px;">
    Enroll Now <i class="fas fa-arrow-right ml-2"></i>
</a>
                </div>
            </div>
            
            <div class="info-card animate-up mt-4" style="animation-delay: 0.8s;">
                <div class="info-title">
                    <i class="fas fa-question-circle"></i> How To Apply
                </div>
                <div class="info-content">
                    <p>To secure your place in this course, please follow these simple steps:</p>
                    <ol>
                        <li><strong>Download</strong> the Participant Nomination Form from our website</li>
                        <li><strong>Complete</strong> the form with your details</li>
                        <li><strong>Email</strong> the completed form to: training1@bsc-icc.com or training2@bsc-icc.com</li>
                        <li><strong>Make payment</strong> to confirm your registration</li>
                    </ol>
                    <div class="mt-3 p-3" style="background: #f8f9fa; border-radius: 8px;">
                        <p class="mb-1"><strong>Important Notes:</strong></p>
                        <ul class="custom-list">
                            <li>Advance payment is required to reserve your seat</li>
                            <li>For cancellations, please notify us at least 2 days before the course start date</li>
                            <li>Transfer of nomination to another participant is acceptable</li>
                            <li>Maximum attendance: 20 delegates per session</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- end section -->
    <?php include './footerSource.php'; ?>
</body>
</html>