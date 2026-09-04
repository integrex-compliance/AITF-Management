<!DOCTYPE html>
<html lang="en">
<head>
    <?php include __DIR__ . '/headsource.php'; ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        :root {
            --primary: #2563eb;
            --primary-light: #3b82f6;
            --primary-dark: #1d4ed8;
            --secondary: #1e40af;
            --accent: #60a5fa;
            --dark: #0f172a;
            --light: #f8fafc;
            --text: #1e293b;
            --text-light: #64748b;
            --card-bg: #ffffff;
            --gradient: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            --shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: #1e293b;  /* Darker text for better contrast */
            line-height: 1.8;  /* Increased line height */
            background-color: #ffffff;  /* Pure white background */
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            font-size: 1.05rem;  /* Slightly larger base font size */
            font-weight: 400;
            text-rendering: optimizeLegibility;
        }
        
        .outcomes-hero {
            background: var(--gradient);
            color: white;
            padding: 100px 0 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
            clip-path: polygon(0 0, 100% 0, 100% 90%, 0 100%);
            margin-bottom: 60px;
        }
        
        .outcomes-hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .outcomes-hero h1 span {
            color: #4fc3f7;
            position: relative;
            display: inline-block;
        }
        
        .outcomes-hero h1:after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: #4fc3f7;
            margin: 15px auto 0;
            border-radius: 2px;
        }
        
        .outcomes-section {
            padding: 80px 0;
            background-color: #ffffff;
        }
        
        .outcomes-card {
            background: #ffffff;  /* Solid white background */
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 35px 30px;
            margin-bottom: 30px;
            transition: var(--transition);
            border: 1px solid rgba(0, 0, 0, 0.05);
            height: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .outcomes-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }
        
        .outcomes-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .outcomes-card h2 {
            color: #0f172a;  /* Darker heading color */
            font-size: 1.8rem;  /* Slightly smaller for better hierarchy */
            font-weight: 700;
            margin-bottom: 1.5rem;  /* Better spacing */
            position: relative;
            padding-bottom: 1rem;
            line-height: 1.4;  /* Better line height for headings */
            letter-spacing: -0.3px;  /* Slightly tighter letter spacing */
        }
        
        .outcomes-card h2:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background: #2c7be5;
        }
        
        .outcomes-card h3 {
            color: #1d4ed8;  /* Darker blue for better contrast */
            font-size: 1.4rem;
            margin: 2rem 0 1.2rem;  /* Better vertical rhythm */
            position: relative;
            padding-left: 1.2rem;  /* Better alignment */
            font-weight: 600;  /* Slightly bolder */
            line-height: 1.5;  /* Better line height */
        }
        
        .outcomes-card h3:before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 5px;
            height: 20px;
            background: #2c7be5;
            border-radius: 3px;
        }
        
        .outcomes-card p {
            color: #334155;  /* Darker text for better contrast */
            line-height: 1.9;  /* Increased line height */
            font-size: 1.05rem;  /* Slightly larger font size */
            margin-bottom: 1.5rem;  /* Better paragraph spacing */
            font-weight: 400;  /* Normal weight for better readability */
            margin-bottom: 20px;
            font-size: 1.05rem;
        }
        
        .outcomes-list {
            list-style: none;
            padding: 0;
            margin: 0 0 25px 0;
        }
        
        .outcomes-list li {
            padding: 10px 0 10px 35px;
            position: relative;
            margin-bottom: 8px;
            color: #4a5568;
            line-height: 1.6;
            border-bottom: 1px solid #edf2f7;
            transition: all 0.3s ease;
        }
        
        .outcomes-list li:before {
            content: '\f00c';
            font-family: 'FontAwesome';
            position: absolute;
            left: 0;
            top: 10px;
            color: #2c7be5;
            font-size: 14px;
            width: 25px;
            height: 25px;
            background: #e3f2fd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .outcomes-list li:hover {
            transform: translateX(5px);
            color: #1a3a6a;
        }
        
        .outcomes-nested-list {
            list-style: none;
            padding: 0 0 0 30px;
            margin: 15px 0;
        }
        
        .outcomes-nested-list li {
            position: relative;
            padding: 5px 0 5px 30px;
            margin-bottom: 5px;
            color: #4a5568;
            border: none;
        }
        
        .outcomes-nested-list li:before {
            content: '\f105';
            font-family: 'FontAwesome';
            position: absolute;
            left: 0;
            top: 8px;
            color: #2c7be5;
            font-size: 14px;
            background: transparent;
            width: auto;
            height: auto;
        }
        
        .outcomes-image {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            height: 100%;
            transition: transform 0.3s ease;
        }
        
        .outcomes-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .outcomes-image:hover img {
            transform: scale(1.05);
        }
        
        .highlight-box {
            background: #f8fafc;  /* Lighter background for better contrast */
            border-left: 4px solid var(--primary);
            padding: 1.5rem 2rem;
            margin: 2.5rem 0;
            border-radius: 8px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            line-height: 1.8;  /* Better line spacing */
        }
        
        .highlight-box p {
            color: #1e40af;  /* Darker blue for better readability */
            font-weight: 500;  /* Slightly bolder */
            margin: 0;
            font-size: 1.05rem;  /* Consistent font size */
        }
        
        .highlight-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M54.627 0l.83.828-1.415 1.415L51.8 0h2.827zM5.373 0l-2.242 2.243L1.717.828 2.544 0h2.829zM48.97 0l-8.486 8.485-1.414-1.414L46.142 0h2.828zM.515 0L0 .515v2.544l3.172-3.17L.515 0zm6.272 0l2.242 2.242L10.385.828 9.556 0H6.787zM60 0v.2L57.07 3.127 60 6.056V0zM0 0v6.057L2.928 3.13 0 .201V0zm0 7.5v2.5h2.5c0-1.38-1.12-2.5-2.5-2.5zM60 7.5c-1.38 0-2.5 1.12-2.5 2.5H60V7.5zM0 15.5v2.5h2.5c0-1.38-1.12-2.5-2.5-2.5zm60 0c-1.38 0-2.5 1.12-2.5 2.5H60zM0 24c0 1.38 1.12 2.5 2.5 2.5V24H0zm0 8v2.5h2.5c0-1.38-1.12-2.5-2.5-2.5zm60 0c-1.38 0-2.5 1.12-2.5 2.5H60V32zm0 8v2.5c1.38 0 2.5-1.12 2.5-2.5H60zM0 40c0 1.38 1.12 2.5 2.5 2.5V40H0zm0 8v2.5h2.5c0-1.38-1.12-2.5-2.5-2.5zm60 0c-1.38 0-2.5 1.12-2.5 2.5H60V48zm0 8v2.5c1.38 0 2.5-1.12 2.5-2.5H60zM0 56c0 1.38 1.12 2.5 2.5 2.5V56H0zm8.485 0l-8.485 8.485 1.414 1.415L9.9 57.414 8.485 56zm2.829 0l-8.485 8.485 1.414 1.415L12.728 57.414 11.314 56zm8.486 0L11.314 64.9l1.414 1.415 8.485-8.486L19.8 56zm8.486 0l-8.485 8.485 1.414 1.415 8.485-8.486L28.286 56zm8.485 0l-8.485 8.485 1.414 1.415 8.485-8.486-1.414-1.414zm8.486 0l-8.485 8.485 1.414 1.415 8.485-8.486-1.414-1.414zm8.485 0l-8.485 8.485 1.414 1.415 8.485-8.486L53.742 56zm8.485 0l-8.485 8.485 1.414 1.415 8.485-8.486-1.414-1.414zM32.9 24.9a8 8 0 100-16 8 8 0 000 16z' fill='%233b82f6' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.5;
            z-index: 0;
        }
        
        .highlight-box p {
            margin: 0;
            font-style: italic;
            color: #2c7be5;
            font-weight: 500;
        }
        
        .outcome-item {
            margin-bottom: 1.2rem;  /* Increased spacing between items */
            padding-left: 2.2rem;  /* Better alignment */
            position: relative;
            transition: var(--transition);
            color: #1e293b;  /* Darker text */
            font-size: 1.05rem;  /* Slightly larger font */
            line-height: 1.7;  /* Better line spacing */
        }
        
        .outcome-item:hover {
            transform: translateX(5px);
        }
        
        .outcome-item::before {
            content: '✓';
            position: absolute;
            left: 0;
            top: 0;
            width: 24px;
            height: 24px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
        
        /* Responsive Design */
        @media (max-width: 991px) {
            .outcomes-hero {
                padding: 80px 0 60px;
                clip-path: polygon(0 0, 100% 0, 100% 95%, 0 100%);
            }
            
            .outcomes-hero h1 {
                font-size: 2.5rem;
            }
            
            .outcomes-card {
                padding: 35px 25px;
            }
        }
        
        @media (max-width: 768px) {
            .outcomes-hero {
                padding: 70px 0 50px;
                clip-path: polygon(0 0, 100% 0, 100% 97%, 0 100%);
            }
            
            .outcomes-hero h1 {
                font-size: 2.2rem;
                line-height: 1.3;
            }
            
            .outcomes-card {
                padding: 30px 20px;
                margin-bottom: 25px;
            }
            
            .highlight-box {
                padding: 20px 25px;
            }
        }
        
        @media (max-width: 576px) {
            .outcomes-hero {
                padding: 60px 0 40px;
            }
            
            .outcomes-hero h1 {
                font-size: 2rem;
            }
            
            .outcome-item {
                padding-left: 30px;
            }
        }
    </style>
</head>

<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>

    <!-- Hero Section -->
    <section class="outcomes-hero">
        <div class="container">
            <h1>EXPECTED <span>OUTCOMES</span></h1>
            <p class="lead" style="max-width: 800px; margin: 0 auto; font-size: 1.2rem; opacity: 0.9;">
                Achieving Excellence Through ISO 9001 Certification
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="outcomes-section">
        <div class="container">
            <div class="row align-items-center mb-5">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="outcomes-card">
                        <h2>Expected Outcomes for Accredited ISO 9001</h2>
                        <p>The International Accreditation Forum (IAF) and the International Organization for Standardization (ISO) support the following concise statement of outcomes that are to be expected as a result of accredited certification to ISO 9001. The intent is to promote a common focus throughout the entire conformity assessment chain in order to achieve these expected outcomes and thereby enhance the value and relevance of accredited certification.</p>
                        
                        <div class="highlight-box">
                            <p><i class="fa fa-star"></i> <strong>Important:</strong> ISO 9001 certification is used across private and public sectors to enhance confidence in products and services, facilitate business relationships, and support procurement processes.</p>
                        </div>
                        
                        <h3>Customer Perspective</h3>
                        <p>"For the defined certification scope, an organization with a certified quality management system consistently provides products that meet customer and applicable statutory and regulatory requirements, and aims to enhance customer satisfaction."</p>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="outcomes-image">
                        <img src="aitfImages/outcome-iso-01.jpg" alt="ISO 9001 Certification Outcomes" class="img-fluid">
                    </div>
                </div>
            </div>
            
            <!-- Notes Section -->
            <div class="row mb-5">
                <div class="col-12">
                    <div class="outcomes-card">
                        <h3>Key Notes</h3>
                        <ul class="outcomes-list">
                            <li><strong>Terminology:</strong> "Products" also includes "services" in the context of ISO 9001 certification.</li>
                            <li><strong>Customer Requirements:</strong> These may be explicitly stated (e.g., in contracts or specifications) or implicitly understood (e.g., through promotional materials or industry standards).</li>
                            <li><strong>Scope:</strong> Requirements may encompass delivery and post-delivery activities as part of the product offering.</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Expected Outcomes Section -->
            <div class="row">
                <div class="col-12">
                    <div class="outcomes-card">
                        <h2>Expected Organizational Implementation</h2>
                        <p>For organizations seeking or maintaining ISO 9001 certification, the following implementations are expected:</p>
                        
                        <ul class="outcomes-list">
                            <li><strong>Quality Management System:</strong> Establishment of a QMS that is appropriate for the organization's products, processes, and certification scope.</li>
                            <li><strong>Customer Focus:</strong> Analysis and understanding of customer needs, expectations, and relevant statutory/regulatory requirements.</li>
                            <li><strong>Product Specifications:</strong> Clear definition of product characteristics to ensure they meet all customer and regulatory requirements.</li>
                            <li><strong>Process Management:</strong> Identification and management of processes necessary to achieve desired outcomes, including conforming products and enhanced customer satisfaction.</li>
                            <li><strong>Resource Allocation:</strong> Ensuring the availability of necessary resources to support and monitor all relevant processes.</li>
                            <li><strong>Quality Control:</strong> Implementation of monitoring and control measures for defined product characteristics.</li>
                            <li><strong>Continuous Improvement:</strong> Systematic processes for preventing nonconformities and implementing improvements, including:
                                <ul class="outcomes-nested-list">
                                    <li>Correction of nonconformities, including those detected after delivery</li>
                                    <li>Root cause analysis and corrective actions to prevent recurrence</li>
                                    <li>Effective handling and resolution of customer complaints</li>
                                </ul>
                            </li>
                            <li><strong>Internal Review:</strong> Implementation of robust internal audit and management review processes.</li>
                            <li><strong>Performance Monitoring:</strong> Continuous monitoring, measurement, and improvement of the QMS effectiveness.</li>
                        </ul>
                        
                        <div class="highlight-box mt-4">
                            <p><i class="fa fa-info-circle"></i> <strong>Note:</strong> These expectations form the foundation for achieving and maintaining ISO 9001 certification, demonstrating an organization's commitment to quality and continuous improvement.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
        
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Add smooth scrolling to all links
        $("a").on('click', function(event) {
            if (this.hash !== "") {
                event.preventDefault();
                var hash = this.hash;
                $('html, body').animate({
                    scrollTop: $(hash).offset().top
                }, 800, function(){
                    window.location.hash = hash;
                });
            }
        });
    </script>
    
    <?php include './footerSource.php'; ?>
</body>
</html>
