<!DOCTYPE html>
<html lang="en">
    <!-- Basic -->
    <style>
        /* Hero Section Styling */
        .gfg {
            width: 100%;
            height: 400px;
            object-fit: cover;
            position: relative;
        }
        
        /* Main Content Styling */
        .section {
            padding: 60px 0;
        }
        
        .layout_padding_2 {
            padding: 15px 0;
        }
        
        /* Heading Styling */
        .heading_main_training h2 {
            color: #095a83;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 30px;
            text-transform: uppercase;
            position: relative;
            padding-bottom: 15px;
        }
        
        .heading_main_training h2:after {
            content: '';
            position: absolute;
            width: 80px;
            height: 3px;
            background: #f39c12;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .heading_main_training h2 span {
            color: #f39c12;
        }
        
        /* Content Styling */
        .full p {
            color: #333;
            line-height: 1.8;
            margin-bottom: 15px;
        }
        
        /* List Items Styling */
        .fa-arrow-circle-right, .fa-angle-right {
            color: #f39c12;
            margin-right: 8px;
        }
        
        /* Section Headers */
        .section-header {
            color: #095a83;
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 15px;
            position: relative;
            padding-left: 15px;
        }
        
        .section-header:before {
            content: '';
            position: absolute;
            left: 0;
            top: 8px;
            width: 6px;
            height: 20px;
            background: #f39c12;
            border-radius: 3px;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .gfg {
                height: 250px;
            }
            
            .heading_main_training h2 {
                font-size: 26px;
            }
            
            .section {
                padding: 40px 0;
            }
        }
        
        /* Card-like sections */
        .content-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            padding: 30px;
            margin-bottom: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        /* Highlight Important Text */
        .highlight-text {
            color: #e74c3c;
            font-weight: 600;
        }
    </style>
    <head>
        <?php include __DIR__ . '/headsource.php'; ?>
    </head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <head>
        <?php include './headerSource.php'; ?>
    </head>

    <!-- Start Banner -->
    <div class="conatiner"> 
        <!-- background image -->
        <img class="gfg" src="aitfImages/information-security-iso-01.jpg"> 
        <!-- title and tag line with button -->
    </div> 
    <!-- End Banner -->

    <!-- section -->
    <div class="section margin-top_50" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="content-card">
                        <div class="heading_main_training text-center">
                            <h2><span>Information Security Management System</span> (ISO 27001:2013)</h2>
                            <p class="lead">Enhance your organization's information security with our comprehensive training program</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end section -->
    <!-- section -->
    <div class="section layout_padding">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="content-card">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <h4 class="section-header">Training</h4>
                            </div>
                            <div class="col-md-9">
                                <div class="full">
                                    <p class="mb-0"><i class="fa fa-certificate text-warning mr-2"></i> Internal Auditor Training Course: ISMS 27001</p>
                                </div>
                            </div>
                        </div>
                        <div class="row align-items-center mt-4">
                            <div class="col-md-3">
                                <h4 class="section-header">Participants Receive</h4>
                            </div>
                            <div class="col-md-9">
                                <div class="full">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fa fa-check-circle text-success mr-2"></i>
                                        <p class="mb-0">ISMS Slides including Exercises</p>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fa fa-check-circle text-success mr-2"></i>
                                        <p class="mb-0">ISMS 27001 standard</p>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="fa fa-check-circle text-success mr-2"></i>
                                        <p class="mb-0">Certificate of course completion</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row align-items-center mt-4">
                            <div class="col-md-3">
                                <h4 class="section-header">Duration</h4>
                            </div>
                            <div class="col-md-9">
                                <div class="full">
                                    <p class="mb-0"><i class="fa fa-clock-o text-info mr-2"></i> 2 Days Intensive Training</p>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <div class="content-card mt-4">
                        <h4 class="text-center mb-4" style="color: #095a83; font-weight: 700;">Course Learning Outcomes</h4>
                        <p class="text-center mb-4">By the end of this comprehensive training, you will gain expertise in:</p>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <i class="fa fa-check-square-o text-success mt-1 mr-3" style="font-size: 20px;"></i>
                                    <div>
                                        <h5 class="font-weight-bold mb-2">Security Techniques</h5>
                                        <p class="mb-0">Master the essential techniques and tools used in information security systems.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <i class="fa fa-check-square-o text-success mt-1 mr-3" style="font-size: 20px;"></i>
                                    <div>
                                        <h5 class="font-weight-bold mb-2">Asset Management</h5>
                                        <p class="mb-0">Understand the critical role of asset and owner identification in security.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <i class="fa fa-check-square-o text-success mt-1 mr-3" style="font-size: 20px;"></i>
                                    <div>
                                        <h5 class="font-weight-bold mb-2">Risk Assessment</h5>
                                        <p class="mb-0">Learn to identify and evaluate security threats and vulnerabilities.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex">
                                    <i class="fa fa-check-square-o text-success mt-1 mr-3" style="font-size: 20px;"></i>
                                    <div>
                                        <h5 class="font-weight-bold mb-2">Compliance</h5>
                                        <p class="mb-0">Understand ISO 27001:2013 requirements and implementation strategies.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <div class="content-card mt-4">
                        <div class="row">
                            <div class="col-lg-3">
                                <h4 class="section-header">Who Should Attend?</h4>
                            </div>
                            <div class="col-lg-9">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex bg-light p-3 rounded h-100">
                                            <i class="fa fa-users text-primary mr-3 mt-1"></i>
                                            <div>
                                                <h5 class="font-weight-bold mb-2">IT Professionals</h5>
                                                <p class="mb-0">Looking to specialize in information security management systems.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex bg-light p-3 rounded h-100">
                                            <i class="fa fa-shield text-primary mr-3 mt-1"></i>
                                            <div>
                                                <h5 class="font-weight-bold mb-2">Security Officers</h5>
                                                <p class="mb-0">Responsible for implementing and maintaining ISMS.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex bg-light p-3 rounded h-100">
                                            <i class="fa fa-search-plus text-primary mr-3 mt-1"></i>
                                            <div>
                                                <h5 class="font-weight-bold mb-2">Internal Auditors</h5>
                                                <p class="mb-0">Looking to conduct ISMS audits within their organizations.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex bg-light p-3 rounded h-100">
                                            <i class="fa fa-graduation-cap text-primary mr-3 mt-1"></i>
                                            <div>
                                                <h5 class="font-weight-bold mb-2">Compliance Managers</h5>
                                                <p class="mb-0">Ensuring organizational adherence to ISO 27001 standards.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    
                    <div class="content-card mt-4">
                        <h4 class="text-center mb-4" style="color: #095a83; font-weight: 700;">Course Content & Structure</h4>
                        
                        <div class="text-center mb-4">
                            <p>Our expert facilitators combine extensive industry experience with engaging teaching methods to deliver a comprehensive learning experience. The course is designed to be interactive, with practical exercises and real-world case studies.</p>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="course-module p-3 mb-3 border rounded">
                                    <h5 class="font-weight-bold text-primary">Module 1: ISMS Fundamentals</h5>
                                    <ul class="list-unstyled">
                                        <li class="mb-2"><i class="fa fa-check-circle text-success mr-2"></i> Introduction to ISO 27001:2013</li>
                                        <li class="mb-2"><i class="fa fa-check-circle text-success mr-2"></i> Key concepts and principles</li>
                                        <li class="mb-2"><i class="fa fa-check-circle text-success mr-2"></i> ISMS scope and objectives</li>
                                    </ul>
                                </div>
                                
                                <div class="course-module p-3 mb-3 border rounded">
                                    <h5 class="font-weight-bold text-primary">Module 2: Risk Management</h5>
                                    <ul class="list-unstyled">
                                        <li class="mb-2"><i class="fa fa-check-circle text-success mr-2"></i> Risk assessment methodologies</li>
                                        <li class="mb-2"><i class="fa fa-check-circle text-success mr-2"></i> Risk treatment options</li>
                                        <li class="mb-2"><i class="fa fa-check-circle text-success mr-2"></i> Risk evaluation techniques</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="course-module p-3 mb-3 border rounded">
                                    <h5 class="font-weight-bold text-primary">Module 3: Audit Process</h5>
                                    <ul class="list-unstyled">
                                        <li class="mb-2"><i class="fa fa-check-circle text-success mr-2"></i> Audit planning and preparation</li>
                                        <li class="mb-2"><i class="fa fa-check-circle text-success mr-2"></i> Conducting the audit</li>
                                        <li class="mb-2"><i class="fa fa-check-circle text-success mr-2"></i> Audit reporting and follow-up</li>
                                    </ul>
                                </div>
                                
                                <div class="course-module p-3 mb-3 border rounded">
                                    <h5 class="font-weight-bold text-primary">Module 4: Implementation</h5>
                                    <ul class="list-unstyled">
                                        <li class="mb-2"><i class="fa fa-check-circle text-success mr-2"></i> Documented information</li>
                                        <li class="mb-2"><i class="fa fa-check-circle text-success mr-2"></i> Performance evaluation</li>
                                        <li class="mb-2"><i class="fa fa-check-circle text-success mr-2"></i> Continual improvement</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
            
                    </div>
                    
                    <div class="content-card mt-4 bg-primary text-white">
                        <div class="row align-items-center">
                            <div class="col-lg-3 text-center text-lg-left mb-3 mb-lg-0">
                                <h3 class="mb-0">How To Apply</h3>
                            </div>
                            <div class="col-lg-6">
                                <p class="mb-0">Ready to enhance your information security expertise? Download the application form and secure your spot today!</p>
                            </div>
                            <div class="col-lg-3 text-center text-lg-right mt-3 mt-lg-0">
                                <a href="#" class="btn btn-light btn-lg px-4">Download Form</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-4" role="alert">
                        <h5 class="alert-heading">Important Information</h5>
                        <p class="mb-0">To apply for this course, please email the completed Participant Nomination Form to <a href="mailto:training1@bsc-icc.com" class="font-weight-bold">training1@bsc-icc.com</a> or <a href="mailto:training2@bsc-icc.com" class="font-weight-bold">training2@bsc-icc.com</a>. Advance payment is required to confirm your seat. For cancellations, please notify us at least 2 days before the course commencement. Nomination transfers are permitted.</p>
                    </div>

        </div>
    </div>
    <!-- end section -->
    <?php include './footerSource.php'; ?>
</body>
</html>
