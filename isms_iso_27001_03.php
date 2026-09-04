<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISMS 27001:2013 - Information Security Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #095a83;
            --secondary-color: #0c7ab0;
            --accent-color: #4CAF50;
            --light-bg: #f8f9fa;
            --card-shadow: 0 4px 12px rgba(0,0,0,0.08);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #fff;
        }
        
        /* Hero Section */
        .hero-banner {
            position: relative;
            height: 60vh;
            overflow: hidden;
        }
        
        .hero-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.7);
        }
        
        .hero-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
            width: 90%;
            max-width: 800px;
        }
        
        .hero-content h1 {
            font-size: 2.8rem;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
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
            display: inline-block;
            font-size: 2.2rem;
            color: var(--primary-color);
            position: relative;
            padding-bottom: 1rem;
        }
        
        .section-title h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: var(--accent-color);
        }
        
        /* Cards */
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            padding: 2rem;
            margin-bottom: 2rem;
            transition: var(--transition);
            border-left: 4px solid var(--primary-color);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        
        .card h3 {
            color: var(--primary-color);
            margin-bottom: 1.2rem;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
        }
        
        .card h3 i {
            margin-right: 10px;
            color: var(--accent-color);
        }
        
        .card p, .card ul {
            margin-bottom: 1rem;
            color: #555;
        }
        
        .card ul {
            padding-left: 1.5rem;
        }
        
        .card ul li {
            margin-bottom: 0.8rem;
            position: relative;
            padding-left: 1.5rem;
        }
        
        .card ul li:before {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: var(--accent-color);
            position: absolute;
            left: 0;
        }
        
        .highlight-box {
            background: rgba(9, 90, 131, 0.05);
            border-left: 4px solid var(--accent-color);
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-radius: 0 4px 4px 0;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .section-title h2 {
                font-size: 1.8rem;
            }
            
            .card {
                padding: 1.5rem;
            }
        }
        
        /* Utility Classes */
        .text-accent {
            color: var(--accent-color);
        }
        
        .mb-4 {
            margin-bottom: 1.5rem;
        }
    </style>
    <head>
        <?php include __DIR__ . '/headsource.php'; ?>
    </head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <head>
        <?php include './headerSource.php'; ?>
    </head>

    <!-- Hero Banner -->
    <div class="hero-banner">
        <img src="aitfImages/information-security-iso-01.jpg" alt="Information Security Management System">
        <div class="hero-content">
            <h1>Information Security Management System</h1>
            <h3 class="text-white">ISO 27001:2013 Training Program</h3>
        </div>
    </div>

    <!-- Main Content -->
    <section class="section">
        <div class="container">
            <div class="section-title">
                <h2>Course Overview</h2>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <h3><i class="fas fa-info-circle"></i> Training Details</h3>
                        <p><strong>Course:</strong> Awareness Course: ISMS 27001</p>
                        <p><strong>Duration:</strong> 1 Day</p>
                        <p style="font-size: 0.90rem; color: #6c757d; font-style: italic; margin-top: 8px;">The above duration training is customised as per organizational need. Please contact us in case of requirement.</p>
                        <p><strong>Class Timing:</strong> 9:00 AM to 6:00 PM</p>
                        <p><strong>Maximum Participants:</strong> 20 Delegates</p>
                    </div>
                    
                    <div class="card">
                        <h3><i class="fas fa-award"></i> Participants Receive</h3>
                        <ul>
                            <li>OHSAS slides including exercises</li>
                            <li>ISO 27001 standard documentation</li>
                            <li>Certificate of Course Completion</li>
                        </ul>
                    </div>
                    
                    <div class="card">
                        <h3><i class="fas fa-user-graduate"></i> Who Should Attend?</h3>
                        <ul>
                            <li>Professionals seeking ISMS 27001 understanding</li>
                            <li>Teams implementing information security systems</li>
                            <li>IT and security professionals</li>
                            <li>Management representatives</li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <h3><i class="fas fa-bullseye"></i> Learning Outcomes</h3>
                        <p class="mb-4">By the end of the course you will be able to demonstrate:</p>
                        <ul>
                            <li>Knowledge of techniques and tools in information security</li>
                            <li>Understanding of asset and owner identification</li>
                            <li>Comprehension of threats, vulnerabilities, and their impacts</li>
                            <li>Risk assessment and evaluation skills</li>
                        </ul>
                    </div>
                    
                    <div class="card">
                        <h3><i class="fas fa-book-open"></i> Course Content</h3>
                        <p>The course includes interactive sessions with:</p>
                        <ul>
                            <li>Comprehensive presentations</li>
                            <li>Practical exercises and group activities</li>
                            <li>Case studies and brainstorming sessions</li>
                            <li>Real-world practical experiences</li>
                        </ul>
                        
                        <div class="highlight-box mt-4">
                            <h4>Key Topics Covered:</h4>
                            <ul>
                                <li>Tools and techniques in security management</li>
                                <li>Risk assessment versus risk evaluation</li>
                                <li>ISMS application range</li>
                                <li>Implementation strategies</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="row">
                            <div class="col-md-4">
                                <h3><i class="fas fa-chalkboard-teacher"></i> Expert Facilitators</h3>
                            </div>
                            <div class="col-md-8">
                                <p>Our facilitators are seasoned professionals with extensive experience in Information Security Management Systems. They combine practical industry knowledge with effective training methodologies to deliver engaging and valuable learning experiences.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="row">
                            <div class="col-md-4">
                                <h3><i class="fas fa-clipboard-check"></i> Eligibility & Application</h3>
                            </div>
                            <div class="col-md-8">
                                <p><strong>No prior knowledge required!</strong> This course is designed for professionals at all experience levels.</p>
                                <div class="highlight-box">
                                    <h4>How to Apply:</h4>
                                    <p>Enrollment process:</p>
                                    <ol>
                                        <li>Download the Participant Nomination Form</li>
                                        <li>Email the completed form to: <a href="mailto:training1@aitf.co.in">training1@aitf.co.in</a> or <a href="mailto:training2@aitf.co.in">training2@aitf.co.in</a></li>
                                        <li>Complete the advance payment to secure your seat</li>
                                    </ol>
                                    <p class="mt-3"><em>Note: For cancellations, please notify us at least two business days before the course commencement. Transfer of nomination to another participant is permitted.</em></p>
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
