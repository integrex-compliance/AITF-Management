<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './headSource.php'; ?>
    <style>
        :root {
            --primary-color: #2196F3;
            --secondary-color: #03A9F4;
            --accent-color: #00BCD4;
            --text-dark: #2c3e50;
            --text-light: #f8f9fa;
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }

        /* Modern Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--text-dark);
            line-height: 1.6;
            overflow-x: hidden;
            background-image: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgcGF0dGVyblRyYW5zZm9ybT0icm90YXRlKDQ1KSI+PHJlY3Qgd2lkdGg9IjUwJSIgaGVpZ2h0PSI1MCUiIGZpbGw9InJnYmEoMCwwLDAsMC4wMSkiLz48L3BhdHRlcm4+PC9kZWZzPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InVybCgjcGF0dGVybikiLz48L3N2Zz4=');
        }

        .conatiner {
            position: relative;
            height: 60vh;
            overflow: hidden;
        }

        .gfg {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.8);
            transition: transform 0.5s ease;
        }

        .conatiner:hover .gfg {
            transform: scale(1.05);
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--glass-shadow);
            padding: 30px;
            margin: 20px 0;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .heading_main_training h2 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
            padding-bottom: 15px;
        }

        .heading_main_training h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }

        .section {
            padding: 80px 0;
        }

        .layout_padding_2 {
            padding: 20px;
        }

        .full p {
            margin-bottom: 15px;
            font-size: 1.05rem;
            line-height: 1.7;
        }

        .fa-arrow-circle-right {
            color: var(--primary-color);
            margin-right: 8px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .heading_main_training h2 {
                font-size: 2rem;
            }
            
            .glass-card {
                padding: 20px;
                margin: 15px 0;
            }
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

        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        /* Custom Scrollbar for Firefox */
        * {
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) #f1f1f1;
        }
    </style>
</head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <head>
        <?php include './headerSource.php'; ?>
    </head>
    <!-- Start Banner -->
    <div class="conatiner"> 
        <!-- background image -->
        <img class="gfg" src="aitfImages/envirmental-system-iso-01.jpg"> 
        <!-- title and tag line with button -->
    </div> 
    <!-- End Banner -->
<!-- new stylling provided by gaurav oli -->
    <div class="section margin-top_50">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="glass-card animate-fadeInUp" style="animation-delay: 0.2s;">
                        <div class="heading_main_training text-center">
                            <h2>Environmental Management Systems (ISO 14001:2015)</h2>
                            <p class="lead text-muted">Enhancing Environmental Performance Through Effective Management Systems</p>
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
            <div class="row mb-4">
                <div class="col-12">
                    <div class="glass-card animate-fadeInUp" style="animation-delay: 0.3s;">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center h-100">
                                    <h4 class="mb-0" style="color: var(--primary-color);"><i class="fas fa-graduation-cap me-2"></i>Training:</h4>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="d-flex align-items-center h-100">
                                    <p class="mb-0"><strong>Internal Auditor Course:</strong> ISO 14001</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="glass-card animate-fadeInUp" style="animation-delay: 0.4s;">
                        <div class="row">
                            <div class="col-md-3">
                                <h4 style="color: var(--primary-color);"><i class="fas fa-gift me-2"></i>Participants Receive:</h4>
                            </div>
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><i class="fas fa-chevron-circle-right text-primary me-2"></i>EMS Slides including Exercises</p>
                                        <p><i class="fas fa-chevron-circle-right text-primary me-2"></i>ISO 14001 standard</p>
                                        <p><i class="fas fa-chevron-circle-right text-primary me-2"></i>Sample Environment Management System Manual</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><i class="fas fa-chevron-circle-right text-primary me-2"></i>Case Studies</p>
                                        <p><i class="fas fa-chevron-circle-right text-primary me-2"></i>Workbook</p>
                                        <p><i class="fas fa-chevron-circle-right text-primary me-2"></i>Certificate of Internal Auditor for ISO 14001</p>
                                    </div>
                                </div>
                                <div class="alert alert-info mt-3" style="background: rgba(13, 110, 253, 0.1); border: 1px solid rgba(13, 110, 253, 0.2);">
                                    <i class="fas fa-info-circle me-2"></i> A test will be conducted at the end of training and all successful candidates will be issued a certificate.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="glass-card animate-fadeInUp" style="animation-delay: 0.5s;">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center h-100">
                                    <h4 class="mb-0" style="color: var(--primary-color);"><i class="far fa-clock me-2"></i>Duration:</h4>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="d-flex align-items-center h-100">
                                    <div class="d-flex align-items-center">
                                        <div class="badge bg-primary bg-opacity-10 text-primary p-2 rounded-pill me-3">
                                            <i class="fas fa-calendar-day me-2"></i>2 Days
                                        </div>
                                        <div class="text-muted">
                                            <i class="far fa-calendar-alt me-1"></i> 9:00 AM - 6:00 PM
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="glass-card animate-fadeInUp" style="animation-delay: 0.6s;">
                        <div class="row">
                            <div class="col-md-3">
                                <h4 style="color: var(--primary-color);"><i class="fas fa-bullseye me-2"></i>Learning Outcomes:</h4>
                            </div>
                            <div class="col-md-9">
                                <h5 class="mb-3">By the end of the course you will be able to:</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="icon-box bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                                <i class="fas fa-check-circle text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Develop Audit Systems</h6>
                                                <p class="mb-0 text-muted small">Create effective environmental internal audit systems</p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="icon-box bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                                <i class="fas fa-check-circle text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Conduct Audits</h6>
                                                <p class="mb-0 text-muted small">Perform environmental audits per ISO 14001 requirements</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="icon-box bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                                <i class="fas fa-check-circle text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Maximize Value</h6>
                                                <p class="mb-0 text-muted small">Get optimal value from internal audit processes</p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-start">
                                            <div class="icon-box bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                                <i class="fas fa-check-circle text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Drive Improvement</h6>
                                                <p class="mb-0 text-muted small">Implement audits that ensure continuous improvement</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="glass-card animate-fadeInUp" style="animation-delay: 0.7s;">
                        <div class="row">
                            <div class="col-md-3">
                                <h4 style="color: var(--primary-color);"><i class="fas fa-chalkboard-teacher me-2"></i>Course Facilitators:</h4>
                            </div>
                            <div class="col-md-9">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-primary bg-opacity-10 p-3 rounded-circle me-4">
                                        <i class="fas fa-user-tie text-primary" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0">All course facilitators are highly qualified and experienced in both training and assessment of Environmental Management Systems. Our experts combine practical industry experience with exceptional training skills to deliver engaging and effective learning experiences.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- new stylling applied by gaurav oli -->

            <div class="row mb-4">
                <div class="col-12">
                    <div class="glass-card animate-fadeInUp" style="animation-delay: 0.8s;">
                        <div class="row">
                            <div class="col-md-3">
                                <h4 style="color: var(--primary-color);"><i class="fas fa-user-check me-2"></i>WHO SHOULD ATTEND?</h4>
                            </div>
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="icon-box bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                                <i class="fas fa-bullseye text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Course Purpose</h6>
                                                <p class="mb-0 text-muted">This comprehensive two-day program equips participants with essential knowledge and skills for conducting internal environmental audits.</p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-start">
                                            <div class="icon-box bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                                <i class="fas fa-user-tie text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Ideal For</h6>
                                                <p class="mb-0 text-muted">Quality Managers, Departmental Managers, and professionals responsible for environmental management systems.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start h-100">
                                            <div class="icon-box bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                                <i class="fas fa-users text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1">Who Will Benefit</h6>
                                                <ul class="list-unstyled">
                                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Quality Professionals</li>
                                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Environmental Managers</li>
                                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Technical Personnel</li>
                                                    <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Administrative Staff</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="glass-card animate-fadeInUp" style="animation-delay: 0.9s;">
                        <div class="row">
                            <div class="col-md-3">
                                <h4 style="color: var(--primary-color);"><i class="fas fa-clipboard-check me-2"></i>Eligibility</h4>
                            </div>
                            <div class="col-md-9">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box bg-primary bg-opacity-10 p-3 rounded-circle me-4">
                                        <i class="fas fa-user-graduate text-primary" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0">Participants should have a basic understanding of Environmental Management System (EMS) principles and familiarity with ISO 14001 standards to fully benefit from this course.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<!-- new stylling is applied by gaurav oli  -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="glass-card animate-fadeInUp" style="animation-delay: 1.0s;">
                        <div class="row">
                            <div class="col-md-3">
                                <h4 style="color: var(--primary-color);"><i class="fas fa-book-open me-2"></i>Course Content & Structure</h4>
                            </div>
                            <div class="col-md-9">
                                <div class="course-schedule mb-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-box bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                            <i class="far fa-clock text-primary"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">Course Schedule</h6>
                                            <p class="mb-0 text-muted">9:00 AM - 6:00 PM | Maximum 10 Participants</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <h5 class="mb-3">Course Methodology:</h5>
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="icon-box bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                                <i class="fas fa-chalkboard-teacher text-primary"></i>
                                            </div>
                                            <span>Interactive Presentations</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="icon-box bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                                <i class="fas fa-users text-primary"></i>
                                            </div>
                                            <span>Group Activities</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="icon-box bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                                <i class="fas fa-brain text-primary"></i>
                                            </div>
                                            <span>Brainstorming Sessions</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="icon-box bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                                <i class="fas fa-book-reader text-primary"></i>
                                            </div>
                                            <span>Case Studies</span>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="mb-3">Course Modules:</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="module-item mb-3">
                                            <div class="d-flex">
                                                <div class="icon-box bg-primary bg-opacity-10 p-2 rounded-circle me-3 flex-shrink-0">
                                                    <i class="fas fa-check-circle text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Core Concepts</h6>
                                                    <p class="mb-0 text-muted">Understanding EMS principles and ISO 14001 requirements</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="module-item mb-3">
                                            <div class="d-flex">
                                                <div class="icon-box bg-primary bg-opacity-10 p-2 rounded-circle me-3 flex-shrink-0">
                                                    <i class="fas fa-check-circle text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Audit Planning</h6>
                                                    <p class="mb-0 text-muted">Developing effective audit programs and checklists</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="module-item mb-3">
                                            <div class="d-flex">
                                                <div class="icon-box bg-primary bg-opacity-10 p-2 rounded-circle me-3 flex-shrink-0">
                                                    <i class="fas fa-check-circle text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Audit Execution</h6>
                                                    <p class="mb-0 text-muted">Conducting effective environmental audits</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="module-item mb-3">
                                            <div class="d-flex">
                                                <div class="icon-box bg-primary bg-opacity-10 p-2 rounded-circle me-3 flex-shrink-0">
                                                    <i class="fas fa-check-circle text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Reporting</h6>
                                                    <p class="mb-0 text-muted">Documenting findings and preparing audit reports</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="module-item mb-3">
                                            <div class="d-flex">
                                                <div class="icon-box bg-primary bg-opacity-10 p-2 rounded-circle me-3 flex-shrink-0">
                                                    <i class="fas fa-check-circle text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Corrective Actions</h6>
                                                    <p class="mb-0 text-muted">Implementing and verifying corrective measures</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="module-item">
                                            <div class="d-flex">
                                                <div class="icon-box bg-primary bg-opacity-10 p-2 rounded-circle me-3 flex-shrink-0">
                                                    <i class="fas fa-check-circle text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">Auditor Competencies</h6>
                                                    <p class="mb-0 text-muted">Developing essential auditor skills and attributes</p>
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

            <div class="row mb-4">
                <div class="col-12">
                    <div class="glass-card animate-fadeInUp" style="animation-delay: 1.1s; background: linear-gradient(135deg, rgba(33, 150, 243, 0.1) 0%, rgba(0, 188, 212, 0.1) 100%);">
                        <div class="row align-items-center">
                            <div class="col-lg-3 text-center text-lg-start mb-4 mb-lg-0">
                                <div class="icon-box bg-primary bg-opacity-10 p-4 rounded-circle d-inline-block">
                                    <i class="fas fa-paper-plane text-primary" style="font-size: 2rem;"></i>
                                </div>
                                <h4 class="mt-3" style="color: var(--primary-color);">HOW TO APPLY</h4>
                            </div>
                            <div class="col-lg-9">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start mb-4">
                                            <div class="icon-box bg-white bg-opacity-20 p-2 rounded-circle me-3 flex-shrink-0">
                                                <i class="fas fa-file-download text-primary"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-2">1. Download Form</h5>
                                                <p class="mb-0 text-muted">Obtain our Participant Nomination Form</p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-start">
                                            <div class="icon-box bg-white bg-opacity-20 p-2 rounded-circle me-3 flex-shrink-0">
                                                <i class="fas fa-envelope-open-text text-primary"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-2">2. Submit Application</h5>
                                                <p class="mb-0 text-muted">Email completed form to <a href="mailto:training1@aitf.co.in" class="text-primary">training1@aitf.co.in</a></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start mb-4">
                                            <div class="icon-box bg-white bg-opacity-20 p-2 rounded-circle me-3 flex-shrink-0">
                                                <i class="fas fa-credit-card text-primary"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-2">3. Secure Your Seat</h5>
                                                <p class="mb-0 text-muted">Make advance payment to confirm registration</p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-start">
                                            <div class="icon-box bg-white bg-opacity-20 p-2 rounded-circle me-3 flex-shrink-0">
                                                <i class="fas fa-info-circle text-primary"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-2">Important Notes</h5>
                                                <p class="mb-0 text-muted">• 48-hour cancellation notice required<br>• Nomination transfers permitted</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center mt-4">
                                    <a href="#" class="btn btn-primary btn-lg px-4">
                                        <i class="fas fa-download me-2"></i>Download Application Form
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- end section -->

    <?php include './footerSource.php'; ?>
</body>
</html>