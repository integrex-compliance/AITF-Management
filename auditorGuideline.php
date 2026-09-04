<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/headsource.php'; ?>
    <style>
        :root {
            --primary-color: #0056b3;
            --secondary-color: #ff6b35;
            --accent-color: #00a8e8;
            --light-bg: #f8f9fa;
            --dark-text: #2c3e50;
            --light-text: #6c757d;
            --success-color: #28a745;
            --white: #ffffff;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark-text);
            line-height: 1.6;
            background-color: var(--light-bg);
        }
        
        .document-container {
            background: var(--white);
            border-radius: 12px;
            padding: 50px;
            box-shadow: var(--box-shadow);
            margin: 40px 0;
            border: none;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .document-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .document-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }
        
        .document-actions {
            display: flex;
            justify-content: center;
            gap: 25px;
            margin: 45px 0 35px;
            flex-wrap: wrap;
        }
        
        .btn-document {
            padding: 14px 40px;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 220px;
            position: relative;
            overflow: hidden;
            z-index: 1;
            font-size: 14px;
            border: none;
        }
        
        .btn-document::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            z-index: -1;
            transition: transform 0.5s;
            transform: scaleX(0);
            transform-origin: right;
        }
        
        .btn-document:hover::before {
            transform: scaleX(1);
            transform-origin: left;
        }
        
        .btn-document i {
            margin-right: 12px;
            font-size: 20px;
            transition: transform 0.3s ease;
        }
        
        .btn-document:hover i {
            transform: translateX(5px);
        }
        
        .btn-view {
            background: var(--primary-color);
            color: var(--white);
        }
        
        .btn-download {
            background: var(--secondary-color);
            color: var(--white);
        }
        
        .btn-view:hover {
            background: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(0, 86, 179, 0.4);
        }
        
        .btn-download:hover {
            background: var(--secondary-color);
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(255, 107, 53, 0.4);
        }
        
        .document-info {
            text-align: center;
            margin: 30px 0 50px;
            position: relative;
            padding-bottom: 25px;
        }
        
        .document-info::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 3px;
        }
        
        .document-info h3 {
            color: var(--primary-color);
            font-size: 32px;
            margin-bottom: 20px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            display: inline-block;
        }
        
        .document-info p {
            color: var(--light-text);
            font-size: 17px;
            line-height: 1.8;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .document-description {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 30px;
            border-radius: 10px;
            margin-top: 40px;
            border-left: 5px solid var(--accent-color);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }
        
        .document-description::before {
            content: '\f15c';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 60px;
            opacity: 0.1;
            color: var(--accent-color);
            z-index: 0;
        }
        
        .document-description p {
            color: var(--dark-text);
            font-size: 16px;
            line-height: 1.8;
            margin: 0;
            position: relative;
            z-index: 1;
        }
        
        .document-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 40px 0 30px;
        }
        
        .feature-item {
            background: var(--white);
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .feature-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background: rgba(0, 168, 232, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 24px;
            color: var(--accent-color);
        }
        
        .feature-item h4 {
            color: var(--primary-color);
            margin-bottom: 12px;
            font-size: 18px;
        }
        
        .feature-item p {
            color: var(--light-text);
            font-size: 14px;
            margin: 0;
        }
        
        .section {
            padding: 80px 0;
        }
        
        /* Guidelines Content Styling */
        .guidelines-content {
            background: #f9fafc;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-top: 30px;
            border: 1px solid #e1e5eb;
        }
        
        .guidelines-header {
            margin-bottom: 40px;
            padding: 25px;
            background: linear-gradient(135deg, #f7fafc 0%, #ebf8ff 100%);
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }
        
        .guidelines-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #3182ce, #63b3ed);
        }
        
        .guidelines-header h2 {
            color: #1a365d;
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .guidelines-header h4 {
            color: #2d3748;
            font-size: 18px;
            font-weight: 500;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .document-meta {
            color: #718096;
            font-size: 14px;
            margin-top: 15px;
            font-style: italic;
            letter-spacing: 0.3px;
            background: rgba(113, 128, 150, 0.1);
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
        }
        
        .guideline-section {
            margin-bottom: 30px;
            padding: 20px;
            border-radius: 8px;
            background: #ffffff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
            border-left: 4px solid #e2e8f0;
        }
        
        .guideline-section:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
            border-left-color: #4299e1;
        }
        
        .guideline-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .guideline-section h3 {
            color: #2c5282;
            font-size: 20px;
            margin-bottom: 15px;
            font-weight: 600;
            padding-bottom: 8px;
            border-bottom: 2px solid #e2e8f0;
            display: inline-block;
            padding-right: 20px;
        }
        
        .guideline-section p, 
        .guideline-section li {
            color: #4a5568;
            line-height: 1.8;
            margin-bottom: 12px;
            font-size: 15.5px;
        }
        
        .guideline-section p strong {
            color: #2c5282;
            font-weight: 600;
        }
        
        .guideline-section ol, 
        .guideline-section ul {
            padding-left: 30px;
            margin: 15px 0;
        }
        
        .guideline-section ol {
            list-style-type: none;
            counter-reset: item;
        }
        
        .guideline-section ol > li {
            counter-increment: item;
            position: relative;
            padding-left: 35px;
        }
        
        .guideline-section ol > li:before {
            content: counter(item) '.';
            position: absolute;
            left: 0;
            color: #3182ce;
            font-weight: 600;
        }
        
        .guideline-section ul > li {
            position: relative;
            padding-left: 25px;
        }
        
        .guideline-section ul > li:before {
            content: '•';
            color: #3182ce;
            font-weight: bold;
            position: absolute;
            left: 0;
        }
        
        .guideline-section li {
            margin-bottom: 8px;
        }
        
        .document-footer {
            margin-top: 50px;
            padding: 25px;
            color: #4a5568;
            font-size: 14px;
            background: #f7fafc;
            border-radius: 8px;
            border-top: 1px dashed #cbd5e0;
        }
        
        .document-footer p:first-child {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
        }
        
        @media (max-width: 768px) {
            .guidelines-content {
                padding: 20px 15px;
            }
            
            .guidelines-header h2 {
                font-size: 20px;
            }
            
            .guidelines-header h4 {
                font-size: 16px;
            }
            
            .guideline-section h3 {
                font-size: 18px;
            }
        }
        
        .heading_main {
            position: relative;
            margin-bottom: 50px;
        }
        
        .heading_main h2 {
            font-size: 42px;
            color: var(--primary-color);
            margin-bottom: 15px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            display: inline-block;
        }
        
        .heading_main h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--secondary-color);
            border-radius: 2px;
        }
        
        .heading_main h2 span {
            color: var(--secondary-color);
            position: relative;
        }
        
        .heading_main p {
            color: var(--light-text);
            font-size: 18px;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.7;
        }
        
        @media (max-width: 991px) {
            .document-container {
                padding: 30px 20px;
            }
            
            .document-actions {
                flex-direction: column;
                gap: 15px;
            }
            
            .btn-document {
                width: 100%;
                padding: 12px 20px;
            }
            
            .document-features {
                grid-template-columns: 1fr;
            }
            
            .heading_main h2 {
                font-size: 32px;
            }
        }
        
        @media (max-width: 767px) {
            .section {
                padding: 60px 0;
            }
            
            .document-info h3 {
                font-size: 26px;
            }
            
            .document-description::before {
                display: none;
            }
        }
    </style>
</head>

<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>

    <!-- Hero Section -->
    <div class="section" style="background: linear-gradient(135deg, var(--primary-color) 0%, #003366 100%); padding: 180px 0 80px; color: white;">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto text-center">
                    <h1 class="mb-4" style="font-size: 42px; font-weight: 700; text-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                        <span style="color: #ffcc00;">AUDITOR GUIDELINES</span> 
                    </h1>
                    <p class="lead mb-0" style="font-size: 20px; opacity: 1; max-width: 800px; margin: 0 auto; color: #ffffff; text-shadow: 0 1px 3px rgba(0,0,0,0.3);">
                        Essential resources and documentation for AITF auditors to ensure compliance and excellence in auditing practices.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Section -->
    <div class="section" style="padding-top: 60px;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="document-container">
                        <div class="document-info">
                            <h3>Auditor Guidelines Document</h3>
                            <p>Contact AITF OFFICE for auditor guideline document.</p>
                        </div>
                        

                        
                        <div class="document-features">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                                <h4>Audit Procedures</h4>
                                <p>Step-by-step guidelines for conducting thorough and effective audits</p>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <h4>Documentation</h4>
                                <p>Required forms, checklists, and reporting templates</p>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-balance-scale"></i>
                                </div>
                                <h4>Compliance</h4>
                                <p>Latest regulatory requirements and industry standards</p>
                            </div>
                        </div>
                        
                      
                        

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include './footerSource.php'; ?>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- Smooth scrolling -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
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
        const animateOnScroll = function() {
            const elements = document.querySelectorAll('.feature-item, .document-container');
            elements.forEach(element => {
                const elementPosition = element.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.3;
                
                if (elementPosition < screenPosition) {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }
            });
        };
        
        // Set initial state for animation
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.feature-item, .document-container');
            elements.forEach(element => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(30px)';
                element.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
            });
            
            // Trigger animation on page load
            setTimeout(animateOnScroll, 300);
        });
        
        window.addEventListener('scroll', animateOnScroll);
    });
    </script>
</body>
</html>
