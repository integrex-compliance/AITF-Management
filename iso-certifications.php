<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISO Certifications - AITF</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --black: #000000;
            --white: #ffffff;
            --gray-50: #fafafa;
            --gray-100: #f5f5f5;
            --gray-200: #e5e5e5;
            --gray-300: #d4d4d4;
            --gray-400: #a3a3a3;
            --gray-500: #737373;
            --gray-600: #525252;
            --gray-700: #404040;
            --gray-800: #262626;
            --gray-900: #171717;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--white);
            color: var(--black);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Navigation Bar */
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            background-color: var(--black);
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .nav-logo {
            font-size: 24px;
            font-weight: 800;
            color: var(--white);
            text-decoration: none;
            letter-spacing: -1px;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 10px;
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            color: var(--white) !important;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            display: block;
            cursor: pointer;
            background-color: transparent;
        }

        .nav-link:hover {
            background-color: var(--white) !important;
            color: var(--black) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.3);
        }

        .nav-link.active {
            background-color: var(--white) !important;
            color: var(--black) !important;
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.3);
        }

        /* Hero Section */
        .hero {
            margin-top: 70px;
            padding: 100px 20px;
            background: linear-gradient(135deg, var(--black) 0%, var(--gray-900) 100%);
            color: var(--white);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 10px,
                rgba(255, 255, 255, 0.03) 10px,
                rgba(255, 255, 255, 0.03) 20px
            );
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 900;
            margin-bottom: 20px;
            letter-spacing: -2px;
            line-height: 1.1;
        }

        .hero p {
            font-size: 1.25rem;
            font-weight: 400;
            opacity: 0.9;
            margin-bottom: 40px;
        }

        /* Section Container */
        .section {
            padding: 80px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section:nth-child(even) {
            background-color: var(--gray-50);
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            color: var(--black);
            margin-bottom: 20px;
            letter-spacing: -1px;
        }

        .section-subtitle {
            font-size: 1.25rem;
            color: var(--gray-600);
            font-weight: 400;
        }

        /* ISO Cards */
        .iso-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .iso-card {
            background-color: var(--white);
            border: 2px solid var(--gray-200);
            border-radius: 16px;
            padding: 40px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .iso-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background-color: var(--black);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .iso-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border-color: var(--gray-400);
        }

        .iso-card:hover::before {
            transform: scaleX(1);
        }

        .iso-icon {
            width: 80px;
            height: 80px;
            background-color: var(--black);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 2rem;
        }

        .iso-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--black);
            margin-bottom: 15px;
        }

        .iso-description {
            color: var(--gray-600);
            line-height: 1.7;
            margin-bottom: 20px;
        }

        .iso-features {
            list-style: none;
            margin-top: 20px;
        }

        .iso-features li {
            padding: 8px 0;
            color: var(--gray-700);
            position: relative;
            padding-left: 25px;
        }

        .iso-features li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--black);
            font-weight: bold;
        }

        /* Content Sections */
        .content-section {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .content-section.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: start;
        }

        .content-text {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--gray-700);
        }

        .content-text h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--black);
            margin-bottom: 15px;
            margin-top: 30px;
        }

        .content-text p {
            margin-bottom: 20px;
        }

        .content-image {
            background-color: var(--gray-100);
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            min-height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .content-image i {
            font-size: 4rem;
            color: var(--black);
            margin-bottom: 20px;
        }

        /* Benefits Section */
        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .benefit-item {
            text-align: center;
            padding: 30px;
            background-color: var(--white);
            border: 1px solid var(--gray-200);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .benefit-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .benefit-icon {
            font-size: 2.5rem;
            color: var(--black);
            margin-bottom: 15px;
        }

        .benefit-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--black);
            margin-bottom: 10px;
        }

        .benefit-description {
            color: var(--gray-600);
            font-size: 0.95rem;
        }

        /* Footer */
        .footer {
            background-color: var(--black);
            color: var(--white);
            padding: 60px 20px 30px;
            text-align: center;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-logo {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 20px;
        }

        .footer-text {
            color: var(--gray-400);
            margin-bottom: 30px;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 30px;
        }

        .footer-links a {
            color: var(--gray-400);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--white);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .nav-menu {
                position: fixed;
                left: -100%;
                top: 70px;
                flex-direction: column;
                background-color: var(--black);
                width: 100%;
                text-align: center;
                transition: 0.3s;
                box-shadow: 0 10px 27px rgba(0, 0, 0, 0.05);
                padding: 20px 0;
            }

            .nav-menu.active {
                left: 0;
            }

            .nav-item {
                margin: 10px 0;
            }

            .hamburger {
                display: block;
                cursor: pointer;
            }

            .hamburger span {
                display: block;
                width: 25px;
                height: 3px;
                margin: 5px auto;
                background-color: var(--white);
                transition: 0.3s;
            }

            .content-grid {
                grid-template-columns: 1fr;
            }

            .hero {
                padding: 60px 20px;
            }
        }

        @media (min-width: 769px) {
            .hamburger {
                display: none;
            }
        }

        /* Hamburger Animation */
        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.active span:nth-child(1) {
            transform: translateY(8px) rotate(45deg);
        }

        .hamburger.active span:nth-child(3) {
            transform: translateY(-8px) rotate(-45deg);
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="#" class="nav-logo">AITF</a>
            <ul class="nav-menu" id="navMenu">
                <li class="nav-item">
                    <a href="#home" class="nav-link active" data-section="home">Home</a>
                </li>
                <li class="nav-item">
                    <a href="#iso-9001" class="nav-link" data-section="iso-9001" 
                       style="color: white !important; text-decoration: none; padding: 10px 20px; border-radius: 8px; transition: all 0.3s ease; display: block;"
                       onmouseover="this.style.backgroundColor='white'; this.style.color='black'; this.style.transform='translateY(-2px)';"
                       onmouseout="this.style.backgroundColor='transparent'; this.style.color='white'; this.style.transform='translateY(0)';">ISO 9001</a>
                </li>
                <li class="nav-item">
                    <a href="#iso-14001" class="nav-link" data-section="iso-14001"
                       style="color: white !important; text-decoration: none; padding: 10px 20px; border-radius: 8px; transition: all 0.3s ease; display: block;"
                       onmouseover="this.style.backgroundColor='white'; this.style.color='black'; this.style.transform='translateY(-2px)';"
                       onmouseout="this.style.backgroundColor='transparent'; this.style.color='white'; this.style.transform='translateY(0)';">ISO 14001</a>
                </li>
                <li class="nav-item">
                    <a href="#iso-45001" class="nav-link" data-section="iso-45001"
                       style="color: white !important; text-decoration: none; padding: 10px 20px; border-radius: 8px; transition: all 0.3s ease; display: block;"
                       onmouseover="this.style.backgroundColor='white'; this.style.color='black'; this.style.transform='translateY(-2px)';"
                       onmouseout="this.style.backgroundColor='transparent'; this.style.color='white'; this.style.transform='translateY(0)';">ISO 45001</a>
                </li>
                <li class="nav-item">
                    <a href="#jis-iso" class="nav-link" data-section="jis-iso"
                       style="color: white !important; text-decoration: none; padding: 10px 20px; border-radius: 8px; transition: all 0.3s ease; display: block;"
                       onmouseover="this.style.backgroundColor='white'; this.style.color='black'; this.style.transform='translateY(-2px)';"
                       onmouseout="this.style.backgroundColor='transparent'; this.style.color='white'; this.style.transform='translateY(0)';">JIS ISO</a>
                </li>
            </ul>
            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-content">
            <h1>ISO Certifications</h1>
            <p>Excellence in Quality, Environment, Safety, and Standards</p>
        </div>
    </section>

    <!-- ISO 9001 Section -->
    <section class="content-section" id="iso-9001">
        <div class="section">
            <div class="section-header">
                <h2 class="section-title">ISO 9001:2015</h2>
                <p class="section-subtitle">Quality Management Systems</p>
            </div>
            
            <div class="content-grid">
                <div class="content-text">
                    <h3>What is ISO 9001?</h3>
                    <p>ISO 9001:2015 is the international standard that specifies requirements for a quality management system (QMS). Organizations use this standard to demonstrate the ability to consistently provide products and services that meet customer and regulatory requirements.</p>
                    
                    <h3>Key Requirements</h3>
                    <ul class="iso-features">
                        <li>Quality management system and its processes</li>
                        <li>Leadership commitment and responsibilities</li>
                        <li>Planning for the quality management system</li>
                        <li>Support including resources, competence, and awareness</li>
                        <li>Operation including planning and control processes</li>
                        <li>Performance evaluation and monitoring</li>
                        <li>Continual improvement</li>
                    </ul>
                    
                    <h3>Benefits of ISO 9001 Certification</h3>
                    <p>ISO 9001 certification helps organizations demonstrate to customers and stakeholders that your business is committed to quality and continuous improvement. It enhances customer satisfaction, improves process efficiency, and provides a competitive advantage in the marketplace.</p>
                </div>
                
                <div class="content-image">
                    <i class="fas fa-certificate"></i>
                    <h3>ISO 9001:2015</h3>
                    <p>Quality Management System</p>
                </div>
            </div>
            
            <div class="benefits-grid">
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h4 class="benefit-title">Enhanced Credibility</h4>
                    <p class="benefit-description">Internationally recognized certification</p>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4 class="benefit-title">Customer Satisfaction</h4>
                    <p class="benefit-description">Improved customer trust and loyalty</p>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4 class="benefit-title">Process Efficiency</h4>
                    <p class="benefit-description">Streamlined operations and reduced waste</p>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h4 class="benefit-title">Market Access</h4>
                    <p class="benefit-description">Entry into new markets and opportunities</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ISO 14001 Section -->
    <section class="content-section" id="iso-14001">
        <div class="section">
            <div class="section-header">
                <h2 class="section-title">ISO 14001:2015</h2>
                <p class="section-subtitle">Environmental Management Systems</p>
            </div>
            
            <div class="content-grid">
                <div class="content-text">
                    <h3>What is ISO 14001?</h3>
                    <p>ISO 14001:2015 specifies the requirements for an environmental management system that an organization can use to enhance its environmental performance. ISO 14001:2015 is intended for use by an organization seeking to manage its environmental responsibilities in a systematic manner.</p>
                    
                    <h3>Key Requirements</h3>
                    <ul class="iso-features">
                        <li>Environmental aspects and impacts identification</li>
                        <li>Legal and regulatory requirements compliance</li>
                        <li>Environmental objectives and targets</li>
                        <li>Operational control procedures</li>
                        <li>Emergency preparedness and response</li>
                        <li>Monitoring and measurement of environmental performance</li>
                        <li>Management review and continual improvement</li>
                    </ul>
                    
                    <h3>Benefits of ISO 14001 Certification</h3>
                    <p>ISO 14001 certification demonstrates your commitment to environmental responsibility. It helps organizations reduce waste, prevent pollution, and comply with environmental regulations while improving efficiency and reducing costs.</p>
                </div>
                
                <div class="content-image">
                    <i class="fas fa-leaf"></i>
                    <h3>ISO 14001:2015</h3>
                    <p>Environmental Management System</p>
                </div>
            </div>
            
            <div class="benefits-grid">
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-tree"></i>
                    </div>
                    <h4 class="benefit-title">Environmental Protection</h4>
                    <p class="benefit-description">Reduced environmental impact</p>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <h4 class="benefit-title">Legal Compliance</h4>
                    <p class="benefit-description">Meeting regulatory requirements</p>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <h4 class="benefit-title">Cost Reduction</h4>
                    <p class="benefit-description">Efficient resource utilization</p>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h4 class="benefit-title">Stakeholder Trust</h4>
                    <p class="benefit-description">Enhanced corporate reputation</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ISO 45001 Section -->
    <section class="content-section" id="iso-45001">
        <div class="section">
            <div class="section-header">
                <h2 class="section-title">ISO 45001:2018</h2>
                <p class="section-subtitle">Occupational Health and Safety Management Systems</p>
            </div>
            
            <div class="content-grid">
                <div class="content-text">
                    <h3>What is ISO 45001?</h3>
                    <p>ISO 45001:2018 specifies requirements for an occupational health and safety (OH&S) management system, with guidance for its use, to enable an organization to provide safe and healthy workplaces by preventing work-related injury and ill health.</p>
                    
                    <h3>Key Requirements</h3>
                    <ul class="iso-features">
                        <li>Hazard identification and risk assessment</li>
                        <li>Legal and other requirements compliance</li>
                        <li>OH&S objectives and planning</li>
                        <li>Competence, awareness, and training</li>
                        <li>Operational controls and emergency preparedness</li>
                        <li>Performance monitoring and measurement</li>
                        <li>Internal audit and management review</li>
                    </ul>
                    
                    <h3>Benefits of ISO 45001 Certification</h3>
                    <p>ISO 45001 certification helps organizations create safer working conditions, reduce workplace accidents and illnesses, and demonstrate commitment to employee well-being. It integrates with other ISO standards for comprehensive management.</p>
                </div>
                
                <div class="content-image">
                    <i class="fas fa-hard-hat"></i>
                    <h3>ISO 45001:2018</h3>
                    <p>Occupational Health & Safety Management</p>
                </div>
            </div>
            
            <div class="benefits-grid">
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4 class="benefit-title">Worker Safety</h4>
                    <p class="benefit-description">Reduced workplace incidents</p>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <h4 class="benefit-title">Health Protection</h4>
                    <p class="benefit-description">Improved employee well-being</p>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-gavel"></i>
                    </div>
                    <h4 class="benefit-title">Regulatory Compliance</h4>
                    <p class="benefit-description">Meeting safety regulations</p>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-smile"></i>
                    </div>
                    <h4 class="benefit-title">Employee Morale</h4>
                    <p class="benefit-description">Enhanced workplace satisfaction</p>
                </div>
            </div>
        </div>
    </section>

    <!-- JIS ISO Section -->
    <section class="content-section" id="jis-iso">
        <div class="section">
            <div class="section-header">
                <h2 class="section-title">JIS ISO Standards</h2>
                <p class="section-subtitle">Japanese Industrial Standards Integration</p>
            </div>
            
            <div class="content-grid">
                <div class="content-text">
                    <h3>What are JIS ISO Standards?</h3>
                    <p>Japanese Industrial Standards (JIS) are the national standards of Japan that align with international ISO standards. JIS ISO standards represent the harmonization of Japanese industrial requirements with global best practices, ensuring products and services meet both domestic and international quality expectations.</p>
                    
                    <h3>Key JIS ISO Standards</h3>
                    <ul class="iso-features">
                        <li>JIS Q 9001 (equivalent to ISO 9001)</li>
                        <li>JIS Q 14001 (equivalent to ISO 14001)</li>
                        <li>JIS Q 45001 (equivalent to ISO 45001)</li>
                        <li>JIS Q 27001 (Information Security Management)</li>
                        <li>JIS Q 22000 (Food Safety Management)</li>
                        <li>JIS Q 31000 (Risk Management)</li>
                        <li>JIS Q 13485 (Medical Devices Quality Management)</li>
                    </ul>
                    
                    <h3>Benefits of JIS ISO Certification</h3>
                    <p>JIS ISO certification provides dual recognition, meeting both Japanese market requirements and international standards. It enhances access to Japanese markets while maintaining global competitiveness and quality assurance.</p>
                </div>
                
                <div class="content-image">
                    <i class="fas fa-flag"></i>
                    <h3>JIS ISO Standards</h3>
                    <p>Japanese Industrial Standards</p>
                </div>
            </div>
            
            <div class="benefits-grid">
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-torii-gate"></i>
                    </div>
                    <h4 class="benefit-title">Market Access</h4>
                    <p class="benefit-description">Entry to Japanese markets</p>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h4 class="benefit-title">Dual Recognition</h4>
                    <p class="benefit-description">JIS and ISO certification</p>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h4 class="benefit-title">Quality Excellence</h4>
                    <p class="benefit-description">Japanese quality standards</p>
                </div>
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-sync"></i>
                    </div>
                    <h4 class="benefit-title">Global Compatibility</h4>
                    <p class="benefit-description">International standard alignment</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-logo">AITF</div>
            <p class="footer-text">Your trusted partner for ISO certification and compliance solutions</p>
            <div class="footer-links">
                <a href="#home">Home</a>
                <a href="#iso-9001">ISO 9001</a>
                <a href="#iso-14001">ISO 14001</a>
                <a href="#iso-45001">ISO 45001</a>
                <a href="#jis-iso">JIS ISO</a>
            </div>
            <p style="color: var(--gray-500); font-size: 0.9rem; margin-top: 30px;">
                © 2024 AITF. All rights reserved.
            </p>
        </div>
    </footer>

    <script>
        // Navigation functionality
        const hamburger = document.getElementById('hamburger');
        const navMenu = document.getElementById('navMenu');
        const navLinks = document.querySelectorAll('.nav-link');
        const sections = document.querySelectorAll('.content-section');
        const heroSection = document.getElementById('home');

        // Mobile menu toggle
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });

        // Navigation link clicks
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Remove active class from all links and sections
                navLinks.forEach(l => l.classList.remove('active'));
                sections.forEach(s => s.classList.remove('active'));
                heroSection.style.display = 'none';
                
                // Add active class to clicked link
                link.classList.add('active');
                
                // Show corresponding section
                const targetSection = link.getAttribute('data-section');
                if (targetSection === 'home') {
                    heroSection.style.display = 'block';
                } else {
                    const section = document.getElementById(targetSection);
                    if (section) {
                        section.classList.add('active');
                        // Smooth scroll to top of section
                        window.scrollTo({
                            top: section.offsetTop - 70,
                            behavior: 'smooth'
                        });
                    }
                }
                
                // Close mobile menu
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });

        // Show home section by default
        heroSection.style.display = 'block';

        // Smooth scroll behavior for better UX
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

        // Add scroll effect to navbar
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 100) {
                navbar.style.background = 'rgba(0, 0, 0, 0.95)';
                navbar.style.backdropFilter = 'blur(10px)';
            } else {
                navbar.style.background = 'var(--black)';
                navbar.style.backdropFilter = 'none';
            }
        });
    </script>
</body>
</html>
