<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './headSource.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a237e;
            --secondary-color: #0d47a1;
            --accent-color: #2962ff;
            --light-bg: #f8f9fa;
            --dark-text: #212529;
            --light-text: #f8f9fa;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.8;
            color: var(--dark-text);
            background-color: #f5f7ff;
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
            background: linear-gradient(90deg, #fff, #64b5f6);
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
            box-shadow: 0 4px 15px rgba(41, 98, 255, 0.3);
        }
        
        .btn-apply:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(41, 98, 255, 0.4);
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
    </style>
</head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content animate__animated animate__fadeIn">
            <h1>Information Security Management System</h1>
            <p class="lead">ISO 27001:2013 Training & Certification</p>
            <a href="#apply" class="btn-apply mt-3">Enroll Now</a>
        </div>
        <div class="hero-overlay"></div>
    </section>

    <!-- Course Overview -->
    <section class="content-section">
        <div class="container">
            <div class="section-title">
                <h2>Course Overview</h2>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="info-card animate-on-scroll">
                        <h3><i class="fas fa-graduation-cap"></i> Training Program</h3>
                        <p class="lead">Internal Auditor Training Course: ISMS 27001</p>
                        <p class="text-muted">Become a certified ISMS internal auditor with our comprehensive training program</p>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="info-card animate-on-scroll">
                        <h3><i class="fas fa-award"></i> What You'll Receive</h3>
                        <ul>
                            <li>ISMS Slides including Exercises</li>
                            <li>ISMS 27001 Standard Documentation</li>
                            <li>Certificate of Course Completion</li>
                            <li>Practical Audit Experience</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="info-card animate-on-scroll">
                        <h3><i class="far fa-clock"></i> Course Duration</h3>
                        <div class="d-flex align-items-center mt-3">
                            <div class="text-center p-3 bg-light rounded mr-4">
                                <h2 class="mb-0 text-primary">2</h2>
                                <small>Days</small>
                            </div>
                            <div>
                                <p class="mb-1"><i class="far fa-calendar-alt text-primary mr-2"></i> 9:00 AM - 6:00 PM Daily</p>
                                <p class="mb-1"><i class="fas fa-users text-primary mr-2"></i> Max 20 Participants</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="info-card animate-on-scroll">
                        <h3><i class="fas fa-user-graduate"></i> Who Should Attend</h3>
                        <p>This course is ideal for:</p>
                        <ul>
                            <li>IT Professionals</li>
                            <li>Information Security Officers</li>
                            <li>Compliance Managers</li>
                            <li>Internal Auditors</li>
                            <li>Anyone involved in ISMS implementation</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Learning Outcomes -->
    <section class="content-section bg-light">
        <div class="container">
            <div class="section-title">
                <h2>Learning Outcomes</h2>
                <p class="lead">By the end of this course, you will be able to:</p>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="info-card animate-on-scroll">
                        <h3><i class="fas fa-shield-alt"></i> Core Competencies</h3>
                        <ul>
                            <li>Master ISMS 27001:2013 requirements</li>
                            <li>Conduct internal ISMS audits effectively</li>
                            <li>Identify and manage information security risks</li>
                            <li>Understand the audit process and techniques</li>
                            <li>Prepare and present audit reports</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="info-card animate-on-scroll">
                        <h3><i class="fas fa-tasks"></i> Key Content Areas</h3>
                        <ul>
                            <li>ISMS requirements and controls</li>
                            <li>Risk assessment methodologies</li>
                            <li>Audit planning and preparation</li>
                            <li>Audit reporting and follow-up</li>
                            <li>Corrective action management</li>
                            <li>Continuous improvement of ISMS</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Course Facilitators -->
    <section class="content-section">
        <div class="container">
            <div class="section-title">
                <h2>Our Expert Facilitators</h2>
                <p class="lead">Learn from industry experts with real-world experience</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="info-card animate-on-scroll">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center mb-3 mb-md-0">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto" style="width: 150px; height: 150px;">
                                    <i class="fas fa-user-tie" style="font-size: 4rem;"></i>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <h3>Meet Our Experts</h3>
                                <p>Our facilitators are seasoned professionals with extensive experience in Information Security Management Systems. They bring a wealth of practical knowledge and real-world insights to the training room.</p>
                                <p>With years of experience in both implementing and auditing ISMS, our trainers are equipped to provide you with the skills and knowledge needed to excel in information security management.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- How to Apply -->
    <section id="apply" class="content-section bg-primary text-white">
        <div class="container">
            <div class="section-title">
                <h2 class="text-white">How to Apply</h2>
                <p>Secure your spot in our next training session</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="info-card bg-white text-dark">
                        <h3 class="text-primary"><i class="fas fa-paper-plane"></i> Application Process</h3>
                        <ol class="pl-3">
                            <li>Download the Participant Nomination Form</li>
                            <li>Fill in your details</li>
                            <li>Email the completed form to <strong>training1@bsc-icc.com</strong> or <strong>training2@bsc-icc.com</strong></li>
                            <li>Make the advance payment to confirm your seat</li>
                        </ol>
                        
                        <div class="alert alert-info mt-4">
                            <h5><i class="fas fa-info-circle"></i> Important Notes:</h5>
                            <ul class="mb-0">
                                <li>Maximum class size: 20 participants</li>
                                <li>48-hour cancellation policy applies</li>
                                <li>Nomination transfers are acceptable</li>
                            </ul>
                        </div>
                        
                        <div class="text-center mt-4">
                            <a href="#" class="btn-apply btn-lg">Download Application Form</a>
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
        });
    </script>
</body>
</html>