<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Occupational Health & Safety Management Systems (ISO 45001:2018)</title>
    <?php include './headSource.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a365d;
            --secondary-color: #2b6cb0;
            --accent-color: #f6ad55;
            --light-bg: #f7fafc;
            --dark-bg: #2d3748;
            --success: #38a169;
            --warning: #dd6b20;
            --danger: #e53e3e;
            --gradient-primary: linear-gradient(135deg, #1a365d 0%, #2b6cb0 100%);
            --gradient-accent: linear-gradient(135deg, #f6ad55 0%, #dd6b20 100%);
            --box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.7;
            color: #4a5568;
            background-color: #f8fafc;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }
        
        /* Hero Section */
        .hero-banner {
            position: relative;
            height: 90vh;
            min-height: 600px;
            background: linear-gradient(135deg, rgba(26, 54, 93, 0.9) 0%, rgba(43, 108, 176, 0.9) 100%), 
                        url('aitfImages/occupitional_safety_iso01.jpg') no-repeat center center/cover fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            margin-bottom: 100px;
            clip-path: polygon(0 0, 100% 0, 100% 90%, 0% 100%);
            position: relative;
            overflow: hidden;
        }
        
        .hero-banner:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.6;
            z-index: 1;
        }
        
        .hero-content {
            max-width: 900px;
            padding: 0 30px;
            position: relative;
            z-index: 2;
            animation: fadeInUp 1s ease-out;
        }
        
        .hero-content h1 {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            text-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background: linear-gradient(to right, #fff, #ebf8ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
            letter-spacing: -0.5px;
        }
        
        .hero-content p {
            font-size: 1.5rem;
            margin-bottom: 2.5rem;
            opacity: 0.95;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            font-weight: 300;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            z-index: 1;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .btn:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient-primary);
            z-index: -2;
            border-radius: 50px;
        }
        
        .btn:before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0%;
            height: 100%;
            background: var(--gradient-accent);
            transition: all 0.3s;
            z-index: -1;
            border-radius: 50px;
        }
        
        .btn:hover {
            color: #fff;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .btn:hover:before {
            width: 100%;
        }
        
        .btn-primary {
            color: white;
            border: none;
        }
        
        .btn-outline-primary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .btn-outline-primary:hover {
            background: white;
            color: var(--primary-color);
            border-color: white;
        }
        
        .btn-lg {
            padding: 1rem 2.5rem;
            font-size: 1rem;
        }
        
        .btn i {
            margin-right: 8px;
            font-size: 1.1em;
        }
        
        .btn-group {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin-top: 2rem;
        }
        
        /* Content Sections */
        .section {
            padding: 100px 0;
            position: relative;
        }
        
        .section-bg-light {
            background-color: var(--light-bg);
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 4rem;
            position: relative;
            padding: 0 20px;
        }
        
        .section-title h2 {
            font-size: 2.75rem;
            font-weight: 800;
            color: var(--primary-color);
            display: inline-block;
            position: relative;
            padding-bottom: 20px;
            margin-bottom: 1.5rem;
            line-height: 1.3;
        }
        
        .section-title h2:after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background: var(--gradient-accent);
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }
        
        .section-title .lead {
            font-size: 1.25rem;
            color: #718096;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.7;
        }
        
        /* Course Info Cards */
        .info-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--box-shadow);
            padding: 2.5rem;
            margin-bottom: 2rem;
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
            height: 100%;
            position: relative;
            overflow: hidden;
            background: linear-gradient(145deg, #ffffff, #f0f4f8);
        }
        
        .info-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient-accent);
        }
        
        .info-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0,0,0,0.12);
        }
        
        .info-card h3 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-weight: 700;
            position: relative;
            padding-bottom: 1rem;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .info-card h3 i {
            margin-right: 12px;
            color: var(--accent-color);
            font-size: 1.75rem;
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(246, 173, 85, 0.15);
            border-radius: 50%;
            padding: 10px;
        }
        
        .info-card h3:after {
            content: '';
            position: absolute;
            width: 40px;
            height: 3px;
            background: var(--gradient-accent);
            bottom: 0;
            left: 0;
            border-radius: 2px;
        }
        
        .info-card h4 {
            font-size: 1.25rem;
            color: var(--primary-color);
            margin-bottom: 1.25rem;
            font-weight: 700;
        }
        
        .info-card h5 {
            font-size: 1.1rem;
            color: var(--secondary-color);
            margin-bottom: 0.75rem;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .info-card h5 i {
            margin-right: 8px;
            color: var(--accent-color);
        }
        
        .info-card p, .info-card ul {
            margin-bottom: 1.25rem;
            color: #4a5568;
            line-height: 1.7;
        }
        
        .info-card ul {
            padding-left: 0;
            list-style: none;
        }
        
        .info-card ul li {
            margin-bottom: 0.75rem;
            position: relative;
            padding-left: 2rem;
            color: #4a5568;
            line-height: 1.6;
        }
        
        .info-card ul li:before {
            content: '✓';
            color: var(--success);
            font-weight: bold;
            position: absolute;
            left: 0;
            background: rgba(72, 187, 120, 0.15);
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }
        
        .objective-item {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .objective-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
        }
        
        /* Section Labels & Headings */
        .section-label {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--secondary-color);
            margin-bottom: 6px;
        }
        
        .section-heading {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 14px;
            border-bottom: 3px solid var(--accent-color);
            display: flex;
            align-items: center;
        }
        
        .section-heading i {
            color: var(--secondary-color);
            font-size: 1.2rem;
        }
        
        /* Outcome Items */
        .outcome-item {
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            transition: var(--transition);
        }
        
        .outcome-item:hover {
            border-color: var(--secondary-color);
            box-shadow: 0 4px 12px rgba(43, 108, 176, 0.08);
            transform: translateY(-2px);
        }
        
        .outcome-item h5 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 4px;
        }
        
        /* Module Items */
        .module-item {
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            transition: var(--transition);
        }
        
        .module-item:hover {
            border-color: var(--secondary-color);
            box-shadow: 0 4px 12px rgba(43, 108, 176, 0.08);
            transform: translateY(-2px);
        }
        
        .module-item h5 {
            font-size: 0.98rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .module-number {
            width: 32px;
            height: 32px;
            min-width: 32px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.85rem;
            margin-right: 14px;
        }
        
        /* Duration Badge */
        .duration-badge {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: #ffffff;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 700;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
            
            .info-card {
                padding: 20px;
            }
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in {
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 5px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-color);
        }
    </style>
</head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>
    
    <!-- Hero Banner -->
    <section class="hero-banner">
        <div class="hero-content">
            <h1 class="mb-4">Occupational Health & Safety Management Systems</h1>
            <p class="lead mb-5">Master ISO 45001:2018 with our comprehensive certification training program designed for professionals</p>
            <div class="btn-group">
                <a href="#course-details" class="btn btn-primary btn-lg mr-3">
                    <i class="fas fa-search mr-2"></i> Explore Course
                </a>
                <a href="#contact" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-envelope mr-2"></i> Contact Us
                </a>
            </div>
        </div>
        <div class="scroll-down">
            <a href="#course-details" class="scroll-link">
                <i class="fas fa-chevron-down"></i>
            </a>
        </div>
    </section>
    
    <!-- Scroll Down Indicator -->
    <style>
        .scroll-down {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            animation: bounce 2s infinite;
        }
        
        .scroll-link {
            display: block;
            width: 40px;
            height: 40px;
            border: 2px solid rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            color: white;
            text-align: center;
            line-height: 36px;
            transition: all 0.3s ease;
        }
        
        .scroll-link:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(5px);
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-20px);}
            60% {transform: translateY(-10px);}
        }
        
        /* Course Features */
        .feature-box {
            text-align: center;
            padding: 2rem 1.5rem;
            border-radius: 10px;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: var(--transition);
            height: 100%;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .feature-box .icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            box-shadow: 0 10px 20px rgba(26, 54, 93, 0.2);
            transition: var(--transition);
        }
        
        .feature-box h4 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-weight: 700;
            font-size: 1.25rem;
        }
        
        .feature-box p {
            color: #718096;
            margin-bottom: 0;
            font-size: 0.95rem;
        }
        
        .feature-box:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .feature-box:hover .icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 15px 30px rgba(26, 54, 93, 0.3);
        }
    </style>
    
    <!-- Main Content -->
    <section id="course-details" class="section section-bg-light">
        <div class="container">
            <div class="section-title">
                <h2>Course Overview</h2>
                <p class="lead">Comprehensive training program designed to help you master ISO 45001:2018 standards and implementation</p>
            </div>
            
            <!-- Course Features -->
            <div class="row mb-5">
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="feature-box">
                        <div class="icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <h4>Certification</h4>
                        <p>Earn a recognized certification upon successful completion of the course</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="feature-box">
                        <div class="icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h4>Expert Trainers</h4>
                        <p>Learn from industry professionals with years of practical experience</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="feature-box">
                        <div class="icon">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <h4>Interactive Learning</h4>
                        <p>Engaging sessions with practical exercises and real-world examples</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="feature-box">
                        <div class="icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h4>Networking</h4>
                        <p>Connect with peers and industry professionals</p>
                    </div>
                </div>
            </div>
    <!-- end section -->
            <div class="row">
                <div class="col-md-6">
                    <div class="info-card fade-in" style="animation-delay: 0.2s;">
                        <h3><i class="fas fa-book-open mr-2"></i>Course Details</h3>
                        <p><strong>Course Name:</strong> ISO 45001:2018 Lead Auditor Training Course</p>
                        <p><strong>Duration:</strong> 5 Days</p>
                        <p><strong>Certification:</strong> Internationally Recognized Certificate</p>
                        <p>The above duration training is customised as per organizational need. Please contact us in case of requirement.</p>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="info-card fade-in" style="animation-delay: 0.4s;">
                        <h3><i class="fas fa-star mr-2"></i>Course Features</h3>
                        <p>Comprehensive training on Occupational Health & Safety Management System Standard ISO 45001:2018, focusing on:</p>
                        <ul>
                            <li>Systematic process approach through PDCA model</li>
                            <li>First, second, and third-party audit processes</li>
                            <li>Compliance with ISO 19011 and ISO/IEC 17021-1 standards</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-12 mt-4">
                    <div class="info-card fade-in" style="animation-delay: 0.6s;">
                        <h3><i class="fas fa-bullseye mr-2"></i>Learning Objectives</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="objective-item mb-3">
                                    <h5 class="text-primary"><i class="fas fa-check-circle mr-2"></i>Understanding OH&S Systems</h5>
                                    <p>Explain the purpose of occupational health & safety management systems, standards, and the business benefits of improved performance.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="objective-item mb-3">
                                    <h5 class="text-primary"><i class="fas fa-tasks mr-2"></i>Auditor's Role</h5>
                                    <p>Understand the auditor's responsibilities in planning, conducting, reporting, and following up on OH&S management system audits.</p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="objective-item">
                                    <h5 class="text-primary"><i class="fas fa-clipboard-check mr-2"></i>Practical Application</h5>
                                    <p>Gain hands-on experience in conducting audits to establish conformity with ISO 45001 standards in accordance with ISO 19011 and ISO/IEC 17021-1.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            
            <!-- WHO SHOULD ATTEND -->
            <div class="glass-card fade-in mb-4" style="animation-delay: 0.7s;">
                <div class="section-label"><i class="fas fa-user-friends me-2"></i>Participants</div>
                <h3 class="section-heading"><i class="fas fa-user-friends me-2"></i>WHO SHOULD ATTEND?</h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="outcome-item d-flex align-items-start mb-3 p-3">
                            <div class="icon-box d-flex align-items-center justify-content-center me-3"><i class="fas fa-user-check text-primary"></i></div>
                            <div><h5>Aspiring OH&S Auditors</h5><p class="mb-0 text-muted" style="font-size:0.92rem;">Individuals who want to progress towards Certified OH&S Auditor status</p></div>
                        </div>
                        <div class="outcome-item d-flex align-items-start mb-3 p-3">
                            <div class="icon-box d-flex align-items-center justify-content-center me-3"><i class="fas fa-search text-primary"></i></div>
                            <div><h5>Audit Professionals</h5><p class="mb-0 text-muted" style="font-size:0.92rem;">Individuals interested in conducting first, second and third party audits</p></div>
                        </div>
                        <div class="outcome-item d-flex align-items-start mb-3 p-3">
                            <div class="icon-box d-flex align-items-center justify-content-center me-3"><i class="fas fa-building text-primary"></i></div>
                            <div><h5>Company Leaders</h5><p class="mb-0 text-muted" style="font-size:0.92rem;">Individuals leading their companies to ISO 45001 certification</p></div>
                        </div>
                        <div class="outcome-item d-flex align-items-start mb-3 p-3">
                            <div class="icon-box d-flex align-items-center justify-content-center me-3"><i class="fas fa-hard-hat text-primary"></i></div>
                            <div><h5>Health & Safety Managers</h5><p class="mb-0 text-muted" style="font-size:0.92rem;">Health and safety managers and occupational H&S managers</p></div>
                        </div>
                        <div class="outcome-item d-flex align-items-start mb-3 p-3">
                            <div class="icon-box d-flex align-items-center justify-content-center me-3"><i class="fas fa-shield-alt text-primary"></i></div>
                            <div><h5>Safety Professionals</h5><p class="mb-0 text-muted" style="font-size:0.92rem;">Industrial hygienists and safety professionals</p></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="outcome-item d-flex align-items-start mb-3 p-3">
                            <div class="icon-box d-flex align-items-center justify-content-center me-3"><i class="fas fa-clipboard-check text-primary"></i></div>
                            <div><h5>Internal Audit Coordinators</h5><p class="mb-0 text-muted" style="font-size:0.92rem;">Internal audit coordinators and corporate H&S auditors</p></div>
                        </div>
                        <div class="outcome-item d-flex align-items-start mb-3 p-3">
                            <div class="icon-box d-flex align-items-center justify-content-center me-3"><i class="fas fa-exclamation-triangle text-primary"></i></div>
                            <div><h5>Risk Managers</h5><p class="mb-0 text-muted" style="font-size:0.92rem;">Corporate loss control/risk managers</p></div>
                        </div>
                        <div class="outcome-item d-flex align-items-start mb-3 p-3">
                            <div class="icon-box d-flex align-items-center justify-content-center me-3"><i class="fas fa-stamp text-primary"></i></div>
                            <div><h5>Certification Body Auditors</h5><p class="mb-0 text-muted" style="font-size:0.92rem;">Certification body auditors and third-party assessors</p></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- COURSE CONTENT -->
            <div class="glass-card fade-in mb-4" style="animation-delay: 0.8s;">
                <div class="section-label"><i class="fas fa-book me-2"></i>Curriculum</div>
                <h3 class="section-heading"><i class="fas fa-book me-2"></i>COURSE CONTENT</h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="module-item p-3 mb-3">
                            <div class="d-flex align-items-center"><div class="module-number">1</div><h5 class="mb-0">ISO 45000 Standards Series</h5></div>
                            <p class="text-muted mb-0 mt-2" style="font-size:0.9rem;">Overview of the ISO 45000 family of standards</p>
                        </div>
                        <div class="module-item p-3 mb-3">
                            <div class="d-flex align-items-center"><div class="module-number">2</div><h5 class="mb-0">Auditing Process Approach</h5></div>
                            <p class="text-muted mb-0 mt-2" style="font-size:0.9rem;">Including the PDCA Cycle methodology</p>
                        </div>
                        <div class="module-item p-3 mb-3">
                            <div class="d-flex align-items-center"><div class="module-number">3</div><h5 class="mb-0">Auditing ISO 45001:2018</h5></div>
                            <p class="text-muted mb-0 mt-2" style="font-size:0.9rem;">Standard requirements auditing</p>
                        </div>
                        <div class="module-item p-3 mb-3">
                            <div class="d-flex align-items-center"><div class="module-number">4</div><h5 class="mb-0">OH&S Documentation</h5></div>
                            <p class="text-muted mb-0 mt-2" style="font-size:0.9rem;">Auditing Health & Safety Management Systems Documentation</p>
                        </div>
                        <div class="module-item p-3 mb-3">
                            <div class="d-flex align-items-center"><div class="module-number">5</div><h5 class="mb-0">Audit Planning</h5></div>
                            <p class="text-muted mb-0 mt-2" style="font-size:0.9rem;">Audit Plan development and preparation</p>
                        </div>
                        <div class="module-item p-3 mb-3">
                            <div class="d-flex align-items-center"><div class="module-number">6</div><h5 class="mb-0">Auditing ISO 19011</h5></div>
                            <p class="text-muted mb-0 mt-2" style="font-size:0.9rem;">ISO 19011:2011 standard requirements</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="module-item p-3 mb-3">
                            <div class="d-flex align-items-center"><div class="module-number">7</div><h5 class="mb-0">Roles & Responsibilities</h5></div>
                            <p class="text-muted mb-0 mt-2" style="font-size:0.9rem;">Auditor roles and responsibilities</p>
                        </div>
                        <div class="module-item p-3 mb-3">
                            <div class="d-flex align-items-center"><div class="module-number">8</div><h5 class="mb-0">Checklists & NCR</h5></div>
                            <p class="text-muted mb-0 mt-2" style="font-size:0.9rem;">Checklists, Non Conformance Reports</p>
                        </div>
                        <div class="module-item p-3 mb-3">
                            <div class="d-flex align-items-center"><div class="module-number">9</div><h5 class="mb-0">Audit Reporting</h5></div>
                            <p class="text-muted mb-0 mt-2" style="font-size:0.9rem;">Final Team Meeting / Follow-up procedures</p>
                        </div>
                        <div class="module-item p-3 mb-3">
                            <div class="d-flex align-items-center"><div class="module-number">10</div><h5 class="mb-0">Verification & Records</h5></div>
                            <p class="text-muted mb-0 mt-2" style="font-size:0.9rem;">Verification and Audit Records management</p>
                        </div>
                        <div class="module-item p-3 mb-3">
                            <div class="d-flex align-items-center"><div class="module-number">11</div><h5 class="mb-0">Accreditation & Certification</h5></div>
                            <p class="text-muted mb-0 mt-2" style="font-size:0.9rem;">Certification and Auditor Registration process</p>
                        </div>
                        <div class="module-item p-3 mb-3">
                            <div class="d-flex align-items-center"><div class="module-number">12</div><h5 class="mb-0">Exercises & Examination</h5></div>
                            <p class="text-muted mb-0 mt-2" style="font-size:0.9rem;">Exercises, case study, and Course Examination</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- COURSE STRUCTURE -->
            <div class="glass-card fade-in mb-4" style="animation-delay: 0.9s;">
                <div class="section-label"><i class="fas fa-chalkboard-teacher me-2"></i>Schedule</div>
                <h3 class="section-heading"><i class="fas fa-chalkboard-teacher me-2"></i>COURSE STRUCTURE</h3>
                <div class="outcome-item p-3">
                    <p class="mb-0" style="font-size: 0.98rem; color: #475569; line-height: 1.8;">Course starts at <strong>8:30 A.M.</strong> and ends at <strong>6:30 P.M.</strong> The course includes a series of presentations, Examples, Exercises involving group activities, Brain Storming, Readings and Sharing of Practical Experiences followed by <strong>45 minutes Lunch Break</strong>.</p>
                </div>
            </div>

            <!-- DURATION -->
            <div class="glass-card fade-in mb-4" style="animation-delay: 1s;">
                <div class="section-label"><i class="fas fa-calendar-alt me-2"></i>Duration</div>
                <h3 class="section-heading"><i class="fas fa-clock me-2"></i>DURATION</h3>
                <div class="duration-section p-4" style="text-align: center;">
                    <div class="duration-badge d-inline-block px-5 py-3 mb-3">
                        <i class="fas fa-hourglass-half me-2"></i>5 Days (40 Hours)
                    </div>
                    <p class="mb-0 mt-2" style="font-size: 0.92rem; color: #64748b; font-style: italic;">The above duration training is customised as per organizational need. Please contact us in case of requirement.</p>
                </div>
            </div>
            
        </div>
    </div>
            </div>
        </div>
    </section>
    
    <!-- Additional Course Content -->
    <section class="section bg-light">
        <div class="container">
            <div class="section-title">
                <h2>What You'll Learn</h2>
                <p class="lead">Comprehensive coverage of OH&S management systems and auditing processes</p>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="info-card h-100">
                        <h4><i class="fas fa-book-reader mr-2"></i>Introduction to ISO 45001</h4>
                        <ul>
                            <li>Overview of OH&S management systems</li>
                            <li>Key concepts and requirements</li>
                            <li>PDCA approach</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="info-card h-100">
                        <h4><i class="fas fa-clipboard-list mr-2"></i>Audit Process</h4>
                        <ul>
                            <li>Audit planning and preparation</li>
                            <li>Conducting the audit</li>
                            <li>Reporting and follow-up</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="info-card h-100">
                        <h4><i class="fas fa-certificate mr-2"></i>Certification</h4>
                        <ul>
                            <li>Auditor certification process</li>
                            <li>Maintaining certification</li>
                            <li>Continuous improvement</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-5">
                <a href="#" class="btn btn-primary btn-lg">
                    <i class="fas fa-download mr-2"></i> Download Course Brochure
                </a>
                <a href="#" class="btn btn-outline-primary btn-lg ml-3">
                    <i class="fas fa-calendar-alt mr-2"></i> View Schedule
                </a>
            </div>
        </div>
    </section>
    
    <!-- Call to Action -->
    <section class="py-5" style="background: linear-gradient(135deg, #095a83 0%, #4CAF50 100%); color: white;">
        <div class="container text-center">
            <h2 class="mb-4">Ready to enhance your OH&S management skills?</h2>
            <p class="lead mb-4">Join our professional training program and become a certified ISO 45001:2018 Lead Auditor</p>
            <a href="#" class="btn btn-light btn-lg">
                <i class="fas fa-user-plus mr-2"></i> Enroll Now
            </a>
        </div>
    </section>
    
    <?php include './footerSource.php'; ?>
    
    <!-- Back to Top Button -->
    <a href="#" id="back-to-top" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </a>
    
    <script>
        // Back to Top Button
        const backToTop = document.getElementById('back-to-top');
        
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });
        
        backToTop.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        
        // Add animation to elements when they come into view
        const animateOnScroll = () => {
            const elements = document.querySelectorAll('.fade-in');
            
            elements.forEach(element => {
                const elementPosition = element.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.3;
                
                if (elementPosition < screenPosition) {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }
            });
        };
        
        // Initialize elements as hidden
        document.addEventListener('DOMContentLoaded', () => {
            const elements = document.querySelectorAll('.fade-in');
            elements.forEach(element => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(30px)';
                element.style.transition = 'opacity 0.8s ease-out, transform 0.8s ease-out';
            });
            
            // Initial check in case elements are already in view
            setTimeout(animateOnScroll, 100);
        });
        
        // Check on scroll
        window.addEventListener('scroll', animateOnScroll);
    </script>
</body>
</html>
