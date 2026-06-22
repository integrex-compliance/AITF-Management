<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './headSource.php'; ?>
    <title>Frequently Asked Questions - AITF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --accent-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --dark-bg: #1a1f2e;
            --card-bg: rgba(255, 255, 255, 0.12);
            --card-hover: rgba(255, 255, 255, 0.18);
            --text-primary: #ffffff;
            --text-secondary: #e2e8f0;
            --text-muted: #a0aec0;
            --accent-color: #4299e1;
            --accent-hover: #3182ce;
            --border-color: rgba(255, 255, 255, 0.15);
            --transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
            --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.15);
            --shadow-lg: 0 8px 30px rgba(0, 0, 0, 0.25);
            --shadow-xl: 0 15px 50px rgba(0, 0, 0, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--dark-bg);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(66, 153, 225, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 50%, rgba(49, 130, 206, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 50% 100%, rgba(118, 75, 162, 0.08) 0%, transparent 50%);
            pointer-events: none;
            z-index: 1;
        }

        .faq-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 100px 20px 80px;
            position: relative;
            z-index: 2;
        }

        .page-header {
            text-align: center;
            margin-bottom: 60px;
            animation: fadeInDown 0.8s ease;
        }

        .page-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            background: var(--accent-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -1px;
        }

        .page-subtitle {
            color: var(--text-muted);
            font-size: 1.2rem;
            font-weight: 400;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Search Section */
        .search-section {
            margin-bottom: 50px;
            animation: fadeInUp 0.8s ease 0.2s both;
        }

        .search-container {
            max-width: 600px;
            margin: 0 auto;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 18px 60px 18px 25px;
            background: var(--card-bg);
            border: 2px solid var(--border-color);
            border-radius: 50px;
            color: var(--text-primary);
            font-size: 16px;
            font-weight: 500;
            transition: var(--transition);
            backdrop-filter: blur(10px);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--accent-color);
            background: var(--card-hover);
            box-shadow: 0 0 0 4px rgba(66, 153, 225, 0.15);
        }

        .search-input::placeholder {
            color: var(--text-muted);
        }

        .search-icon {
            position: absolute;
            right: 25px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent-color);
            font-size: 18px;
            pointer-events: none;
        }

        .search-results {
            margin-top: 15px;
            text-align: center;
            color: var(--text-muted);
            font-size: 14px;
            min-height: 20px;
        }

        .no-results {
            color: #f87171;
        }

        /* Category Pills */
        .category-pills {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 40px;
            animation: fadeInUp 0.8s ease 0.4s both;
        }

        .category-pill {
            padding: 10px 20px;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 25px;
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: var(--transition);
            backdrop-filter: blur(10px);
            cursor: pointer;
        }

        .category-pill:hover,
        .category-pill.active {
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        /* FAQ Sections */
        .faq-section {
            margin-bottom: 50px;
            animation: fadeInUp 0.8s ease 0.6s both;
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 30px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 15px;
            position: relative;
        }

        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-color);
            margin-left: 20px;
        }

        .section-title i {
            font-size: 1.5rem;
            color: var(--accent-color);
            background: var(--card-bg);
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border-color);
        }

        .faq-item {
            background: var(--card-bg);
            border-radius: 16px;
            margin-bottom: 20px;
            overflow: hidden;
            border: 1px solid var(--border-color);
            transition: var(--transition);
            backdrop-filter: blur(10px);
            position: relative;
        }

        .faq-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--accent-gradient);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .faq-item:hover {
            background: var(--card-hover);
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(66, 153, 225, 0.3);
        }

        .faq-item:hover::before {
            opacity: 1;
        }

        .faq-item.active {
            background: var(--card-hover);
            border-color: var(--accent-color);
        }

        .faq-item.active::before {
            opacity: 1;
        }

        .faq-question {
            padding: 25px 30px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transition);
            line-height: 1.5;
        }

        .faq-question:hover {
            color: var(--accent-color);
        }

        .faq-icon {
            transition: transform 0.4s ease;
            color: var(--accent-color);
            font-size: 1.2rem;
            flex-shrink: 0;
            margin-left: 20px;
        }

        .faq-item.active .faq-icon {
            transform: rotate(180deg);
        }

        .faq-answer {
            padding: 0 30px;
            max-height: 0;
            overflow: hidden;
            transition: all 0.4s ease;
            color: var(--text-muted);
            line-height: 1.8;
            font-size: 1rem;
        }

        .faq-item.active .faq-answer {
            padding: 0 30px 25px 30px;
            max-height: 500px;
        }

        .highlight {
            background: rgba(66, 153, 225, 0.2);
            padding: 2px 4px;
            border-radius: 4px;
            color: var(--accent-color);
        }

        /* Back to Home */
        .back-to-home {
            text-align: center;
            margin-top: 60px;
            animation: fadeInUp 0.8s ease 0.8s both;
        }

        .back-to-home a {
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 15px 30px;
            background: var(--card-bg);
            border: 2px solid var(--border-color);
            border-radius: 12px;
            transition: var(--transition);
            backdrop-filter: blur(10px);
            font-size: 16px;
        }

        .back-to-home a:hover {
            background: var(--accent-color);
            color: var(--dark-bg);
            border-color: var(--accent-color);
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        /* Responsive */
        @media (max-width: 768px) {
            .faq-container {
                padding: 80px 15px 60px;
            }
            
            .page-title {
                font-size: 2.5rem;
            }
            
            .page-subtitle {
                font-size: 1.1rem;
            }
            
            .section-title {
                font-size: 1.5rem;
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .section-title::after {
                display: none;
            }
            
            .section-title i {
                width: 45px;
                height: 45px;
            }
            
            .faq-question {
                padding: 20px 25px;
                font-size: 1rem;
            }
            
            .search-input {
                padding: 15px 50px 15px 20px;
                font-size: 15px;
            }
        }

        /* Footer specific styles for FAQ page */
        .footer-box {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
            color: #ffffff !important;
            position: relative !important;
            overflow: hidden !important;
            padding: 60px 0 0 !important;
            font-family: 'Inter', sans-serif !important;
        }

        .footer_blog {
            background: transparent !important;
            padding: 0 !important;
            height: 100% !important;
            transition: all 0.3s ease !important;
            margin-bottom: 30px !important;
        }

        .footer_blog h3 {
            color: white !important;
            font-size: 18px !important;
            font-weight: 600 !important;
            margin: 0 0 25px 0 !important;
            padding: 0 0 10px 0 !important;
            position: relative !important;
            text-transform: uppercase !important;
            letter-spacing: 1px !important;
            font-family: 'Inter', sans-serif !important;
            border: none !important;
        }

        .footer_blog h3::after {
            content: '' !important;
            position: absolute !important;
            left: 0 !important;
            bottom: 0 !important;
            width: 40px !important;
            height: 2px !important;
            background: #3b82f6 !important;
        }

        .footer-links {
            list-style: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .footer-links li {
            margin-bottom: 12px !important;
        }

        .footer-links a {
            color: #94a3b8 !important;
            text-decoration: none !important;
            transition: all 0.3s ease !important;
            display: block !important;
            padding: 4px 0 !important;
            position: relative !important;
            font-size: 14px !important;
        }

        .footer-links a:hover {
            color: #ffffff !important;
            transform: translateX(5px) !important;
        }

        .social-links {
            display: flex !important;
            gap: 15px !important;
            margin-top: 20px !important;
        }

        .social-link {
            display: flex !important;
            align-items: center !important;
            color: #cbd5e1 !important;
            text-decoration: none !important;
            transition: all 0.3s ease !important;
            margin-bottom: 12px !important;
        }

        .social-link i {
            color: #3b82f6 !important;
            margin-right: 12px !important;
            font-size: 18px !important;
            transition: all 0.3s ease !important;
        }

        .social-link:hover {
            background: transparent !important;
            transform: translateX(5px) !important;
        }

        .footer-bottom {
            background: rgba(0, 0, 0, 0.3) !important;
            padding: 20px 0 !important;
            margin-top: 50px !important;
            font-size: 14px !important;
            color: #94a3b8 !important;
            border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
            text-align: center !important;
        }

        .crp {
            color: #94a3b8 !important;
            font-size: 0.9rem !important;
            margin: 0 !important;
            text-align: center !important;
            font-weight: 400 !important;
            line-height: 1.7 !important;
            font-family: 'Inter', sans-serif !important;
        }

        #scroll-to-top {
            position: fixed !important;
            bottom: 30px !important;
            right: 30px !important;
            width: 50px !important;
            height: 50px !important;
            background: #3b82f6 !important;
            color: white !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-decoration: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
            transition: all 0.3s ease !important;
            z-index: 999 !important;
            box-shadow: 0 5px 20px rgba(59, 130, 246, 0.3) !important;
            border: none !important;
            cursor: pointer !important;
        }

        #scroll-to-top.show {
            opacity: 1 !important;
            visibility: visible !important;
        }

        #scroll-to-top:hover {
            background: #2563eb !important;
            transform: translateY(-5px) !important;
        }
    </style>
</head>
<body>
    <?php include './headerSource.php'; ?>
    <div class="faq-container">
        <div class="page-header">
            <h1 class="page-title">Frequently Asked Questions</h1>
            <p class="page-subtitle">Find answers to common questions about AITF certification and services</p>
        </div>

        <!-- Search Section -->
        <div class="search-section">
            <div class="search-container">
                <input type="text" class="search-input" id="searchInput" placeholder="Search for questions...">
                <i class="fas fa-search search-icon"></i>
            </div>
            <div class="search-results" id="searchResults"></div>
        </div>

        <!-- Category Pills -->
        <div class="category-pills">
            <button class="category-pill active" data-category="all">All Categories</button>
            <button class="category-pill" data-category="certification">Certification Process</button>
            <button class="category-pill" data-category="iso">ISO Certification</button>
            <button class="category-pill" data-category="audit">Audit & Assessment</button>
            <button class="category-pill" data-category="general">General</button>
        </div>

        <!-- Certification Process FAQs -->
        <div class="faq-section" data-section="certification">
            <h2 class="section-title">
                <i class="fas fa-certificate"></i>
                Certification Process
            </h2>
            
            <div class="faq-item" data-category="certification">
                <div class="faq-question">
                    <span>What is the first step to get certification from AITF?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    The first step is to fill out the application form available on our website. Once submitted, our team will review your application and contact you for the next steps, including document verification and audit planning.
                </div>
            </div>

            <div class="faq-item" data-category="certification">
                <div class="faq-question">
                    <span>How long does the certification process take?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    The certification process typically takes 60-90 days, depending on the type of certification and your organization's readiness. This includes application review, documentation audit, on-site audit, and certificate issuance.
                </div>
            </div>

            <div class="faq-item" data-category="certification">
                <div class="faq-question">
                    <span>Which documents are required for certification?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    Required documents include: company registration proof, quality manual, process documentation, internal audit reports, management review, and other relevant compliance documents based on the certification type.
                </div>
            </div>
        </div>

        <!-- ISO Certification FAQs -->
        <div class="faq-section" data-section="iso">
            <h2 class="section-title">
                <i class="fas fa-globe"></i>
                ISO Certification
            </h2>
            
            <div class="faq-item" data-category="iso">
                <div class="faq-question">
                    <span>What is ISO 9001:2015 certification?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    ISO 9001:2015 is the international standard for Quality Management Systems (QMS). It helps organizations demonstrate their ability to consistently provide products and services that meet customer and regulatory requirements.
                </div>
            </div>

            <div class="faq-item" data-category="iso">
                <div class="faq-question">
                    <span>What are the benefits of ISO certification?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    Benefits include: enhanced credibility, improved operational efficiency, better customer satisfaction, increased market access, competitive advantage, and compliance with international standards.
                </div>
            </div>

            <div class="faq-item" data-category="iso">
                <div class="faq-question">
                    <span>Which ISO standards does AITF provide certification for?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    AITF provides certification for ISO 9001 (Quality Management), ISO 14001 (Environmental Management), ISO 45001 (Occupational Health & Safety), , and other relevant standards.
                </div>
            </div>

            <div class="faq-item" data-category="iso">
                <div class="faq-question">
                    <span>What is the difference between ISO 9001:2015 and previous versions?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    ISO 9001:2015 introduces risk-based thinking, greater emphasis on leadership commitment, and a more flexible structure. It focuses on organizational context and stakeholder needs compared to the 2008 version.
                </div>
            </div>

            <div class="faq-item" data-category="iso">
                <div class="faq-question">
                    <span>How long does ISO certification take?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    ISO certification typically takes 60-90 days, including gap analysis, documentation, implementation, internal audits, management review, and final certification audit by AITF auditors.
                </div>
            </div>

            <div class="faq-item" data-category="iso">
                <div class="faq-question">
                    <span>What is the cost of ISO certification?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    Certification costs vary based on organization size, complexity, scope, and chosen ISO standard. Contact AITF for a customized quote based on your specific requirements and organizational needs.
                </div>
            </div>
        </div>

        <!-- Audit & Compliance FAQs -->
        <div class="faq-section">
            <h2 class="section-title">
                <i class="fas fa-clipboard-check"></i>
                Audit & Assessment
            </h2>
            
            <div class="faq-item" data-category="audit">
                <div class="faq-question">
                    <span>How should I prepare for an audit?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    Prepare by: reviewing all documentation, conducting internal audits, ensuring records are up-to-date, training staff on quality procedures, and arranging necessary facilities for the audit team and arrange ISO awareness session and Internal Auditor's training to Process Owners for better preparation.
                </div>
            </div>

            <div class="faq-item" data-category="audit">
                <div class="faq-question">
                    <span>What is NC Report & Closure process?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    Non-Conformity (NC) Report identifies deviations from standards. The closure process involves: root cause analysis, corrective action implementation, effectiveness verification, and documentation of the resolution.
                </div>
            </div>

            <div class="faq-item" data-category="audit">
                <div class="faq-question">
                    <span>How often are surveillance audits conducted?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    Surveillance audits are typically conducted annually after initial certification. They ensure continued compliance and effectiveness of the management system.
                </div>
            </div>
        </div>

        <!-- General FAQs -->
        <div class="faq-section">
            <h2 class="section-title">
                <i class="fas fa-info-circle"></i>
                General Information
            </h2>
            
            <div class="faq-item" data-category="general">
                <div class="faq-question">
                    <span>How can I use the AITF logo after certification?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    After successful certification, you can use the AITF logo on marketing materials, products, and website as per the Logo Usage Guidelines. Download the official logo package from our resources section.
                </div>
            </div>

              
            <div class="faq-item" data-category="general">
                <div class="faq-question">
                    <span>How can I use the AITF logo after certification?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    After successful certification, you can use the AITF logo on marketing materials, products, and website as per the Logo Usage Guidelines. Download the official logo package from our resources section.
                </div>
            </div>

            <div class="faq-item" data-category="general">
                <div class="faq-question">
                    <span>What is the validity period of certification?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    AITF certifications are typically valid for 3 years, subject to annual surveillance audits and compliance. Renewal requires a recertification audit before expiry.
                </div>
            </div>

            <div class="faq-item" data-category="general">
                <div class="faq-question">
                    <span>How do I update my company information?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    Use the Information Update Form available in our download section. Information that needs to be modified should be duly filled in Update Form given on web site and submitted online. Post submission, AITF will carry out required changes in client profile.
                </div>
            </div>

            <div class="faq-item" data-category="general">
                <div class="faq-question">
                    <span>What is the validity period of certification?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    AITF certifications are typically valid for 3 years, subject to annual surveillance audits and compliance. Renewal requires a recertification audit before expiry.
                </div>
            </div>

            <div class="faq-item" data-category="general">
                <div class="faq-question">
                    <span>How do I update my company information?</span>
                    <i class="fas fa-chevron-down faq-icon"></i>
                </div>
                <div class="faq-answer">
                    Use the Information Update Form available in our download section. Submit changes in company details, contact information, or scope modifications for record updates.
                </div>
            </div>
        </div>

        
        <!-- General FAQs -->
        <div class="faq-section">
            <h2 class="section-title">
                <i class="fas fa-info-circle"></i>
                General Information
            </h2>
          
        </div>

        <div class="back-to-home">
            <a href="index.php">
                <i class="fas fa-arrow-left"></i>
                Back to Home
            </a>
        </div>
    </div>

    <?php include './footerSource.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const faqItems = document.querySelectorAll('.faq-item');
            const searchInput = document.getElementById('searchInput');
            const searchResults = document.getElementById('searchResults');
            const categoryPills = document.querySelectorAll('.category-pill');
            const faqSections = document.querySelectorAll('.faq-section');
            
            // FAQ Accordion functionality
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                
                question.addEventListener('click', () => {
                    // Close other items
                    faqItems.forEach(otherItem => {
                        if (otherItem !== item && otherItem.classList.contains('active')) {
                            otherItem.classList.remove('active');
                        }
                    });
                    
                    // Toggle current item
                    item.classList.toggle('active');
                });
            });
            
            // Category filtering
            categoryPills.forEach(pill => {
                pill.addEventListener('click', () => {
                    // Update active pill
                    categoryPills.forEach(p => p.classList.remove('active'));
                    pill.classList.add('active');
                    
                    const category = pill.dataset.category;
                    filterByCategory(category);
                });
            });
            
            // Remove the complex category assignment and use direct filtering
            function filterByCategory(category) {
                console.log('Filtering by category:', category);
                let visibleCount = 0;
                
                // Direct filtering by data-category attribute
                const allItems = document.querySelectorAll('.faq-item');
                const allSections = document.querySelectorAll('.faq-section');
                
                console.log('Total items found:', allItems.length);
                console.log('Total sections found:', allSections.length);
                
                allItems.forEach(item => {
                    const itemCategory = item.dataset.category;
                    console.log('Item category:', itemCategory, 'Looking for:', category);
                    
                    if (category === 'all' || itemCategory === category) {
                        item.style.display = 'block';
                        visibleCount++;
                        console.log('Showing item');
                    } else {
                        item.style.display = 'none';
                        console.log('Hiding item');
                    }
                });
                
                // Show/hide sections based on visible items
                allSections.forEach(section => {
                    const visibleItemsInSection = section.querySelectorAll('.faq-item[style*="block"], .faq-item:not([style*="none"])');
                    if (visibleItemsInSection.length > 0 || category === 'all') {
                        section.style.display = 'block';
                    } else {
                        section.style.display = 'none';
                    }
                });
                
                // Update search results
                if (category === 'all') {
                    searchResults.textContent = '';
                } else {
                    searchResults.textContent = `Showing ${visibleCount} questions in ${getCategoryName(category)}`;
                    searchResults.classList.remove('no-results');
                }
                console.log('Visible count:', visibleCount);
            }
            
            // Search functionality
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                const searchTerm = e.target.value.toLowerCase().trim();
                
                searchTimeout = setTimeout(() => {
                    performSearch(searchTerm);
                }, 300);
            });
            
            // Clear search when clicking on category pills
            categoryPills.forEach(pill => {
                pill.addEventListener('click', () => {
                    searchInput.value = '';
                    searchResults.textContent = '';
                });
            });
            
            function filterByCategory(category) {
                let visibleCount = 0;
                
                faqSections.forEach(section => {
                    const sectionItems = section.querySelectorAll('.faq-item');
                    let sectionHasVisibleItems = false;
                    
                    sectionItems.forEach(item => {
                        if (category === 'all' || item.dataset.category === category) {
                            item.style.display = 'block';
                            sectionHasVisibleItems = true;
                            visibleCount++;
                        } else {
                            item.style.display = 'none';
                        }
                    });
                    
                    // Show/hide section based on whether it has visible items
                    section.style.display = sectionHasVisibleItems ? 'block' : 'none';
                });
                
                // Update search results
                if (category === 'all') {
                    searchResults.textContent = '';
                } else {
                    searchResults.textContent = `Showing ${visibleCount} questions in ${getCategoryName(category)}`;
                    searchResults.classList.remove('no-results');
                }
            }
            
            function performSearch(searchTerm) {
                if (!searchTerm) {
                    // Reset to show all items
                    faqItems.forEach(item => {
                        item.style.display = 'block';
                        removeHighlight(item);
                    });
                    faqSections.forEach(section => {
                        section.style.display = 'block';
                    });
                    searchResults.textContent = '';
                    return;
                }
                
                let matchCount = 0;
                const sectionsToShow = new Set();
                
                faqItems.forEach(item => {
                    const question = item.querySelector('.faq-question span').textContent.toLowerCase();
                    const answer = item.querySelector('.faq-answer').textContent.toLowerCase();
                    
                    if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                        item.style.display = 'block';
                        highlightText(item, searchTerm);
                        matchCount++;
                        sectionsToShow.add(item.closest('.faq-section'));
                    } else {
                        item.style.display = 'none';
                        removeHighlight(item);
                    }
                });
                
                // Show only sections with matching items
                faqSections.forEach(section => {
                    if (sectionsToShow.has(section)) {
                        section.style.display = 'block';
                    } else {
                        section.style.display = 'none';
                    }
                });
                
                // Update search results
                if (matchCount > 0) {
                    searchResults.textContent = `Found ${matchCount} question${matchCount !== 1 ? 's' : ''} matching "${searchTerm}"`;
                    searchResults.classList.remove('no-results');
                } else {
                    searchResults.textContent = `No questions found matching "${searchTerm}"`;
                    searchResults.classList.add('no-results');
                }
            }
            
            function highlightText(item, searchTerm) {
                const questionSpan = item.querySelector('.faq-question span');
                const answerDiv = item.querySelector('.faq-answer');
                const originalQuestion = questionSpan.textContent;
                const originalAnswer = answerDiv.textContent;
                
                // Highlight in question
                if (originalQuestion.toLowerCase().includes(searchTerm)) {
                    const regex = new RegExp(`(${searchTerm})`, 'gi');
                    questionSpan.innerHTML = originalQuestion.replace(regex, '<span class="highlight">$1</span>');
                }
                
                // Highlight in answer
                if (originalAnswer.toLowerCase().includes(searchTerm)) {
                    const regex = new RegExp(`(${searchTerm})`, 'gi');
                    answerDiv.innerHTML = originalAnswer.replace(regex, '<span class="highlight">$1</span>');
                }
            }
            
            function removeHighlight(item) {
                const questionSpan = item.querySelector('.faq-question span');
                const answerDiv = item.querySelector('.faq-answer');
                
                // Remove highlights from question
                questionSpan.innerHTML = questionSpan.textContent;
                
                // Remove highlights from answer
                answerDiv.innerHTML = answerDiv.textContent;
            }
            
            function getCategoryName(category) {
                const names = {
                    'certification': 'Certification Process',
                    'iso': 'ISO Certification',
                    'audit': 'Audit & Compliance',
                    'general': 'General Information'
                };
                return names[category] || category;
            }
            
            // Initialize with "All Categories" selected
            filterByCategory('all');
        });
    </script>
</body>
</html>
