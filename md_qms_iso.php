<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './headSource.php'; ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Global Styles */
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #4b6cb7;
            --accent-color: #ff6b6b;
            --light-bg: #f8f9fa;
            --dark-text: #2c3e50;
            --light-text: #6c757d;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.8;
            color: var(--dark-text);
            overflow-x: hidden;
        }
        /* Banner Styling */
        .hero-banner {
            position: relative;
            width: 100%;
            height: 80vh;
            min-height: 600px;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(0,0,0,0.7), rgba(0,0,0,0.4));
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }
        
        .hero-banner:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.3) 100%);
            z-index: 1;
        }
        
        .hero-banner img {
            position: absolute;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
            animation: zoomInOut 20s infinite alternate;
        }
        
        .banner-content {
            position: relative;
            z-index: 2;
            max-width: 1200px;
            padding: 0 30px;
            animation: fadeInUp 1s ease-out;
        }
        
        .banner-content h1 {
            font-size: 4.5rem;
            font-weight: 800;
            margin-bottom: 25px;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.3);
            background: linear-gradient(90deg, #fff, #e0e0e0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-transform: uppercase;
            letter-spacing: 2px;
            line-height: 1.2;
        }
        
        .banner-content p.lead {
            font-size: 1.5rem;
            max-width: 800px;
            margin: 0 auto 40px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
            opacity: 0.9;
        }
        
        @keyframes zoomInOut {
            0% { transform: scale(1); }
            100% { transform: scale(1.1); }
        }
        
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
        
        /* Main Content Styling */
        .iso-section {
            padding: 100px 0;
            position: relative;
            overflow: hidden;
            background-color: var(--light-bg);
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 70px;
            position: relative;
        }
        
        .section-title h2 {
            font-size: 2.8rem;
            color: var(--primary-color);
            position: relative;
            display: inline-block;
            padding-bottom: 20px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }
        
        .section-title h2:after {
            content: '';
            position: absolute;
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary-color), #8e2de2);
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }
        
        .section-title p {
            color: var(--light-text);
            font-size: 1.2rem;
            max-width: 800px;
            margin: 20px auto 0;
            line-height: 1.8;
        }
        
        .iso-content {
            background: white;
            border-radius: 15px;
            padding: 50px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.08);
            margin-bottom: 50px;
            position: relative;
            transition: all 0.4s ease;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .iso-content:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.12);
        }
        
        .iso-content h3 {
            color: var(--primary-color);
            margin-bottom: 25px;
            font-weight: 700;
            font-size: 1.8rem;
            position: relative;
            padding-bottom: 15px;
        }
        
        .iso-content h3:after {
            content: '';
            position: absolute;
            width: 50px;
            height: 3px;
            background: var(--secondary-color);
            bottom: 0;
            left: 0;
            border-radius: 2px;
        }
        
        .iso-content p {
            color: var(--light-text);
            line-height: 1.9;
            margin-bottom: 25px;
            font-size: 1.05rem;
        }
        
        /* Features List */
        .features-list {
            list-style: none;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .features-list li {
            padding: 20px;
            background: #f8f9ff;
            border-radius: 10px;
            position: relative;
            padding-left: 60px;
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }
        
        .features-list li:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border-color: var(--secondary-color);
        }
        
        .features-list li:before {
            content: '✓';
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            width: 30px;
            height: 30px;
            background: var(--secondary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
        }
        
        .features-list li strong {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        /* Buttons */
        .cta-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--secondary-color), #8e2de2);
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 1.5px;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            border: none;
            text-decoration: none;
            font-size: 0.95rem;
            box-shadow: 0 10px 30px rgba(75, 108, 183, 0.3);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .cta-button:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #8e2de2, var(--secondary-color));
            opacity: 0;
            transition: all 0.4s ease;
            z-index: -1;
        }
        
        .cta-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(75, 108, 183, 0.4);
            color: white;
        }
        
        .cta-button:hover:before {
            opacity: 1;
        }
        
        .cta-button i {
            margin-left: 10px;
            transition: transform 0.3s ease;
        }
        
        .cta-button:hover i {
            transform: translateX(5px);
        }
        
        /* Secondary Button */
        .btn-secondary {
            background: white;
            color: var(--secondary-color);
            border: 2px solid var(--secondary-color);
            box-shadow: none;
            margin-left: 15px;
        }
        
        .btn-secondary:hover {
            background: var(--secondary-color);
            color: white;
        }
        
        /* Button Container */
        .button-group {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        
        @media (max-width: 768px) {
            .banner-content h1 {
                font-size: 2.5rem;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
            
            .iso-content {
                padding: 25px;
            }
        }
    </style>
</head>

<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>

    <!-- Hero Banner -->
    <div class="hero-banner">
        <img src="aitfImages/information-security-iso-01.jpg" alt="MD-QMS ISO 13485:2016">
        <div class="banner-content">
            <h1>MD-QMS ISO 13485:2016</h1>
            <p class="lead">Ensuring Excellence in Medical Device Quality Management Systems</p>
            <a href="#contact" class="cta-button">Get Free Consultation <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>

    <!-- Main Content Section -->
    <section class="iso-section">
        <div class="container">
            <div class="section-title">
                <h2>Medical Devices Quality Management System</h2>
                <p>ISO 13485:2016 is the international standard that sets out the requirements for a quality management system specific to the medical devices industry.</p>
            </div>
            
            <div class="row">
                <div class="col-lg-10 offset-lg-1">
                    <div class="iso-content">
                        <h3>About ISO 13485:2016</h3>
                        <p>ISO 13485:2016 specifies requirements for a comprehensive quality management system for the design and manufacture of medical devices. This standard is designed to be used by organizations involved in the design, production, installation, and servicing of medical devices and related services. It can also be used by internal and external parties, including certification bodies, to help them with their auditing processes.</p>
                        
                        <p>The standard is based on the process approach and incorporates the Plan-Do-Check-Act (PDCA) cycle, with risk management being a key component. It emphasizes the importance of regulatory compliance and the need to maintain the effectiveness of the quality management system.</p>
                        
                        <h3>Key Benefits of Certification</h3>
                        <ul class="features-list">
                            <li><strong>Enhanced Product Quality:</strong> Systematic approach to quality management reduces errors and improves product consistency.</li>
                            <li><strong>Regulatory Compliance:</strong> Meets international regulatory requirements for medical devices.</li>
                            <li><strong>Improved Risk Management:</strong> Identifies and mitigates potential risks in medical device manufacturing.</li>
                            <li><strong>Market Access:</strong> Facilitates entry into global markets with a recognized certification.</li>
                            <li><strong>Customer Confidence:</strong> Builds trust with customers and stakeholders through demonstrated commitment to quality.</li>
                            <li><strong>Operational Efficiency:</strong> Streamlines processes and reduces waste through continuous improvement.</li>
                        </ul>
                        
                        <h3>Our Implementation Approach</h3>
                        <p>Our team of ISO 13485 experts provides end-to-end support for your certification journey. We tailor our approach to your organization's specific needs, ensuring a smooth and efficient implementation process.</p>
                        
                        <div class="process-steps">
                            <div class="step">
                                <div class="step-icon"><i class="fas fa-search"></i></div>
                                <h4>Gap Analysis</h4>
                                <p>Comprehensive assessment of your current quality management system against ISO 13485:2016 requirements.</p>
                            </div>
                            <div class="step">
                                <div class="step-icon"><i class="fas fa-file-alt"></i></div>
                                <h4>Documentation</h4>
                                <p>Development of all required documentation including quality manual, procedures, and work instructions.</p>
                            </div>
                            <div class="step">
                                <div class="step-icon"><i class="fas fa-cogs"></i></div>
                                <h4>Implementation</h4>
                                <p>Guidance through the implementation of processes and training of personnel.</p>
                            </div>
                            <div class="step">
                                <div class="step-icon"><i class="fas fa-award"></i></div>
                                <h4>Certification</h4>
                                <p>Support during the certification audit and assistance with any non-conformities.</p>
                            </div>
                        </div>
                        
                        <div class="button-group">
                            <a href="#contact" class="cta-button">Start Your Certification <i class="fas fa-arrow-right"></i></a>
                            <a href="#benefits" class="cta-button btn-secondary">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Benefits Section -->
    <section class="benefits-section">
        <div class="container">
            <div class="section-title">
                <h2>Why Choose Our ISO 13485 Services?</h2>
                <p>We provide comprehensive support to help you achieve and maintain ISO 13485:2016 certification</p>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h4>Expert Consultants</h4>
                        <p>Our team consists of experienced professionals with extensive knowledge of medical device regulations and quality management systems.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <h4>Customized Solutions</h4>
                        <p>We tailor our approach to meet the specific needs and challenges of your organization and industry sector.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="benefit-card">
                        <div class="benefit-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <h4>Ongoing Support</h4>
                        <p>We provide continuous support even after certification to ensure your QMS remains effective and compliant.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <?php include './footerSource.php'; ?>
</body>
</html>