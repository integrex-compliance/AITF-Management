<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './headSource.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;  /* Premium blue */
            --primary-light: #60a5fa;
            --primary-dark: #2563eb;
            --secondary: #1d4ed8;  /* Darker blue for contrast */
            --accent: #60a5fa;     /* Lighter blue for accents */
            --dark: #0f172a;       /* Dark blue background */
            --darker: #020617;     /* Even darker for contrast */
            --light: #f8fafc;      /* Light text */
            --glass: rgba(30, 58, 138, 0.2);
            --glass-border: rgba(99, 102, 241, 0.3);
            --text-primary: #ffffff;       /* White primary text */
            --text-secondary: #e2e8f0;  /* Light gray for secondary text */
            --text-muted: #94a3b8;     /* Muted text color */
            --card-bg: rgba(255, 255, 255, 0.95);  /* Semi-transparent white card */
            --card-text: #1e293b;     /* Dark text on cards */
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            font-family: 'Poppins', sans-serif;
            color: #ffffff;
            min-height: 100vh;
            line-height: 1.8;
            overflow-x: hidden;
            background-attachment: fixed;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            margin: 0;
            padding: 0;
            width: 100%;
        }
        
        /* Ensure all sections take full width */
        .section {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .section > .container {
            max-width: 1200px !important;
            width: 100% !important;
            padding: 0 15px !important;
            margin: 0 auto !important;
        }
        
        /* Footer styles are now handled in footerSource.php */
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Decorative Elements */
        .decorative-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.2;
            z-index: -1;
        }
        
        .shape-1 {
            width: 400px;
            height: 400px;
            background: var(--primary);
            top: -200px;
            right: -100px;
        }
        
        .shape-2 {
            width: 500px;
            height: 500px;
            background: var(--accent);
            bottom: -250px;
            left: -250px;
        }
        
        .hero-section {
            position: relative;
            padding: 8rem 0 6rem;
            overflow: hidden;
            text-align: center;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(30, 58, 138, 0.6) 0%, rgba(30, 64, 175, 0.7) 100%);
            z-index: -1;
        }

        .section-title {
            text-align: center;
            margin: 5rem 0 4rem;
            position: relative;
            z-index: 2;
        }
        
        .section-title h1 {
            font-size: 4rem;
            font-weight: 800;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 20px;
            text-shadow: 0 4px 8px rgba(0,0,0,0.4);
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }
        
        .section-title p {
            font-size: 1.25rem;
            color: #ffffff;
            max-width: 800px;
            margin: 0 auto;
            opacity: 0.95;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
            font-weight: 500;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 2rem;
            border: 1px solid #e5e7eb;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .card-header {
            background: var(--primary);
            color: white;
            padding: 1.25rem 1.5rem;
            font-size: 1.25rem;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .card-header i {
            margin-right: 0.75rem;
            font-size: 1.5rem;
        }

        .card-body {
            padding: 2rem;
        }

        .card-body p {
            margin-bottom: 1.25rem;
            color: #1e293b;
            line-height: 1.8;
            font-weight: 400;
            font-size: 1.1rem;
        }

        .card-body ul {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0;
        }

        .card-body li {
            padding: 0.75rem 0;
            padding-left: 2rem;
            position: relative;
            color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
            font-weight: 400;
            font-size: 1.05rem;
        }

        .card-body li:before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--primary);
            font-weight: bold;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }

        .feature-box {
            background: #ffffff;
            border-radius: 0.75rem;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .feature-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
            display: inline-block;
        }
        .feature-icon p, .feature-icon li {
            font-size: 1.15rem;
            line-height: 1.8;
            margin-bottom: 1.4rem;
            color: #1e293b;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        .feature-box p {
            color: #1e293b;
            font-size: 1rem;
            line-height: 1.7;
            font-weight: 500;
        }
        
        /* Glassmorphism Card */
        .card {
            background: #ffffff;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 40px;
            margin: 30px auto;
            max-width: 1200px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            z-index: 1;
            color: #1e293b;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
            transform: rotate(30deg);
            z-index: -1;
            opacity: 0;
            transition: opacity 0.6s ease;
        }
        
        .card:hover::before {
            opacity: 1;
        }
        
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }
        
        .card-body {
            font-size: 1.15rem;
            line-height: 1.8;
            color: #1e293b;
        }
        
        .card-header {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 2rem;
            color: #1e293b;
            position: relative;
            display: inline-block;
        }
        
        .card-header i {
            margin-right: 15px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: all 0.3s ease;
        }
        
        .card:hover .card-header i {
            transform: scale(1.2) rotate(10deg);
        }
        
        .card-header::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -10px;
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            border-radius: 2px;
            transition: all 0.3s ease;
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.5);
        }
        
        .card:hover .card-header::after {
            width: 120px;
        }
        
        .feature-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .card:hover .feature-icon {
            transform: scale(1.2) rotate(10deg);
        }
            transition: transform 0.3s ease;
        }
        
        .feature-box:hover .feature-icon {
            transform: scale(1.1);
        }

        @media (max-width: 768px) {
            .section-title h1 {
                font-size: 2.5rem;
            }
            
            .section-title p {
                font-size: 1.1rem;
            }
            
            .grid {
                grid-template-columns: 1fr;
            }
        }
        
        .benefit-header h4 {
            color: var(--primary-dark);
            margin: 0;
            font-size: 1.4rem;
            font-weight: 600;
        }
        
        .benefit-body {
            padding: 25px;
        }
        
        .benefit-body ul {
            padding-left: 25px;
            margin: 0;
        }
        
        .benefit-body li {
            margin-bottom: 12px;
            position: relative;
            padding-left: 30px;
            list-style: none;
        }
        
        .benefit-body li:before {
            content: '✓';
            color: var(--primary);
            font-weight: bold;
            position: absolute;
            left: 0;
        }
        
        .cta-section {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(37, 99, 235, 0.25));
            padding: 3.5rem;
            border-radius: 20px;
            text-align: center;
            margin: 5rem 0 2rem;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.2));
            z-index: -1;
            border-radius: 20px;
        }
        
        .cta-section h3 {
            font-size: 2.2rem;
            margin: 0 0 15px;
            color: white;
            position: relative;
            z-index: 1;
        }
        
        .cta-section p {
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto 25px;
            color: rgba(255, 255, 255, 0.9);
            position: relative;
            z-index: 1;
        }
        
        .btn-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white !important;
            border: 1px solid rgba(96, 165, 250, 0.3);
            padding: 16px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            box-shadow: 0 10px 30px rgba(108, 99, 255, 0.3);
            position: relative;
            overflow: hidden;
            z-index: 1;
            font-size: 1.1rem;
            border: none;
            cursor: pointer;
            margin-top: 2rem;
        }
        
        .btn-cta::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            z-index: -1;
            transition: all 0.4s ease;
            opacity: 0;
            border-radius: 50px;
            box-shadow: 0 0 25px rgba(59, 130, 246, 0.5);
        }
        
        .btn-cta:hover {
            transform: translateY(-5px) scale(1.03);
            box-shadow: 0 15px 40px rgba(108, 99, 255, 0.4);
            letter-spacing: 2px;
        }
        
        .btn-cta:hover::before {
            opacity: 1;
        }
        
        .btn-cta:active {
            transform: translateY(0) scale(0.98);
            box-shadow: 0 5px 20px rgba(108, 99, 255, 0.3);
        }
        
        .btn-cta i {
            margin-right: 10px;
            transition: transform 0.3s ease;
        }
        
        .btn-cta:hover i {
            transform: translateY(-2px) rotate(-10deg);
        }
        
        .featured-image {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .featured-image img {
            width: 100%;
            height: auto;
            display: block;
        }
        
        @media (max-width: 768px) {
            .about-iso-section {
                padding: 60px 0;
            }
            
            .section-title h2 {
                font-size: 2.2rem;
            }
            
            .benefits-grid {
                grid-template-columns: 1fr;
            }
            
            .cta-section {
                padding: 40px 20px;
            }
            
            .cta-section h3 {
                font-size: 1.6rem;
            }
        }
        /* Custom Footer Styling for About ISO Page */
        .footer-box {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
            border-top: 1px solid rgba(59, 130, 246, 0.3) !important;
            margin-top: 4rem !important;
            padding: 4rem 0 0 !important;
        }
        
        .footer_blog {
            background: rgba(30, 41, 59, 0.5) !important;
            backdrop-filter: blur(10px) !important;
            border: 1px solid rgba(59, 130, 246, 0.1) !important;
            transition: all 0.3s ease !important;
        }
        
        .footer_blog:hover {
            transform: translateY(-5px) !important;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2) !important;
            border-color: rgba(59, 130, 246, 0.3) !important;
        }
        
        .footer-title {
            color: #fff !important;
            font-weight: 600 !important;
            margin-bottom: 1.5rem !important;
            position: relative !important;
            padding-bottom: 10px !important;
        }
        
        .footer-title:after {
            content: '' !important;
            position: absolute !important;
            left: 0 !important;
            bottom: 0 !important;
            width: 50px !important;
            height: 2px !important;
            background: #3b82f6 !important;
        }
        
        .footer_bottom {
            background: rgba(15, 23, 42, 0.8) !important;
            border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
            padding: 1.5rem 0 !important;
            margin-top: 3rem !important;
        }
        
        .copyright {
            color: #94a3b8 !important;
            font-size: 0.9rem !important;
        }
    </style>
</head>

<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Decorative Shapes -->
    <div class="decorative-shape shape-1"></div>
    <div class="decorative-shape shape-2"></div>
    
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="section-title animate-up" style="animation-delay: 0.1s">
                        <h1>About ISO Certification</h1>
                        <p class="lead" style="color: rgba(255,255,255,0.9); font-size: 1.3rem; max-width: 800px; margin: 0 auto;">Understanding the Value of International Standards for Your Business</p>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <h3 style="color: #1e293b; font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">Consensus-Based</h3>
                        <p>Standards developed through global consensus among industry experts</p>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <p style="color: #ffffff !important; font-weight: 500; font-size: 1.1rem; line-height: 1.8; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">
                        With members from 167 national standards bodies, ISO has published over 24,000 International Standards covering almost every industry, from technology and food safety to agriculture and healthcare. These standards help businesses increase productivity while minimizing errors and waste, enable products to enter new markets, and facilitate free and fair global trade.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <div class="card mt-5 animate-up" style="animation-delay: 0.3s">
        <div class="card-header">
            <i class="fas fa-certificate"></i>
            <span>Our Approach to ISO Certification</span>
        </div>
        <div class="card-body">
                <p>
                    We provide comprehensive ISO certification services designed to help organizations implement, maintain, and continuously improve their management systems in line with international standards. Our systematic approach ensures that your organization not only achieves certification but also realizes tangible business benefits through enhanced operational efficiency, improved risk management, and increased stakeholder confidence.
                </p>
                
                <h3 class="section-subtitle" style="color: #1e293b; font-size: 1.8rem; font-weight: 600; margin-bottom: 1.5rem;">ISO 9001:2015 - Quality Management</h3>
                <p>
                    ISO 9001 is the international standard that specifies requirements for a quality management system (QMS). Organizations use the standard to demonstrate the ability to consistently provide products and services that meet customer and regulatory requirements. 
                </p>
                
                <p class="lead">
                    At AITF, we follow a comprehensive approach to help organizations achieve ISO certification. Our process is designed to be efficient, effective, and tailored to your specific needs and industry requirements.
                </p>
                
                <div class="grid">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3 style="color: #1e293b; font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">Gap Analysis</h3>
                        <p>We begin with a thorough assessment of your current processes and systems to identify gaps against ISO requirements.</p>
                    </div>
                    
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3 style="color: #1e293b; font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">Documentation</h3>
                        <p>Our experts help you develop and implement the necessary documentation and procedures to meet ISO standards.</p>
                    </div>
                    
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h3 style="color: #1e293b; font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">Implementation</h3>
                        <p>We guide you through the implementation process, ensuring all requirements are properly addressed and integrated into your operations.</p>
                    </div>
                    
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h3 style="color: #1e293b; font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">Training</h3>
                        <p>Comprehensive training programs to ensure your team understands and can maintain the ISO management system.</p>
                    </div>
                    
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <h3 style="color: #1e293b; font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">Internal Audit</h3>
                        <p>We conduct thorough internal audits to verify the effectiveness of your management system before the certification audit.</p>
                    </div>
                    
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <h3 style="color: #1e293b; font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">Certification</h3>
                        <p>We assist you through the certification process with an accredited certification body to achieve your ISO certification.</p>
                    </div>
                </div>
                
                <h3 class="section-subtitle" style="color: #1e293b; font-size: 1.8rem; font-weight: 600; margin-bottom: 1.5rem;">Benefits of ISO Certification</h3>
                <div class="benefit-body">
                    <ul>
                        <li style="color: #1e293b !important; font-weight: 500; font-size: 1.05rem; line-height: 1.6;">Provides the means for documenting the company's experience in a structured manner to establish a systematic working environment</li>
                        <li style="color: #1e293b !important; font-weight: 500; font-size: 1.05rem; line-height: 1.6;">Helps to maintain consistency in quality & product services</li>
                        <li style="color: #1e293b !important; font-weight: 500; font-size: 1.05rem; line-height: 1.6;">Brings clarity & transparency to duties & responsibilities</li>
                        <li style="color: #1e293b !important; font-weight: 500; font-size: 1.05rem; line-height: 1.6;">Improves traceability in the organization</li>
                        <li style="color: #1e293b !important; font-weight: 500; font-size: 1.05rem; line-height: 1.6;">Reduces failure & process costs</li>
                        <li style="color: #1e293b !important; font-weight: 500; font-size: 1.05rem; line-height: 1.6;">Improves relationships between employees</li>
                    </ul>
                </div>
                
                <div class="cta-section animate-up" style="animation-delay: 0.4s">
                    <h3 style="font-size: 2.2rem; margin-bottom: 1.5rem; color: #fff;">Ready to Achieve ISO Certification?</h3>
                    <p style="font-size: 1.2rem; max-width: 700px; margin: 0 auto 2rem; color: rgba(255,255,255,0.9);">Take the first step towards operational excellence and global recognition. Our experts are here to guide you through every stage of the certification process.</p>
                    <a href="contactUS.php" class="btn-cta">
                        <i class="fas fa-rocket"></i> Get Started Today
                    </a>
                </div>
            </div>
        </div>
    
    <!-- Footer will be included by footerSource.php with full width -->
    <?php include './footerSource.php'; ?>
    
    <!-- Scroll to Top Button -->
    <a href="#" id="scroll-to-top" style="display: none;">
        <i class="fas fa-arrow-up"></i>
    </a>
    
    <script>
        // Scroll to top button
        window.onscroll = function() {
            scrollFunction();
        };
        
        function scrollFunction() {
            var scrollToTopBtn = document.getElementById("scroll-to-top");
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                scrollToTopBtn.style.display = "flex";
            } else {
                scrollToTopBtn.style.display = "none";
            }
        }
        
        document.getElementById('scroll-to-top').addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>
</body>
</html>
