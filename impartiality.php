<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './headSource.php'; ?>
    <style>
        :root {
            --primary-color: #0056b3;
            --secondary-color: #ff6b35;
            --accent-color: #2ec4b6;
            --light-bg: #f8f9fa;
            --dark-text: #2c3e50;
            --light-text: #6c757d;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--dark-text);
            line-height: 1.8;
            overflow-x: hidden;
        }
        
        /* Hero Section */
        .hero-section {
            color: #ffffff;
            padding: 120px 0 100px;
            position: relative;
            overflow: hidden;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
            background-size: cover !important;
            background-position: center !important;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHBhdHRlcm5Vbml0cz0idXNlclNwYWNlT25Vc2UiIHBhdHRlcm5UcmFuc2Zvcm09InJvdGF0ZSg0NSkiPjxyZWN0IHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwwLjA1KSIvPjwvcGF0dGVybj48L2RlZnM+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNwYXR0ZXJuKSIvPjwvc3ZnPg==');
            opacity: 0.2;
            z-index: 1;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .hero-title {
            font-size: 3.2rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #ffffff;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.4);
        }
        
        .hero-subtitle {
            font-size: 1.4rem;
            font-weight: 400;
            margin: 2rem auto 3rem;
            max-width: 800px;
            color: rgba(255,255,255,0.95);
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
            line-height: 1.6;
        }
        
        /* Card Styles */
        .card-wrapper {
            padding: 60px 0;
            background-color: #fff;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 20px 30px;
            border-bottom: none;
            position: relative;
        }
        
        .card-header h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
            position: relative;
            display: inline-block;
        }
        
        .card-header h2::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -10px;
            width: 50px;
            height: 3px;
            background: var(--secondary-color);
            transition: width 0.3s ease;
        }
        
        .card:hover .card-header h2::after {
            width: 100px;
        }
        
        .card-body {
            padding: 30px;
            color: var(--light-text);
        }
        
        .card-body p {
            margin-bottom: 20px;
            font-size: 1.05rem;
            line-height: 1.8;
        }
        
        /* Icon Styling */
        .icon-wrapper {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.15);
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 3rem;
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        
        .icon-wrapper:hover {
            transform: scale(1.05);
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 6px 20px rgba(0,0,0,0.25);
        }
        
        /* Animation */
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
        
        /* Responsive Design */
        /* Image hover effect */
        .img-hover-zoom {
            overflow: hidden;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .img-hover-zoom img {
            transition: transform 0.5s ease;
            border-radius: 10px;
        }
        
        .img-hover-zoom:hover img {
            transform: scale(1.05);
        }
        
        /* Feature boxes */
        .feature-box {
            display: flex;
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }
        
        .feature-icon {
            background: var(--primary-color);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .feature-content h5 {
            color: var(--dark-text);
            margin-bottom: 5px;
        }
        
        .icon-box {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: #e3f2fd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--primary-color);
            flex-shrink: 0;
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.2rem;
                padding: 0 20px;
            }
            
            .hero-subtitle {
                font-size: 1.2rem;
                padding: 0 20px;
            }
            
            .card {
                margin-bottom: 25px;
            }
            
            .icon-wrapper {
                width: 80px;
                height: 80px;
                font-size: 2.2rem;
            }
            
            .feature-box {
                flex-direction: column;
                text-align: center;
            }
            
            .feature-icon {
                margin: 0 auto 15px;
            }
            
            .d-flex.align-items-start {
                flex-direction: column;
                text-align: center;
            }
            
            .icon-box {
                margin: 0 auto 10px;
            }
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 5px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #004494;
        }
    </style>
</head>

<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>
    
    <!-- Hero Section with Background Image -->
    <section class="hero-section" style="position: relative; background: linear-gradient(rgba(0, 51, 102, 0.8), rgba(0, 51, 102, 0.8)), url('aitfImages/iso-truth-images01.jpg') no-repeat center center/cover;">
        <div class="container position-relative" style="z-index: 2;">
            <div class="hero-content">
                <div class="icon-wrapper">
                    <i class="fa fa-balance-scale"></i>
                </div>
                <h1 class="hero-title">IMPARTIALITY & INDEPENDENCE</h1>
                <p class="hero-subtitle">Upholding the highest standards of integrity and objectivity in certification services</p>
            </div>
        </div>
    </section>
    
    <!-- Impartiality Section with Image -->
    <section class="card-wrapper">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="img-hover-zoom" style="border-radius: 10px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                        <img src="aitfImages/iso-quality-system.jpg" alt="Impartiality in Certification" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover; min-height: 400px; transition: transform 0.5s ease;">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h2><i class="fas fa-handshake mr-2"></i>Impartiality</h2>
                        </div>
                        <div class="card-body">
                            <p class="lead">AITF-MCL is committed to maintaining the highest level of impartiality in providing management system certification services.</p>
                            
                            <div class="feature-box">
                                <div class="feature-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="feature-content">
                                    <h5>Our Impartiality Commitment</h5>
                                    <p>We ensure all certification activities are conducted objectively, free from conflicts of interest or undue influence.</p>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex">
                                        <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                                        <span>No commercial or financial pressures</span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex">
                                        <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                                        <span>Strict separation of activities</span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex">
                                        <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                                        <span>Transparent processes</span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex">
                                        <i class="fas fa-check-circle text-success mt-1 mr-2"></i>
                                        <span>Regular risk reviews</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Independence Section with Image -->
    <section class="card-wrapper bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 order-lg-2 mb-4 mb-lg-0">
                    <div class="img-hover-zoom" style="border-radius: 10px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                        <img src="aitfImages/iso-safty-01.jpg" alt="Independent Certification" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover; min-height: 400px; transition: transform 0.5s ease;">
                    </div>
                </div>
                <div class="col-lg-6 order-lg-1">
                    <div class="card h-100">
                        <div class="card-header">
                            <h2><i class="fas fa-shield-alt mr-2"></i>Independence</h2>
                        </div>
                        <div class="card-body">
                            <p class="lead">AITF maintains complete independence in all certification decisions, ensuring the highest level of credibility and trust.</p>
                            
                            <div class="feature-box">
                                <div class="feature-icon">
                                    <i class="fas fa-balance-scale"></i>
                                </div>
                                <div class="feature-content">
                                    <h5>Our Independence Guarantee</h5>
                                    <p>We maintain strict independence in our certification processes, free from any external influences or conflicts of interest.</p>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-6 mb-4">
                                    <div class="d-flex align-items-start">
                                        <div class="icon-box">
                                            <i class="fas fa-handshake"></i>
                                        </div>
                                        <div>
                                            <h6 class="font-weight-bold mb-1">No Conflicts</h6>
                                            <p class="small mb-0">Ensuring complete separation from consultancy services</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="d-flex align-items-start">
                                        <div class="icon-box" style="background: #e3f2fd;">
                                            <i class="fas fa-eye"></i>
                                        </div>
                                        <div>
                                            <h6 class="font-weight-bold mb-1">Transparent</h6>
                                            <p class="small mb-0">Clear and open certification processes</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="d-flex align-items-start">
                                        <div class="icon-box" style="background: #e8f5e9;">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <div>
                                            <h6 class="font-weight-bold mb-1">Objective</h6>
                                            <p class="small mb-0">Decisions based on evidence, not influence</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="d-flex align-items-start">
                                        <div class="icon-box" style="background: #fff3e0;">
                                            <i class="fas fa-award"></i>
                                        </div>
                                        <div>
                                            <h6 class="font-weight-bold mb-1">Credible</h6>
                                            <p class="small mb-0">Maintaining the highest standards of integrity</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info mt-4" role="alert">
                                <div class="d-flex">
                                    <i class="fas fa-info-circle fa-2x mr-3 mt-1"></i>
                                    <div>
                                        <strong>Important Notice:</strong> AITF-MCL operates independently and does not endorse any specific consultancy organization. All certification decisions are based solely on objective evidence of compliance with the relevant standards.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include './footerSource.php'; ?>
</body>
</html>