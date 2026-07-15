<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './headSource.php'; ?>
    <style>
        /* Navbar Fix */
        .top-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar {
            padding: 0.5rem 1rem;
        }
        
        .navbar-brand {
            padding: 0;
            margin-right: 2rem;
        }
        
        .navbar-brand img {
            height: 40px;
            width: auto;
        }
        
        /* Main Container Styles */
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-bg: #f8f9fa;
            --dark-text: #2c3e50;
            --light-text: #7f8c8d;
            --border-radius: 10px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        
        body {
            padding-top: 80px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark-text);
            line-height: 1.6;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            min-height: 100vh;
            margin: 0;
            position: relative;
        }
        
        .section {
            position: relative;
            padding: 40px 0;
            overflow: hidden;
        }
        
        .section:first-of-type {
            padding-top: 0;
            padding-bottom: 20px;
        }
        
        .section:nth-of-type(2) {
            padding-top: 0;
            padding-bottom: 40px;
        }
        
        .heading_main {
            margin-bottom: 20px;
        }
        
        .heading_main h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--primary-color);
            position: relative;
            display: inline-block;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .heading_main h1 span {
            color: var(--secondary-color);
            position: relative;
        }
        
        .heading_main h1:after {
            content: '';
            position: absolute;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }
        
        .heading_main .lead {
            font-size: 1.2rem;
            color: var(--light-text);
            margin-top: 20px;
        }
        
        .header-blue-section {
            padding: 160px 0 80px;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            margin-bottom: -100px;
            text-align: center;
            position: relative;
            z-index: 1;
        }
        
        .header-blue-section h1 {
            color: #ffffff;
            font-weight: 800;
            font-size: 3rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 15px;
            text-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .header-blue-section p {
            color: rgba(255,255,255,0.9);
            font-size: 1.2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        .pdf-container {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin: 0 auto 40px;
            max-width: 900px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            z-index: 2;
        }
        
        .pdf-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
        }
        .pdf-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .pdf-title {
            font-size: 24px;
            color: #333;
            margin: 0;
            font-weight: 600;
        }
        /* Improved Button Styles */
        .btn {
            padding: 12px 25px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }
        
        .btn-group {
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .btn-group .btn {
            margin: 5px;
            min-width: 180px;
        }
        /* Button Styles */
        .btn {
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: none;
            font-size: 1rem;
            border: none;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            z-index: 1;
            min-width: 200px;
            margin: 5px 10px;
        }
        
        .btn:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.2);
            transition: var(--transition);
            z-index: -1;
        }
        
        .btn:hover:before {
            width: 100%;
        }
        
        .btn-view {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
        }
        
        .btn-view:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(46, 204, 113, 0.3);
        }
        
        .btn-download {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }
        
        .btn-download:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(52, 152, 219, 0.3);
        }
        
        .btn-outline-primary, .btn-outline-success, .btn-outline-info {
            background: transparent;
            border: 2px solid;
            padding: 8px 20px;
            font-size: 0.85rem;
        }
        
        .btn-outline-primary {
            border-color: var(--secondary-color);
            color: var(--secondary-color);
        }
        
        .btn-outline-success {
            border-color: #2ecc71;
            color: #2ecc71;
        }
        
        .btn-outline-info {
            border-color: #3498db;
            color: #3498db;
        }
        
        .btn-outline-primary, .btn-outline-success, .btn-outline-info {
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover, .btn-outline-success:hover, .btn-outline-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .pdf-viewer-container {
            width: 100%;
            height: 80vh;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }
        .pdf-viewer {
            width: 100%;
            height: 100%;
            border: none;
        }
        /* Card and Section Styles */
        .about-nc {
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: var(--border-radius);
            padding: 40px !important;
            position: relative;
            overflow: hidden;
            border: none;
            box-shadow: var(--box-shadow);
        }
        
        .about-nc:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom, var(--secondary-color), var(--accent-color));
        }
        
        .about-nc h3 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 15px;
        }
        
        .about-nc h3:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
            border-radius: 3px;
        }
        
        .about-nc p {
            color: var(--light-text);
            font-size: 1.05rem;
            line-height: 1.8;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin: 15px 0;
            padding: 15px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
            transition: var(--transition);
            border-left: 3px solid transparent;
        }
        
        .feature-item:hover {
            transform: translateX(10px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            border-left: 3px solid var(--secondary-color);
        }
        
        .feature-item i {
            margin-right: 10px;
            width: 24px;
            text-align: center;
        }
        
        .resource-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            text-align: center;
            padding: 40px 30px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.03);
            margin: 20px auto;
            max-width: 500px;
        }
        
        .resource-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
            opacity: 0.8;
        }
        
        .resource-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        .resource-card h5 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .resource-card p {
            color: #666;
            font-size: 0.9rem;
            flex-grow: 1;
            margin-bottom: 15px;
        }
        
        .document-header {
            margin-bottom: 30px;
        }
        
        .document-header h2 {
            color: #2c3e50;
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 15px;
            font-size: 1.8rem;
        }
        
        .document-header h2:after {
            content: '';
            position: absolute;
            width: 50px;
            height: 3px;
            background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 3px;
        }
        
        .document-header p {
            color: #6c757d;
            font-size: 1.1rem;
            margin-top: 15px;
        }
        
        .document-header h2:after {
            content: '';
            position: absolute;
            width: 60px;
            height: 3px;
            background: #0d6efd;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .action-buttons {
            margin: 30px 0;
        }
        
        /* Animation */
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
        
        .fade-in {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        
        /* Responsive Styles */
        @media (max-width: 992px) {
            .heading_main h1 {
                font-size: 2.2rem;
            }
            
            .about-nc {
                padding: 30px 20px !important;
            }
        }
        
        @media (max-width: 768px) {
            .btn-group .btn {
                width: 100%;
                margin: 5px 0;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 10px !important;
            }
            
            .action-buttons .btn {
                width: 100%;
                margin: 5px 0;
            }
            
            .resource-card {
                margin-bottom: 20px;
            }
            
            .about-nc {
                padding: 20px !important;
            }
        }
    </style>
</head>

<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>

    <!-- Blue Header Section -->
    <div class="header-blue-section">
        <div class="container">
            <h1>NC CLOSURE REPORT</h1>
            <p>Non-Conformance Closure Documentation and Resources</p>
        </div>
    </div>

    <!-- Document Access Section -->
    <div class="section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <div class="pdf-container">
                        <div class="document-header text-center mb-5">
                            <h2 class="mb-3">NC Closure Report Document</h2>
                            <p class="lead">Access the complete Non-Conformance Closure Report in your preferred format</p>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="action-buttons d-flex justify-content-center flex-wrap gap-3 mb-4">
                            <a href="text/NC_Closure.pdf" target="_blank" class="btn btn-lg btn-view">
                                <i class="fas fa-external-link-alt me-2"></i>View in New Tab
                            </a>
                            <a href="text/NC_Closure.pdf" download class="btn btn-lg btn-download">
                                <i class="fas fa-file-download me-2"></i>Download PDF
                            </a>
                        </div>
                        
                        <!-- Help Section -->
                        <div class="additional-resources mt-5">
                            <div class="row justify-content-center">
                                <div class="col-md-12">
                                    <div class="alert alert-info" role="alert">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-info-circle fa-2x me-3"></i>
                                            <div>
                                                <h5 class="alert-heading">Need Assistance?</h5>
                                                <p class="mb-0">If you encounter any issues accessing the document or have questions about the NC closure process, please don't hesitate to contact our support team.</p>
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
    </div>

    <!-- Main Content Section -->
    <div class="section" style="padding: 30px 0 60px;">
        <div class="container">
            <!-- About NC Closure -->
            <div class="row mb-5">
                <div class="col-lg-8 offset-lg-2 text-center">
                    <div class="about-nc p-4 rounded shadow-sm bg-light">
                        <h3 class="mb-3">About Non-Conformance Closure</h3>
                        <p class="mb-4">The Non-Conformance Closure Report documents the resolution of identified non-conformities in quality management system. This comprehensive report ensures all corrective actions have been effectively implemented and verified.</p>
                        <div class="features-grid">
                            <div class="feature-item">
                                <i class="fas fa-check-circle text-success"></i>
                                <span>Documented Resolution Process</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-clipboard-check text-primary"></i>
                                <span>Verified Corrective Actions</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-file-alt text-info"></i>
                                <span>Complete Documentation</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Document Access Section Moved to Top -->
            </div>
        </div>
    </div>

    <?php include './footerSource.php'; ?>

    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Add custom JavaScript for better PDF handling -->
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
        });
    </script>
</body>
</html>
