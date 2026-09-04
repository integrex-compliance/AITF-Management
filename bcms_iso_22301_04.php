<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/headsource.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --accent-color: #ff6b35;
            --light-bg: #f8f9fa;
            --dark-text: #212529;
            --light-text: #6c757d;
            --gradient-1: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
            --gradient-2: linear-gradient(135deg, #ff6b35 0%, #ff9e2c 100%);
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--dark-text);
            line-height: 1.7;
            background-color: #f8fafc;
            overflow-x: hidden;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-weight: 700;
            line-height: 1.3;
        }
        
        /* Hero Section */
        .hero-banner {
            position: relative;
            height: 80vh;
            min-height: 600px;
            overflow: hidden;
            margin-bottom: 0;
            background: linear-gradient(rgba(13, 110, 253, 0.9), rgba(13, 202, 240, 0.8)), 
                        url('aitfImages/business-continuity-iso02.png') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0% 100%);
        }
        
        .hero-banner:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(ellipse at center, rgba(255,255,255,0.2) 0%, rgba(0,0,0,0.1) 100%);
            z-index: 1;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            max-width: 900px;
            padding: 0 20px;
            animation: fadeInUp 1s ease-out;
        }
        
        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.2);
            letter-spacing: -0.5px;
            line-height: 1.2;
            animation-delay: 0.3s;
        }
        
        .hero-content h3 {
            font-size: 1.5rem;
            font-weight: 400;
            margin-bottom: 2rem;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
            animation-delay: 0.5s;
        }
        
        /* Section Styling */
        .section {
            padding: 60px 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 50px;
            position: relative;
        }
        
        .section-title h2 {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary-color);
            display: inline-block;
            padding-bottom: 15px;
            position: relative;
        }
        
        .section-title h2:after {
            content: '';
            position: absolute;
            width: 80px;
            height: 3px;
            background: var(--accent-color);
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }
        
        /* Card Styling */
        .info-card {
            background: white;
            border-radius: 15px;
            box-shadow: var(--box-shadow);
            padding: 2.5rem;
            margin-bottom: 2rem;
            transition: var(--transition);
            border: none;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .info-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: var(--gradient-1);
            transition: var(--transition);
        }
        
        .info-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.12);
        }
        
        .info-card:hover:before {
            height: 8px;
            background: var(--gradient-2);
        }
        




        .
        .info-card h3 {
            color: var(--dark-text);
            margin-bottom: 1.5rem;
            font-weight: 700;
            position: relative;
            padding-bottom: 1rem;
            font-size: 1.5rem;
        }
        
        .info-card h3:after {
            content: '';
            position: absolute;
            width: 50px;
            height: 3px;
            background: var(--gradient-2);
            bottom: 0;
            left: 0;
            border-radius: 3px;
        }
        
        .info-card p, .info-card li {
            color: var(--dark-text);
            margin-bottom: 1rem;
            font-size: 1.05rem;
            line-height: 1.7;
        }
        
        .info-card ul {
            padding-left: 20px;
        }
        
        .info-card li {
            margin-bottom: 8px;
            position: relative;
            padding-left: 25px;
        }
        
        .fa-arrow-circle-right, .fa-angle-right, .fa-check-circle, .fa-star, .fa-user-tie, .fa-shield-alt, .fa-laptop-code, .fa-search, .fa-project-diagram, .fa-chalkboard-teacher, .fa-users, .fa-lightbulb, .fa-book, .fa-info-circle, .fa-envelope {
            color: var(--primary-color);
            margin-right: 10px;
            transition: var(--transition);
        }
        
        .info-card:hover .fa-arrow-circle-right,
        .info-card:hover .fa-check-circle,
        .info-card:hover .fa-star,
        .info-card:hover .fa-user-tie,
        .info-card:hover .fa-shield-alt,
        .info-card:hover .fa-laptop-code,
        .info-card:hover .fa-search,
        .info-card:hover .fa-project-diagram,
        .info-card:hover .fa-chalkboard-teacher,
        .info-card:hover .fa-users,
        .info-card:hover .fa-lightbulb,
        .info-card:hover .fa-book {
            color: var(--accent-color);
            transform: scale(1.2);
        }
        
        /* Buttons */
        .btn {
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0%;
            height: 100%;
            background: rgba(255,255,255,0.2);
            transition: var(--transition);
            z-index: -1;
        }
        
        .btn:hover:before {
            width: 100%;
        }
        
        .btn-primary {
            background: var(--gradient-1);
            border: none;
        }
        
        .btn-outline-primary {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
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
        
        .animate-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        .delay-1 { animation-delay: 0.2s; }
        .delay-2 { animation-delay: 0.4s; }
        .delay-3 { animation-delay: 0.6s; }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .hero-content h1 {
                font-size: 2.8rem;
            }
            
            .hero-content h3 {
                font-size: 1.3rem;
            }
        }
        
        @media (max-width: 768px) {
            .hero-banner {
                height: 70vh;
                min-height: 500px;
            }
            
            .hero-content h1 {
                font-size: 2.2rem;
            }
            
            .section-title h2 {
                font-size: 1.8rem;
            }
            
            .info-card {
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .hero-content h3 {
                font-size: 1.1rem;
            }
        }
    </style>
</head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>
    
    <!-- Hero Banner -->
    <div class="hero-banner">
        <div class="hero-content animate__animated animate__fadeIn">
            <h1 class="animate-up">Business Continuity Management System</h1>
            <h3 class="animate-up delay-1">ISO 22301:2012 Training & Certification</h3>
            <a href="#course-details" class="btn btn-light btn-lg mt-4 animate-up delay-2">Explore Course</a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="section" id="course-details">
        <div class="container">
            <div class="section-title">
                <h2 class="animate-up">Business Continuity Management System</h2>
                <p class="lead text-muted">Master the art of business continuity with our comprehensive ISO 22301:2012 training program</p>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="info-card">
                        <h3>Course Details</h3>
                        <p><strong>Training:</strong> Internal Auditor Training Course: BCMS 22301</p>
                        <p><strong>Duration:</strong> 2 Days</p>
                        <p style="font-size: 0.90rem; color: #6c757d; font-style: italic; margin-top: 8px;">The above duration training is customised as per organizational need. Please contact us in case of requirement.</p>
                        <p><strong>Timing:</strong> 9:00 AM to 6:00 PM</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="info-card">
                        <h3>Participants Receive</h3>
                        <ul class="list-unstyled">
                            <li><i class="fa fa-check-circle"></i> BCMS Slides including Exercises</li>
                            <li><i class="fa fa-check-circle"></i> BCMS 22301 Standard</li>
                            <li><i class="fa fa-check-circle"></i> Certificate of Course Completion</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="info-card">
                        <h3>Eligibility</h3>
                        <p>No specific knowledge or experience is required. This course is designed for professionals at all levels who want to understand and implement Business Continuity Management Systems.</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="info-card">
                        <h3>Learning Outcomes</h3>
                        <p class="lead">By the end of the course, you will be able to:</p>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li><i class="fa fa-arrow-circle-right"></i> Master the techniques & tools for Business Continuity in organizations</li>
                                    <li><i class="fa fa-arrow-circle-right"></i> Identify and prioritize vulnerabilities and potential threats</li>
                                    <li><i class="fa fa-arrow-circle-right"></i> Develop comprehensive Business Continuity Policies</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li><i class="fa fa-arrow-circle-right"></i> Protect critical business functions during disruptions</li>
                                    <li><i class="fa fa-arrow-circle-right"></i> Build organizational resilience and capability</li>
                                    <li><i class="fa fa-arrow-circle-right"></i> Implement effective BCMS strategies</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="info-card">
                        <h3>Who Should Attend?</h3>
                        <ul class="list-unstyled">
                            <li><i class="fa fa-user-tie"></i> Business Continuity Managers and Consultants</li>
                            <li><i class="fa fa-shield-alt"></i> Risk and Information Security Professionals</li>
                            <li><i class="fa fa-laptop-code"></i> IT and Operations Managers</li>
                            <li><i class="fa fa-search"></i> Internal Auditors and Compliance Officers</li>
                            <li><i class="fa fa-project-diagram"></i> Process Owners and Department Heads</li>
                        </ul>
                    </div>
                </div>
            <br>
                <div class="col-md-6">
                    <div class="info-card">
                        <h3>Course Structure</h3>
                        <p>Our interactive training program runs from 9:00 AM to 6:00 PM and includes:</p>
                        <ul class="list-unstyled">
                            <li><i class="fa fa-chalkboard-teacher"></i> Engaging presentations and discussions</li>
                            <li><i class="fa fa-users"></i> Group exercises and case studies</li>
                            <li><i class="fa fa-lightbulb"></i> Brainstorming sessions</li>
                            <li><i class="fa fa-book"></i> Practical examples and real-world scenarios</li>
                        </ul>
                        <p class="mt-3"><strong>Class Size:</strong> Limited to 10 delegates for optimal learning experience</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="info-card">
                        <h3>Course Content Highlights</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li><i class="fa fa-check-circle text-success"></i> Understanding BCMS best practices</li>
                                    <li><i class="fa fa-check-circle text-success"></i> Designing and implementing BCMS</li>
                                    <li><i class="fa fa-check-circle text-success"></i> Business Continuity Policy development</li>
                                    <li><i class="fa fa-check-circle text-success"></i> Risk assessment and management</li>
                                    <li><i class="fa fa-check-circle text-success"></i> Business Impact Analysis (BIA)</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li><i class="fa fa-check-circle text-success"></i> BCMS implementation strategies</li>
                                    <li><i class="fa fa-check-circle text-success"></i> Internal audit techniques</li>
                                    <li><i class="fa fa-check-circle text-success"></i> Audit reporting and follow-up</li>
                                    <li><i class="fa fa-check-circle text-success"></i> Corrective action planning</li>
                                    <li><i class="fa fa-check-circle text-success"></i> Continuous improvement of BCMS</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="info-card">
                        <h3>Our Expert Facilitators</h3>
                        <p>Our training is delivered by industry experts with extensive experience in Business Continuity Management. Our facilitators are carefully selected based on their:</p>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li><i class="fa fa-star text-warning"></i> <strong>Practical Experience</strong> - Years of hands-on BCMS implementation</li>
                                    <li><i class="fa fa-star text-warning"></i> <strong>Training Expertise</strong> - Proven ability to engage and educate</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li><i class="fa fa-star text-warning"></i> <strong>Industry Knowledge</strong> - Current with latest BCMS standards</li>
                                    <li><i class="fa fa-star text-warning"></i> <strong>Interactive Approach</strong> - Making learning engaging and practical</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="info-card bg-light">
                        <h3 class="text-center">How To Apply</h3>
                        <div class="text-center">
                            <p>Ready to enhance your career with BCMS certification? Here's how to register:</p>
                            <ol class="text-left" style="display: inline-block; text-align: left;">
                                <li>Download the Participant Nomination Form from our website</li>
                                <li>Fill in your details and mail the completed form to:</li>
                            </ol>
                            <div class="mt-3">
                                <a href="mailto:training1@aitf.co.in" class="btn btn-primary btn-lg mr-3">
                                    <i class="fa fa-envelope"></i> training1@aitf.co.in
                                </a>
                                <span class="mx-2">or</span>
                                <a href="mailto:training2@aitf.co.in" class="btn btn-outline-primary btn-lg">
                                    <i class="fa fa-envelope"></i> training2@aitf.co.in
                                </a>
                            </div>
                            <div class="mt-4">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> <strong>Important:</strong> 
                                    <ul class="mb-0 pl-3">
                                        <li>Advance payment is required to confirm your seat</li>
                                        <li>Cancellation must be requested at least 2 days before the course start date</li>
                                        <li>Nomination transfers are acceptable with prior notice</li>
                                    </ul>
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
    
    <!-- Call to Action -->
    <div class="section" style="background-color: var(--primary-color); color: white; padding: 60px 0;">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <h3>Ready to become a BCMS expert?</h3>
                    <p class="mb-0">Join our next training session and take the first step towards mastering Business Continuity Management Systems.</p>
                </div>
                <div class="col-md-4 text-md-right mt-3 mt-md-0">
                    <a href="#contact" class="btn btn-light btn-lg">Contact Us Now</a>
                </div>
            </div>
        </div>
    </div>

    <?php include './footerSource.php'; ?>
    
    <!-- Scripts -->
    <script>
        // Add smooth scrolling to all links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
        
        // Add animation on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const infoCards = document.querySelectorAll('.info-card');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });
            
            infoCards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.5s ease-out';
                observer.observe(card);
            });
        });
    </script>
</body>
</html>
