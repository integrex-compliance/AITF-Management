<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quality Management System (ISO 9001:2015) | AITF</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
            --text-color: #1f2937;
            --light-bg: #f3f4f6;
            --white: #ffffff;
            --success: #10b981;
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
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
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
        
        /* Course Info */
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
            font-size: 1.2rem;
            display: flex;
            align-items: center;
        }
        
        .info-title i {
            margin-right: 10px;
            color: var(--accent-color);
        }
        
        .info-content p {
            margin-bottom: 0.5rem;
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
        
        /* Custom Bullets */
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
    </style>
    <?php include __DIR__ . '/headsource.php'; ?>
</head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <head>
        <?php include './headerSource.php'; ?>
    </head>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content animate-up">
            <h1>Quality Management System</h1>
            <p class="lead">ISO 9001:2015 Certification Training Program</p>
            <div style="margin-top: 2rem;">
                <span class="highlight" style="padding: 0.5rem 1rem; font-size: 1.1rem;">
                    <i class="fas fa-certificate"></i> Internationally Recognized Certification
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
            <div class="section-title">
                <h2>Course Overview</h2>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="info-card animate-up" style="animation-delay: 0.1s;">
                        <div class="info-title">
                            <i class="fas fa-graduation-cap"></i> Training Program
                        </div>
                        <div class="info-content">
                            <p><strong>Awareness Course:</strong> ISO 9001:2015</p>
                            <p><strong>Duration:</strong> 1 Day Intensive Training</p>
                            <p style="font-size: 0.90rem; color: #6c757d; font-style: italic; margin-top: 8px;">The above duration training is customised as per organizational need. Please contact us in case of requirement.</p>
                            <p><strong>Format:</strong> Interactive Workshop</p>
                        </div>
                    </div>
                    
                    <div class="info-card animate-up" style="animation-delay: 0.2s;">
                        <div class="info-title">
                            <i class="fas fa-award"></i> Course Materials
                        </div>
                        <div class="info-content">
                            <p>Comprehensive QMS Training Slides</p>
                            <p>ISO 9001:2015 Standard Copy</p>
                            <p>Practical Exercise Workbooks</p>
                            <p>Official Certificate of Completion</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="info-card animate-up" style="animation-delay: 0.3s; height: 100%;">
                        <div class="info-title">
                            <i class="fas fa-bullseye"></i> Learning Outcomes
                        </div>
                        <div class="info-content">
                            <p>Master the key concepts and requirements of ISO 9001:2015</p>
                            <p>Understand the process approach to quality management</p>
                            <p>Learn to implement and maintain a QMS effectively</p>
                            <p>Gain practical skills for internal auditing</p>
                            <p>Learn to identify and manage risks in quality processes</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="info-card animate-up" style="animation-delay: 0.4s;">
                        <div class="info-title">
                            <i class="fas fa-users"></i> Who Should Attend?
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="custom-list">
                                    <li>Quality Managers & Representatives</li>
                                    <li>Process Owners</li>
                                    <li>Internal Auditors</li>
                                    <li>Management System Professionals</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="custom-list">
                                    <li>Compliance Officers</li>
                                    <li>Operations Managers</li>
                                    <li>Anyone involved in QMS implementation</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="info-card animate-up" style="animation-delay: 0.5s;">
                        <div class="info-title">
                            <i class="fas fa-book-open"></i> Detailed Course Content
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mt-3" style="color: var(--primary-color);">Introduction to QMS</h5>
                                <ul class="custom-list">
                                    <li>ISO 9000 Family of Standards</li>
                                    <li>Quality Management Principles</li>
                                    <li>Process Approach & PDCA Cycle</li>
                                    <li>Risk-Based Thinking</li>
                                </ul>
                                
                                <h5 class="mt-4" style="color: var(--primary-color);">QMS Requirements</h5>
                                <ul class="custom-list">
                                    <li>Context of the Organization</li>
                                    <li>Leadership & Commitment</li>
                                    <li>Planning & Risk Management</li>
                                    <li>Support & Resources</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mt-3" style="color: var(--primary-color);">Operations & Performance</h5>
                                <ul class="custom-list">
                                    <li>Operational Planning & Control</li>
                                    <li>Design & Development</li>
                                    <li>Production & Service Provision</li>
                                    <li>Monitoring & Measurement</li>
                                </ul>
                                
                                <h5 class="mt-4" style="color: var(--primary-color);">Evaluation & Improvement</h5>
                                <ul class="custom-list">
                                    <li>Internal Audits</li>
                                    <li>Management Review</li>
                                    <li>Nonconformity & Corrective Action</li>
                                    <li>Continual Improvement</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="info-card animate-up" style="animation-delay: 0.6s; background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                        <div class="info-title" style="color: var(--primary-color);">
                            <i class="fas fa-chalkboard-teacher"></i> Course Methodology
                        </div>
                        <div class="row">
                            <div class="col-md-4 text-center mb-3">
                                <div class="p-3" style="background: white; border-radius: 10px; height: 100%;">
                                    <i class="fas fa-comments" style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                                    <h5>Interactive Lectures</h5>
                                    <p>Engaging presentations with real-world examples</p>
                                </div>
                            </div>
                            <div class="col-md-4 text-center mb-3">
                                <div class="p-3" style="background: white; border-radius: 10px; height: 100%;">
                                    <i class="fas fa-tasks" style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                                    <h5>Practical Exercises</h5>
                                    <p>Hands-on activities to reinforce learning</p>
                                </div>
                            </div>
                            <div class="col-md-4 text-center mb-3">
                                <div class="p-3" style="background: white; border-radius: 10px; height: 100%;">
                                    <i class="fas fa-users" style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                                    <h5>Group Discussions</h5>
                                    <p>Share experiences and best practices</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="info-card animate-up" style="animation-delay: 0.7s; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);">
                        <div class="text-center">
                            <h3 style="color: var(--primary-color);">Ready to Enhance Your Quality Management Skills?</h3>
                            <p class="lead">Join our ISO 9001:2015 training program and take the first step towards quality excellence.</p>
                            <a href="#contact" class="btn btn-primary btn-lg mt-3" style="background: var(--primary-color); border: none; padding: 0.8rem 2.5rem; font-weight: 600; letter-spacing: 0.5px;">
                                Enroll Now <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- end section -->
    <?php include './footerSource.php'; ?>
</body>
</html>
