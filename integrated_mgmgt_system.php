<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integrated Management System | AITF</title>
    <style>
        :root {
            --primary-color: #1a5276;
            --secondary-color: #2ecc71;
            --accent-color: #f39c12;
            --dark-accent: #1a5276;
            --light-bg: #f8f9fa;
            --lighter-bg: #ffffff;
            --dark-text: #2c3e50;
            --light-text: #7f8c8d;
            --border-color: #e0e0e0;
            --box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --gradient: linear-gradient(135deg, #1a5276 0%, #3498db 100%);
        }
        
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.7;
            color: var(--dark-text);
            background-color: var(--light-bg);
            overflow-x: hidden;
        }
        
        .container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Hero Section */
        .hero-banner {
            position: relative;
            height: 500px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a5276 0%, #2c3e50 100%);
            padding: 80px 0;
            background-size: 400% 400%;
            animation: gradientBG 8s ease infinite;
        }
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .hero-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(26, 82, 118, 0.9) 0%, rgba(44, 62, 80, 0.95) 100%);
            z-index: 1;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: white;
            max-width: 800px;
            padding: 0 20px;
        }
        
        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            line-height: 1.2;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            animation: fadeInUp 1s ease;
            position: relative;
            display: inline-block;
        }
        
        .hero-content h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 80px;
            height: 4px;
            background: var(--secondary-color);
            border-radius: 2px;
        }
        
        .hero-content p {
            font-size: 1.5rem;
            font-weight: 500;
            margin: 2rem 0;
            color: #ffffff;
            max-width: 800px;
            line-height: 1.6;
            text-shadow: 0 1px 3px rgba(0,0,0,0.3);
            animation: fadeInUp 1s ease 0.2s;
            animation-fill-mode: both;
            position: relative;
            padding-left: 20px;
        }
        
        .hero-content p::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: var(--secondary-color);
            border-radius: 2px;
        }
        
        .course-tag {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            color: white;
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 20px;
            letter-spacing: 1px;
            border: 1px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(5px);
        }

/* Hero image removed */

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
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        /* Course Content */
        /* Main Content */
        .course-section {
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }
        
        .course-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 300px;
            background: linear-gradient(to bottom, rgba(26, 82, 118, 0.1), transparent);
            z-index: -1;
        }

        .section-title {
            text-align: center;
            margin: 60px 0 40px;
            position: relative;
            padding-bottom: 20px;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #2ecc71, #27ae60);
            border-radius: 2px;
        }

        .section-title h2 {
            color: var(--primary-color);
            font-weight: 700;
            display: inline-block;
            font-size: 2.5rem;
            margin-bottom: 15px;
            position: relative;
        }

        .section-title h2 span {
            color: #27ae60;
            position: relative;
            font-weight: 800;
            background: linear-gradient(90deg, #27ae60, #2ecc71);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .section-title h2 span::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 0;
            width: 100%;
            height: 8px;
            background: rgba(46, 204, 113, 0.3);
            z-index: -1;
            border-radius: 4px;
        }

        .course-details {
            background: white;
            border-radius: 12px;
            box-shadow: var(--box-shadow);
            padding: 40px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid var(--border-color);
        }
        
        .course-details:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }

        .detail-item {
            margin-bottom: 30px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 25px;
            transition: all 0.3s ease;
            position: relative;
            padding-left: 30px;
        }
        
        .detail-item::before {
            content: '→';
            position: absolute;
            left: 0;
            top: 0;
            color: var(--secondary-color);
            font-weight: bold;
            font-size: 1.5rem;
            line-height: 1;
            opacity: 0;
            transform: translateX(-10px);
            transition: all 0.3s ease;
        }
        
        .detail-item:hover::before {
            opacity: 1;
            transform: translateX(0);
        }

        .detail-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }

        .detail-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 1.4rem;
            position: relative;
            display: inline-block;
            padding-left: 15px;
        }
        
        .detail-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 5px;
            height: 70%;
            background: var(--secondary-color);
            border-radius: 3px;
        }

        .detail-content p {
            margin-bottom: 12px;
            position: relative;
            padding-left: 30px;
            color: var(--dark-text);
            font-size: 1.05rem;
            line-height: 1.7;
        }

        .detail-content p:before {
            content: '•';
            color: var(--secondary-color);
            position: absolute;
            left: 0;
            font-weight: bold;
            font-size: 1.5rem;
            line-height: 1;
            top: -2px;
        }

        .highlight-box {
            display: inline-block;
            background: linear-gradient(90deg, #f8f9fa, #e9ecef);
            padding: 12px 30px;
            border-radius: 50px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin: 15px 0 25px;
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .highlight-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .highlight-box h4 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
            letter-spacing: 1px;
            background: linear-gradient(90deg, #2c3e50, #34495e);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn-enroll {
            background: var(--gradient);
            color: white;
            padding: 15px 35px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
            text-decoration: none;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(26, 82, 118, 0.2);
        }

        .btn-enroll:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(26, 82, 118, 0.3);
            color: white;
        }
        
        .btn-enroll::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 100%);
            transform: translateX(-100%);
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .btn-enroll:hover::before {
            transform: translateX(100%);
        }

        /* Animations */
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
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .hero-content h1 {
                font-size: 2.8rem;
            }
        }
        
        @media (max-width: 768px) {
            .hero-banner {
                height: 400px;
            }
            
            .hero-content h1 {
                font-size: 2.2rem;
                margin-bottom: 1rem;
            }
            
            .hero-content p {
                font-size: 1.1rem;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
            
            .course-details {
                padding: 30px 20px;
                margin-bottom: 30px;
            }
            
            .detail-item {
                padding-left: 20px;
                margin-bottom: 25px;
            }
            
            .detail-title {
                font-size: 1.3rem;
            }
            
            .btn-enroll {
                width: 100%;
                padding: 14px 20px;
            }
        }
        
        @media (max-width: 576px) {
            .hero-content h1 {
                font-size: 1.8rem;
            }
            
            .section-title h2 {
                font-size: 1.7rem;
            }
            
            .detail-content p {
                font-size: 1rem;
                padding-left: 25px;
            }
            
            .highlight-box {
                padding: 20px 15px;
            }
        }
    </style>
        <?php include __DIR__ . '/headsource.php'; ?>
    </head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <head>
        <?php include './headerSource.php'; ?>
    </head>

    <!-- Hero Banner -->
    <div class="hero-banner">
        <div class="container">
            <div class="hero-content text-center">
                <span class="course-tag">INTERNAL AUDITOR COURSE</span>
                <h1>INTEGRATED MANAGEMENT SYSTEM</h1>
                <p>Become a certified internal auditor for Integrated Management Systems with our expert-led training program</p>
                <a href="#enroll" class="btn-enroll">Enroll Now</a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="course-section">
        <div class="container">
            <div class="section-title">
                <h2>Integrated Management <span>System</span></h2>
                <div class="highlight-box">
                    <h4>INTERNAL AUDITOR COURSE: IMS</h4>
                </div>
            </div>

            <div class="course-details">
                <div class="detail-item">
                    <div class="detail-title">Course Materials</div>
                    <div class="detail-content">
                        <p>QMS Slides including Exercises</p>
                        <p>Case Studies</p>
                        <p>Workbook</p>
                        <p>Sample Integrated Management System Manual</p>
                        <p>Certificate of Internal Quality Auditor for IMS (issued after successful completion of the test)</p>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-title">Course Fee & Duration</div>
                    <div class="detail-content">
                        <p><strong>Fee:</strong> INR 5,000</p>
                        <p><strong>Duration:</strong> 3 Days</p>
                        <p><strong>Schedule:</strong> 9:00 AM to 6:00 PM</p>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-title">Learning Outcomes</div>
                    <div class="detail-content">
                        <p>Develop an internal audit system</p>
                        <p>Perform internal audits to the requirements of IMS</p>
                        <p>Understand how to get value for money by conducting internal audits</p>
                        <p>Perform internal audits to ensure continuous improvement</p>
                        <p>Understand auditor selection and characteristics</p>
                        <p>Master IMS fundamentals & audit reporting</p>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-title">Course Facilitators</div>
                    <div class="detail-content">
                        <p>All course facilitators are highly qualified and experienced in both training and assessment of quality management systems. We strongly believe that the combination of experience in Quality Management Assessment and Training skills is essential for delivering a practical training course. Our facilitators are selected for their ability to make courses both practical and enjoyable.</p>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-title">Who Should Attend?</div>
                    <div class="detail-content">
                        <p>This three-day course has been developed to provide delegates with the knowledge to begin performing internal quality audits.</p>
                        <p>Ideal for Quality Managers, Quality Engineers, Departmental Managers, and any other professional, technical or administrative personnel who have responsibility for managing an internal audit system or who are required to carry out internal quality audits in accordance with quality management system requirements.</p>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-title">Course Content And Structure</div>
                    <div class="detail-content">
                        <p>Course runs from 9:00 A.M. to 6:00 P.M. daily, featuring:</p>
                        <p>• Interactive presentations and real-world examples</p>
                        <p>• Group exercises and brainstorming sessions</p>
                        <p>• Case studies and practical experiences</p>
                        <p>• Maximum class size: 10 delegates for personalized attention</p>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-title">Eligibility</div>
                    <div class="detail-content">
                        <p>Delegates should have a basic understanding of Quality Assurance principles and familiarity with the ISO 9000 series of standards. Prior experience in quality management is beneficial but not mandatory.</p>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-title">How To Apply</div>
                    <div class="detail-content">
                        <p>To secure your spot in our next session:</p>
                        <p>1. Download and complete the Participant Nomination Form</p>
                        <p>2. Email the form to: <a href="mailto:training1@bsc-icc.com">training1@bsc-icc.com</a> or <a href="mailto:training2@bsc-icc.com">training2@bsc-icc.com</a></p>
                        <p>3. Complete the advance payment to confirm your registration</p>
                        <p><em>*Cancellation policy: Please notify us at least 2 days before the course start date for any cancellations. Nomination transfers are permitted.</em></p>
                        
                        <a href="#" class="btn-enroll">Enroll Now</a>
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
