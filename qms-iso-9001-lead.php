<!DOCTYPE html>
<html lang="en">
    <!-- Basic -->
    <style> 

        /* Modern styling for course sections */
        .gray_bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }
        
        .gray_bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="10" cy="50" r="0.5" fill="rgba(255,255,255,0.05)"/><circle cx="90" cy="30" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .feature_box {
            text-align: center;
            padding: 40px 30px;
            margin-bottom: 30px;
            background: linear-gradient(145deg, #ffffff, #f8fafc);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1), 0 10px 20px rgba(0,0,0,0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .feature_box::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(59, 130, 246, 0.1), transparent);
            transform: rotate(45deg);
            transition: all 0.6s ease;
            opacity: 0;
        }
        
        .feature_box:hover::before {
            opacity: 1;
            transform: rotate(45deg) translate(50%, 50%);
        }
        
        .feature_box:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 30px 60px rgba(59, 130, 246, 0.3), 0 15px 30px rgba(59, 130, 246, 0.2);
            border-color: rgba(59, 130, 246, 0.3);
        }
        
        .feature_icon {
            font-size: 64px;
            color: #3b82f6;
            margin-bottom: 25px;
            position: relative;
            z-index: 2;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .feature_box h4 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #1e293b;
            position: relative;
            z-index: 2;
            background: linear-gradient(135deg, #1e293b, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .feature_box p {
            font-size: 15px;
            color: #64748b;
            line-height: 1.7;
            position: relative;
            z-index: 2;
        }
        
        .learning_point {
            display: flex;
            align-items: flex-start;
            margin-bottom: 25px;
            padding: 20px;
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border-radius: 15px;
            border-left: 5px solid #3b82f6;
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .learning_point::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            background: linear-gradient(90deg, rgba(59, 130, 246, 0.1), transparent);
            transition: width 0.3s ease;
        }
        
        .learning_point:hover::before {
            width: 100%;
        }
        
        .learning_point:hover {
            transform: translateX(10px);
            box-shadow: 0 15px 35px rgba(59, 130, 246, 0.2);
        }
        
        .learning_point i {
            color: #3b82f6;
            font-size: 24px;
            margin-right: 20px;
            margin-top: 2px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .learning_point p {
            margin: 0;
            font-size: 16px;
            color: #1e293b;
            line-height: 1.6;
            font-weight: 500;
        }
        
        .course_info {
            background: linear-gradient(145deg, #ffffff, #f1f5f9);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08), 0 10px 20px rgba(0,0,0,0.04);
            border: 1px solid rgba(255,255,255,0.5);
            position: relative;
            overflow: hidden;
        }
        
        .course_info::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #3b82f6, #8b5cf6, #ec4899, #3b82f6);
            border-radius: 20px;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .course_info:hover::before {
            opacity: 0.1;
        }
        
        .info_item {
            padding: 18px 0;
            border-bottom: 1px solid rgba(226, 232, 240, 0.5);
            font-size: 16px;
            color: #334155;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .info_item:hover {
            padding-left: 15px;
            color: #3b82f6;
        }
        
        .info_item:last-child {
            border-bottom: none;
        }
        
        .info_item strong {
            color: #1e293b;
            font-weight: 700;
            background: linear-gradient(135deg, #1e293b, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .info_item i {
            color: #3b82f6;
            margin-right: 15px;
            font-size: 18px;
        }
        
        .certification_box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            color: #fff;
            padding: 50px;
            border-radius: 25px;
            text-align: center;
            box-shadow: 0 30px 60px rgba(102, 126, 234, 0.4), 0 15px 30px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
            border: 2px solid rgba(255,255,255,0.1);
        }
        
        .certification_box::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            animation: shimmer 3s ease-in-out infinite;
        }
        
        @keyframes shimmer {
            0%, 100% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            50% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }
        
        .certification_box h3 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 25px;
            text-shadow: 0 5px 15px rgba(0,0,0,0.2);
            position: relative;
            z-index: 2;
        }
        
        .certification_box p {
            font-size: 18px;
            margin-bottom: 30px;
            opacity: 0.95;
            position: relative;
            z-index: 2;
        }
        
        .certification_box ul {
            list-style: none;
            padding: 0;
            text-align: left;
            max-width: 700px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }
        
        .certification_box ul li {
            padding: 15px 0;
            font-size: 16px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .certification_box ul li:hover {
            padding-left: 20px;
            background: rgba(255,255,255,0.05);
        }
        
        .certification_box ul li:last-child {
            border-bottom: none;
        }
        
        .certification_box ul li:before {
            content: "✓";
            color: #4ade80;
            font-weight: bold;
            margin-right: 15px;
            font-size: 20px;
            animation: checkmark 2s ease-in-out infinite;
        }
        
        @keyframes checkmark {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        
        .heading_main h2 {
            font-size: 36px;
            font-weight: 800;
            position: relative;
            padding-bottom: 20px;
            margin-bottom: 40px;
            background: linear-gradient(135deg, #1e293b, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 5px 15px rgba(59, 130, 246, 0.2);
        }
        
        .heading_main h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
            border-radius: 2px;
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
        }
        
        .heading_main.text_align_center h2:after {
            left: 50%;
            transform: translateX(-50%);
        }
        
        /* Enhanced animations */
        .section {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.8s ease forwards;
        }
        
        .section:nth-child(2) { animation-delay: 0.2s; }
        .section:nth-child(3) { animation-delay: 0.4s; }
        .section:nth-child(4) { animation-delay: 0.6s; }
        .section:nth-child(5) { animation-delay: 0.8s; }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

    </style> 
    <head>
        <?php include __DIR__ . '/headsource.php'; ?>
    </head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <head>
        <?php include './headerSource.php'; ?>
    </head>
    
    <!-- section -->
    <div class="section margin-top_50">
        <div class="container">
            <div class="row">
                <div class="col-md-12 layout_padding_2">
                    <div class="full">
                        <div class="heading_main_training text_align_center">
                            <h2><span>QMS ISO 9001:2015</span> Lead Auditor</h2>
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
                        <p class="mt-3">This comprehensive 5-day training program is designed to equip professionals with the knowledge and skills required to conduct first, second, and third-party audits of Quality Management Systems against ISO 9001:2015. The course follows the guidelines of ISO 19011 and is certified by internationally recognized accreditation bodies.</p>
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
                            <i class="fa fa-certificate"></i>
                        </div>
                        <h4>IRCA Certified</h4>
                        <p>Internationally recognized certification that meets global standards</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="full feature_box">
                        <div class="feature_icon">
                            <i class="fa fa-users"></i>
                        </div>
                        <h4>Expert Trainers</h4>
                        <p>Learn from industry experts with extensive audit experience</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="full feature_box">
                        <div class="feature_icon">
                            <i class="fa fa-book"></i>
                        </div>
                        <h4>Comprehensive Materials</h4>
                        <p>Detailed course materials and case studies included</p>
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
                                    <p>Understand the purpose and benefits of a Quality Management System</p>
                                </div>
                                <div class="full learning_point">
                                    <i class="fa fa-check-circle"></i>
                                    <p>Explain the 8 principles of quality management</p>
                                </div>
                                <div class="full learning_point">
                                    <i class="fa fa-check-circle"></i>
                                    <p>Interpret ISO 9001:2015 requirements in audit context</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="full learning_point">
                                    <i class="fa fa-check-circle"></i>
                                    <p>Plan and conduct effective QMS audits</p>
                                </div>
                                <div class="full learning_point">
                                    <i class="fa fa-check-circle"></i>
                                    <p>Report audit findings and nonconformities</p>
                                </div>
                                <div class="full learning_point">
                                    <i class="fa fa-check-circle"></i>
                                    <p>Understand roles of auditors and lead auditors</p>
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
                                <strong>Materials:</strong> Course manual, templates, case studies
                            </div>
                            <div class="info_item">
                                <strong>Assessment:</strong> Written examination + continuous evaluation
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
                                <i class="fa fa-user"></i> Quality Managers
                            </div>
                            <div class="info_item">
                                <i class="fa fa-user"></i> Management Representatives
                            </div>
                            <div class="info_item">
                                <i class="fa fa-user"></i> Internal Auditors
                            </div>
                            <div class="info_item">
                                <i class="fa fa-user"></i> Consultants
                            </div>
                            <div class="info_item">
                                <i class="fa fa-user"></i> Professionals seeking auditor certification
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
                                    <h3>IRCA Certified QMS Lead Auditor</h3>
                                    <p>Upon successful completion of the course and examination, delegates will receive:</p>
                                    <ul>
                                        <li>IRCA Certified QMS Lead Auditor Certificate</li>
                                        <li>Eligibility for IRCA registration</li>
                                        <li>40 hours of CPD credits</li>
                                        <li>Global recognition of auditing competence</li>
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
