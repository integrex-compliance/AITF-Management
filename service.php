<?php
include './headSource.php';
include './headerSource.php';
?>

<!-- Page Header -->
<section class="page-header" style="
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); 
    padding: 120px 0 80px; 
    color: #fff;
    position: relative;
    overflow: hidden;
">
    <!-- Animated Background Elements -->
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
        <div style="
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(56, 182, 255, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            top: -150px;
            right: -100px;
            animation: float 15s infinite alternate ease-in-out;
        "></div>
        <div style="
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.08) 0%, transparent 70%);
            border-radius: 50%;
            bottom: -200px;
            left: -100px;
            animation: float 18s infinite alternate-reverse ease-in-out;
        "></div>
    </div>
    
    <div class="container position-relative" style="z-index: 2;">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center">
                <div style="
                    background: rgba(255, 255, 255, 0.05);
                    backdrop-filter: blur(10px);
                    -webkit-backdrop-filter: blur(10px);
                    border-radius: 20px;
                    padding: 40px 30px;
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
                    transform: translateY(0);
                    transition: all 0.5s ease;
                ">
                    <h1 class="mb-4" style="
                        color: #ffffff; 
                        font-size: 3rem;
                        font-weight: 800;
                        letter-spacing: -0.5px;
                        line-height: 1.2;
                        margin-bottom: 20px !important;
                        text-shadow: 0 2px 10px rgba(0,0,0,0.2);
                        position: relative;
                        display: inline-block;
                    ">
                        <span style="
                            display: inline-block;
                            position: relative;
                        ">
                            Our Certification Services
                            <span style="
                                position: absolute;
                                bottom: -5px;
                                left: 0;
                                width: 100%;
                                height: 4px;
                                background: linear-gradient(90deg, #3b82f6, #8b5cf6);
                                border-radius: 2px;
                            "></span>
                        </span>
                    </h1>
                    <p class="lead mb-0" style="
                        color: #e2e8f0; 
                        font-size: 1.4rem;
                        max-width: 700px;
                        margin: 0 auto;
                        line-height: 1.6;
                        text-shadow: 0 1px 3px rgba(0,0,0,0.2);
                    ">
                        Comprehensive Certification Solutions Tailored for Your Business Success
                    </p>
                    <div class="mt-4">
                        <a href="#services" class="btn btn-primary btn-lg px-4 py-2" style="
                            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
                            border: none;
                            border-radius: 50px;
                            font-weight: 600;
                            letter-spacing: 0.5px;
                            padding: 12px 30px !important;
                            text-transform: uppercase;
                            font-size: 0.9rem;
                            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
                            transition: all 0.3s ease;
                        ">
                            Explore Our Services
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            100% { transform: translateY(-20px) rotate(2deg); }
        }
        .page-header:hover .floating-shape {
            transform: translateY(-10px);
        }
    </style>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="section-padding" style="padding: 80px 0;">
    <div class="container">
        <div class="row">
            <!-- Certification Services -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="service-card" style="background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); height: 100%; transition: all 0.3s ease; border-left: 4px solid #3b82f6;">
                    <div class="service-icon mb-4" style="font-size: 40px; color: #3b82f6;">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h4 style="color: #0f172a; margin-bottom: 15px;">Certification Process</h4>
                    <p style="color: #64748b; margin-bottom: 20px;">Comprehensive certification services to ensure your products and services meet the highest industry standards and regulatory requirements.</p>
                    <a href="certificationGuideline.php" class="btn btn-outline-primary">Learn More</a>
                </div>
            </div>

            <!-- Auditor Services -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="service-card" style="background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); height: 100%; transition: all 0.3s ease; border-left: 4px solid #10b981;">
                    <div class="service-icon mb-4" style="font-size: 40px; color: #10b981;">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <h4 style="color: #0f172a; margin-bottom: 15px;">Audit Services</h4>
                    <p style="color: #64748b; margin-bottom: 20px;">Professional audit services conducted by certified auditors to evaluate and improve your quality management systems.</p>
                    <a href="auditorGuideline.php" class="btn btn-outline-success">View Guidelines</a>
                </div>
            </div>

            <!-- Transition Services -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="service-card" style="background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); height: 100%; transition: all 0.3s ease; border-left: 4px solid #8b5cf6;">
                    <div class="service-icon mb-4" style="font-size: 40px; color: #8b5cf6;">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <h4 style="color: #0f172a; margin-bottom: 15px;">Transition Support</h4>
                    <p style="color: #64748b; margin-bottom: 20px;">Expert guidance and support for transitioning to new standards and certification requirements.</p>
                    <a href="transitionGuideline.php" class="btn btn-outline-primary">Transition Guide</a>
                </div>
            </div>

            <!-- Application Process -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="service-card" style="background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); height: 100%; transition: all 0.3s ease; border-left: 4px solid #f59e0b;">
                    <div class="service-icon mb-4" style="font-size: 40px; color: #f59e0b;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h4 style="color: #0f172a; margin-bottom: 15px;">Application Processing</h4>
                    <p style="color: #64748b; margin-bottom: 20px;">Streamlined application process for certification with dedicated support at every step.</p>
                    <a href="application.php" class="btn btn-outline-warning">Apply Now</a>
                </div>
            </div>

            <!-- Logo Usage -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="service-card" style="background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); height: 100%; transition: all 0.3s ease; border-left: 4px solid #ec4899;">
                    <div class="service-icon mb-4" style="font-size: 40px; color: #ec4899;">
                        <i class="fas fa-trademark"></i>
                    </div>
                    <h4 style="color: #0f172a; margin-bottom: 15px;">Logo Usage</h4>
                    <p style="color: #64748b; margin-bottom: 20px;">Guidelines and support for proper usage of certification marks and logos on your products and marketing materials.</p>
                    <a href="useOfLogo.php" class="btn btn-outline-primary">View Guidelines</a>
                </div>
            </div>

            <!-- Circulars -->
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="service-card" style="background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); height: 100%; transition: all 0.3s ease; border-left: 4px solid #14b8a6;">
                    <div class="service-icon mb-4" style="font-size: 40px; color: #14b8a6;">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <h4 style="color: #0f172a; margin-bottom: 15px;">Latest Updates</h4>
                    <p style="color: #64748b; margin-bottom: 20px;">Stay updated with the latest circulars, announcements, and industry news from AITF.</p>
                    <a href="circuler.php" class="btn btn-outline-info">View Circulars</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5" style="background: #f8fafc;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3 style="color: #0f172a; margin-bottom: 15px;">Need Help with Certification?</h3>
                <p class="mb-0" style="color: #64748b;">Our team of experts is ready to assist you with all your certification needs. Contact us today to get started.</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <a href="contactUS.php" class="btn btn-primary btn-lg">Contact Us</a>
            </div>
        </div>
    </div>
</section>

<?php include_once 'footerSource.php'; ?>

<style>
    .service-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    }
    .service-icon i {
        transition: all 0.3s ease;
    }
    .service-card:hover .service-icon i {
        transform: scale(1.1);
    }
    .btn-outline-primary {
        border-color: #3b82f6;
        color: #3b82f6;
    }
    .btn-outline-primary:hover {
        background: #3b82f6;
        color: #fff;
    }
    .btn-outline-success {
        border-color: #10b981;
        color: #10b981;
    }
    .btn-outline-success:hover {
        background: #10b981;
        color: #fff;
    }
    .btn-outline-warning {
        border-color: #f59e0b;
        color: #f59e0b;
    }
    .btn-outline-warning:hover {
        background: #f59e0b;
        color: #fff;
    }
    .btn-outline-info {
        border-color: #14b8a6;
        color: #14b8a6;
    }
    .btn-outline-info:hover {
        background: #14b8a6;
        color: #fff;
    }
    .btn-primary {
        background: #3b82f6;
        border-color: #3b82f6;
    }
    .btn-primary:hover {
        background: #2563eb;
        border-color: #2563eb;
    }
</style>
