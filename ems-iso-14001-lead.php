<!DOCTYPE html>
<html lang="en">
    <!-- Basic -->
    <style> 

        /* Custom styling for course sections */
        .gray_bg {
            background-color: #f8f9fa;
        }
        
        .feature_box {
            text-align: center;
            padding: 30px 20px;
            margin-bottom: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .feature_box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .feature_icon {
            font-size: 48px;
            color: #16a34a;
            margin-bottom: 20px;
        }
        
        .feature_box h4 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #1e293b;
        }
        
        .feature_box p {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
        }
        
        .learning_point {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
            padding: 15px;
            background: #f0fdf4;
            border-radius: 8px;
            border-left: 4px solid #16a34a;
        }
        
        .learning_point i {
            color: #16a34a;
            font-size: 20px;
            margin-right: 15px;
            margin-top: 2px;
        }
        
        .learning_point p {
            margin: 0;
            font-size: 15px;
            color: #334155;
            line-height: 1.5;
        }
        
        .course_info {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .info_item {
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 15px;
            color: #334155;
        }
        
        .info_item:last-child {
            border-bottom: none;
        }
        
        .info_item strong {
            color: #1e293b;
            font-weight: 600;
        }
        
        .info_item i {
            color: #16a34a;
            margin-right: 10px;
        }
        
        .certification_box {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            color: #fff;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
        }
        
        .certification_box h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .certification_box p {
            font-size: 16px;
            margin-bottom: 25px;
            opacity: 0.9;
        }
        
        .certification_box ul {
            list-style: none;
            padding: 0;
            text-align: left;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .certification_box ul li {
            padding: 10px 0;
            font-size: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .certification_box ul li:last-child {
            border-bottom: none;
        }
        
        .certification_box ul li:before {
            content: "✓";
            color: #4ade80;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .heading_main h2 {
            font-size: 32px;
            font-weight: 700;
            position: relative;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        
        .heading_main h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: #16a34a;
        }
        
        .heading_main.text_align_center h2:after {
            left: 50%;
            transform: translateX(-50%);
        }

    </style> 
    <head>
        <?php include './headSource.php'; ?>
    </head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <head>
        <?php include './headerSource.php'; ?>
    </head>
    
    <!-- Start Banner -->
    <div class="section margin-top_50">
        <div class="container">
            <div class="row">
                <div class="col-md-12 layout_padding_2">
                    <div class="full">
                        <div class="heading_main_training text_align_center">
                            <h2><span>EMS ISO 14001:2015</span> Lead Auditor</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end section -->
    
    <!-- Course Overview Section -->
    <div class="section layout_padding">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="full">
                        <div class="heading_main text_align_left">
                            <h2>Course Overview</h2>
                        </div>
                        <p class="mt-3">This comprehensive 5-day training program provides delegates with the knowledge and skills to conduct first, second, and third-party audits of Environmental Management Systems against ISO 14001:2015. The course follows ISO 19011 guidelines and focuses on environmental aspects, impacts, and regulatory compliance.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Key Features Section -->
    <div class="section layout_padding gray_bg">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="full">
                        <div class="heading_main text_align_left">
                            <h2>Key Features</h2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="full feature_box">
                        <div class="feature_icon">
                            <i class="fa fa-leaf"></i>
                        </div>
                        <h4>Environmental Focus</h4>
                        <p>Specialized training in environmental management and sustainability auditing</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="full feature_box">
                        <div class="feature_icon">
                            <i class="fa fa-gavel"></i>
                        </div>
                        <h4>Regulatory Compliance</h4>
                        <p>Deep understanding of environmental laws and regulatory requirements</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="full feature_box">
                        <div class="feature_icon">
                            <i class="fa fa-globe"></i>
                        </div>
                        <h4>Global Standards</h4>
                        <p>Internationally recognized EMS certification and audit practices</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Learning Outcomes Section -->
    <div class="section layout_padding">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="full">
                        <div class="heading_main text_align_left">
                            <h2>Learning Outcomes</h2>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="full learning_point">
                                    <i class="fa fa-check-circle"></i>
                                    <p>Understand environmental management principles and frameworks</p>
                                </div>
                                <div class="full learning_point">
                                    <i class="fa fa-check-circle"></i>
                                    <p>Interpret ISO 14001:2015 requirements in audit context</p>
                                </div>
                                <div class="full learning_point">
                                    <i class="fa fa-check-circle"></i>
                                    <p>Evaluate environmental aspects and impacts</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="full learning_point">
                                    <i class="fa fa-check-circle"></i>
                                    <p>Assess legal and regulatory compliance</p>
                                </div>
                                <div class="full learning_point">
                                    <i class="fa fa-check-circle"></i>
                                    <p>Plan and conduct effective EMS audits</p>
                                </div>
                                <div class="full learning_point">
                                    <i class="fa fa-check-circle"></i>
                                    <p>Report environmental audit findings</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Course Details Section -->
    <div class="section layout_padding gray_bg">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="full">
                        <div class="heading_main text_align_left">
                            <h2>Course Details</h2>
                        </div>
                        <div class="course_info">
                            <div class="info_item">
                                <strong>Duration:</strong> 5 Days (40 hours)
                            </div>
                            <div class="info_item" style="background-color: #f8f9fa; padding: 10px; border-radius: 5px; margin-top: 10px;">
                                <p style="font-size: 0.90rem; color: #6c757d; font-style: italic; margin: 0;">The above duration training is customised as per organizational need. Please contact us in case of requirement.</p>
                            </div>
                            <div class="info_item">
                                <strong>Format:</strong> Classroom / Virtual
                            </div>
                            <div class="info_item">
                                <strong>Language:</strong> English
                            </div>
                            <div class="info_item">
                                <strong>Materials:</strong> Environmental audit manual, templates, case studies
                            </div>
                            <div class="info_item">
                                <strong>Assessment:</strong> Written examination + environmental audit simulation
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="full">
                        <div class="heading_main text_align_left">
                            <h2>Who Should Attend</h2>
                        </div>
                        <div class="course_info">
                            <div class="info_item">
                                <i class="fa fa-user"></i> Environmental Managers
                            </div>
                            <div class="info_item">
                                <i class="fa fa-user"></i> Sustainability Officers
                            </div>
                            <div class="info_item">
                                <i class="fa fa-user"></i> Compliance Officers
                            </div>
                            <div class="info_item">
                                <i class="fa fa-user"></i> HSE Professionals
                            </div>
                            <div class="info_item">
                                <i class="fa fa-user"></i> Environmental Consultants
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Certification Section -->
    <div class="section layout_padding">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="full">
                        <div class="heading_main text_align_center">
                            <h2>Certification & Benefits</h2>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="full certification_box">
                                    <h3>IRCA Certified EMS Lead Auditor</h3>
                                    <p>Upon successful completion of the course and examination, delegates will receive:</p>
                                    <ul>
                                        <li>IRCA Certified EMS Lead Auditor Certificate</li>
                                        <li>Eligibility for IRCA EMS registration</li>
                                        <li>40 hours of CPD credits</li>
                                        <li>Expertise in environmental compliance auditing</li>
                                        <li>Global recognition in environmental management</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include './footerSource.php'; ?>
    </body>
</html>
