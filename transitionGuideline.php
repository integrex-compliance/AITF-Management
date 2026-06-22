<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './headSource.php'; ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            color: #2d3748;
            line-height: 1.7;
            background-color: #f8fafc;
        }
        
        /* Header Section */
        .guideline-header {
            background: linear-gradient(135deg, #1a4b8c 0%, #0d2b4e 100%);
            color: white;
            padding: 180px 0 70px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .guideline-header:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgcGF0dGVyblRyYW5zZm9ybT0icm90YXRlKDQ1KSI+PHJlY3Qgd2lkdGg9IjUwJSIgaGVpZ2h0PSI1MCUiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wMikiLz48L3BhdHRlcm4+PC9kZWZzPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InVybCgjcGF0dGVybikiLz48L3N2Zz4=');
            opacity: 0.3;
        }
        
        .guideline-header h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            display: inline-block;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
            position: relative;
            display: inline-block;
        }
        
        .guideline-header h1:after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background: #e74c3c;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
        }
        
        .guideline-subtitle {
            font-size: 1.5rem;
            margin-bottom: 30px;
            color: #ecf0f1;
        }
        
        .document-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 50px;
        }
        
        .document-title {
            color: #2c3e50;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f1f1;
        }

        .transition-doc-header {
            background: #f8fafc;
            border: 1px solid #e9ecef;
            border-left: 5px solid #3498db;
            border-radius: 10px;
            padding: 28px 24px;
            margin-bottom: 30px;
        }
        
        .document-actions {
            margin: 30px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .btn-download {
            background: #3498db;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-download:hover {
            background: #2980b9;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .pdf-buttons-container {
            margin: 30px 0;
            text-align: center;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .btn-view-pdf, .btn-download-pdf {
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-top: 10px;
        }
        
        .btn-view-pdf {
            background: #3498db;
        }
        
        .btn-download-pdf {
            background: #2ecc71;
        }
        
        .btn-view-pdf:hover, .btn-download-pdf:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            color: white;
            text-decoration: none;
        }
        
        .btn-view-pdf:hover {
            background: #2980b9;
        }
        
        .btn-download-pdf:hover {
            background: #27ae60;
        }
        
        .btn-view-pdf i, .btn-download-pdf i {
            margin-right: 8px;
        }
        
        .document-viewer {
            width: 100%;
            height: 800px;
            border: 1px solid #e1e1e1;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .guideline-header {
                padding: 145px 0 45px;
            }
            
            .guideline-header h1 {
                font-size: 2.2rem;
            }
            
            .guideline-subtitle {
                font-size: 1.2rem;
            }
            
            .document-container {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>
    
    <!-- Header Section -->
    <section class="guideline-header">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1>TRANSITION GUIDELINE</h1>
                    <p class="guideline-subtitle">AITF Transition Guideline for ISO 9001:2015</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Document Actions Section -->
    <section class="section pt-0">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="document-container">
                        <div class="document-actions mb-5">
                            <div class="text-center">
                                <h3 class="mb-4">Access the Complete Transition Guidelines</h3>
                                <div class="pdf-buttons-container">
                                    <a href="perfect-learn/text/transition_guideline.pdf" target="_blank" class="btn-view-pdf">
                                        <i class="fa fa-eye"></i> View Document
                                    </a>
                                    <a href="perfect-learn/text/transition_guideline.pdf" download class="btn-download-pdf">
                                        <i class="fa fa-download"></i> Download PDF
                                    </a>
                                </div>
                                <p class="mt-3 text-muted">Get the complete transition guideline document for ISO 9001:2015</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Document Content Section -->
    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="document-container">
                        <h2 class="document-title">AITF MANAGEMENT CERTIFICATION LIMITED</h2>
                        <h3 class="mb-4">GUIDELINE FOR TRANSITION - ISO 9001:2015</h3>
                        
                        <div class="guideline-container">
                            <!-- Header Section -->
                            <div class="transition-doc-header text-center mb-5">
                                <h2 class="main-title">ISO 9001:2015 Transition Guidelines</h2>
                                <p class="subtitle">Your Complete Guide to Upgrading from ISO 9001:2008</p>
                                <a href="perfect-learn/text/transition_guideline.pdf" download class="btn btn-outline-primary btn-lg ml-3">
                                    <i class="fa fa-download mr-2"></i> Download PDF
                                </a>
                            </div>

                            <!-- Quick Navigation -->
                            <div class="quick-nav mb-5">
                                <h4 class="section-title mb-3">Quick Navigation</h4>
                                <div class="nav-buttons">
                                    <a href="#introduction" class="btn btn-sm btn-outline-secondary m-1">Introduction</a>
                                    <a href="#changes" class="btn btn-sm btn-outline-secondary m-1">Key Changes</a>
                                    <a href="#principles" class="btn btn-sm btn-outline-secondary m-1">Principles</a>
                                    <a href="#timeline" class="btn btn-sm btn-outline-secondary m-1">Timeline</a>
                                    <a href="#transition" class="btn btn-sm btn-outline-secondary m-1">Transition Plan</a>
                                </div>
                            </div>

                            <!-- Main Content -->
                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-lg-8">
                                    <!-- Introduction -->
                                    <section id="introduction" class="card mb-4">
                                        <div class="card-header bg-primary text-white">
                                            <h3 class="h5 mb-0">1. Introduction</h3>
                                        </div>
                                        <div class="card-body">
                                            <p class="lead">This guide provides a comprehensive overview of the transition from ISO 9001:2008 to ISO 9001:2015, highlighting the key changes and steps required for a smooth transition.</p>
                                            
                                            <div class="alert alert-info">
                                                <i class="fa fa-info-circle mr-2"></i>
                                                ISO 9001:2015 brings significant changes to quality management systems, with a stronger focus on risk-based thinking and leadership engagement.
                                            </div>

                                            <h5 class="mt-4">Key Benefits of ISO 9001:2015:</h5>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <ul class="list-check">
                                                        <li>Enhanced risk management</li>
                                                        <li>Greater leadership involvement</li>
                                                        <li>Improved process approach</li>
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <ul class="list-check">
                                                        <li>Better alignment with business strategy</li>
                                                        <li>Increased customer satisfaction</li>
                                                        <li>More flexible documentation</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    <!-- Key Changes -->
                                    <section id="changes" class="card mb-4">
                                        <div class="card-header bg-primary text-white">
                                            <h3 class="h5 mb-0">2. Key Changes in ISO 9001:2015</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="change-item">
                                                <div class="change-icon">
                                                    <i class="fa fa-users"></i>
                                                </div>
                                                <div class="change-content">
                                                    <h5>Leadership Engagement</h5>
                                                    <p>Greater emphasis on leadership commitment and accountability in the QMS.</p>
                                                </div>
                                            </div>

                                            <div class="change-item">
                                                <div class="change-icon">
                                                    <i class="fa fa-shield-alt"></i>
                                                </div>
                                                <div class="change-content">
                                                    <h5>Risk-Based Thinking</h5>
                                                    <p>Systematic approach to identifying and addressing risks and opportunities.</p>
                                                </div>
                                            </div>

                                            <div class="change-item">
                                                <div class="change-icon">
                                                    <i class="fa fa-file-alt"></i>
                                                </div>
                                                <div class="change-content">
                                                    <h5>Documented Information</h5>
                                                    <p>More flexible approach to documentation requirements.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                </div>

                                <!-- Right Column -->
                                <div class="col-lg-4">
                                    <!-- Timeline -->
                                    <section id="timeline" class="card mb-4">
                                        <div class="card-header bg-primary text-white">
                                            <h3 class="h5 mb-0">Transition Timeline</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="timeline-vertical">
                                                <div class="timeline-item">
                                                    <div class="timeline-badge bg-success">
                                                        <i class="fa fa-calendar-check"></i>
                                                    </div>
                                                    <div class="timeline-content">
                                                        <h6>15 Sept 2015</h6>
                                                        <p>ISO 9001:2015 Published</p>
                                                    </div>
                                                </div>
                                                <div class="timeline-item">
                                                    <div class="timeline-badge bg-warning">
                                                        <i class="fa fa-exclamation-triangle"></i>
                                                    </div>
                                                    <div class="timeline-content">
                                                        <h6>31 Mar 2017</h6>
                                                        <p>Last date for ISO 9001:2008 applications</p>
                                                    </div>
                                                </div>
                                                <div class="timeline-item">
                                                    <div class="timeline-badge bg-danger">
                                                        <i class="fa fa-ban"></i>
                                                    </div>
                                                    <div class="timeline-content">
                                                        <h6>15 Sept 2018</h6>
                                                        <p>ISO 9001:2008 certificates expire</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    <!-- Quick Links -->
                                    <!-- <div class="card mb-4">
                                        <div class="card-header bg-light">
                                            <h4 class="h6 mb-0">Quick Links</h4>
                                        </div>
                                        <div class="list-group list-group-flush">
                                            <a href="#" class="list-group-item list-group-item-action">
                                                <i class="fa fa-file-pdf text-danger mr-2"></i> Download Transition Checklist
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">
                                                <i class="fa fa-question-circle text-info mr-2"></i> FAQ - Transition Process
                                            </a>
                                            <a href="#" class="list-group-item list-group-item-action">
                                                <i class="fa fa-calendar-alt text-success mr-2"></i> Schedule a Consultation
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div> -->

                            <!-- Principles Section -->
                            <section id="principles" class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h3 class="h5 mb-0">3. Seven Quality Management Principles</h3>
                                </div>
                                <div class="card-body">
                                    <div class="principles-grid">
                                        <div class="principle-card">
                                            <div class="principle-icon">
                                                <i class="fa fa-user-tie"></i>
                                            </div>
                                            <h5>Customer Focus</h5>
                                            <p>Understanding and meeting customer requirements</p>
                                        </div>
                                        <div class="principle-card">
                                            <div class="principle-icon">
                                                <i class="fa fa-flag"></i>
                                            </div>
                                            <h5>Leadership</h5>
                                            <p>Establishing unity of purpose and direction</p>
                                        </div>
                                        <div class="principle-card">
                                            <div class="principle-icon">
                                                <i class="fa fa-users"></i>
                                            </div>
                                            <h5>Engagement of People</h5>
                                            <p>Competent, empowered and engaged people</p>
                                        </div>
                                        <div class="principle-card">
                                            <div class="principle-icon">
                                                <i class="fa fa-project-diagram"></i>
                                            </div>
                                            <h5>Process Approach</h5>
                                            <p>Consistent and predictable results</p>
                                        </div>
                                        <div class="principle-card">
                                            <div class="principle-icon">
                                                <i class="fa fa-chart-line"></i>
                                            </div>
                                            <h5>Improvement</h5>
                                            <p>Maintaining performance and creating new opportunities</p>
                                        </div>
                                        <div class="principle-card">
                                            <div class="principle-icon">
                                                <i class="fa fa-chart-pie"></i>
                                            </div>
                                            <h5>Evidence-based Decisions</h5>
                                            <p>Data-driven decision making</p>
                                        </div>
                                        <div class="principle-card">
                                            <div class="principle-icon">
                                                <i class="fa fa-handshake"></i>
                                            </div>
                                            <h5>Relationship Management</h5>
                                            <p>Managing relationships with interested parties</p>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Transition Plan -->
                            <section id="transition" class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h3 class="h5 mb-0">4. Transition Plan</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="plan-card">
                                                <h4><i class="fa fa-building text-primary mr-2"></i> For Existing Clients</h4>
                                                <ul class="plan-features">
                                                    <li>Transition during surveillance or recertification</li>
                                                    <li>Additional 0.5 man-day for assessment</li>
                                                    <li>6-month implementation period required</li>
                                                    <li>Gap analysis recommended</li>
                                                </ul>
                                                <button class="btn btn-sm btn-outline-primary mt-2">Learn More</button>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="plan-card">
                                                <h4><i class="fa fa-user-plus text-success mr-2"></i> For New Clients</h4>
                                                <ul class="plan-features">
                                                    <li>Direct certification to ISO 9001:2015</li>
                                                    <li>No need for transition later</li>
                                                    <li>Future-proof certification</li>
                                                    <li>Latest best practices</li>
                                                </ul>
                                                <button class="btn btn-sm btn-outline-success mt-2">Get Started</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <style>
                            .guideline-container {
                                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                                color: #333;
                                line-height: 1.6;
                            }
                            
                            .main-title {
                                color: #2c3e50;
                                font-weight: 700;
                                margin-bottom: 10px;
                            }
                            
                            .subtitle {
                                color: #7f8c8d;
                                font-size: 1.2rem;
                                margin-bottom: 20px;
                            }
                            
                            .section-title {
                                color: #2c3e50;
                                border-left: 4px solid #3498db;
                                padding-left: 15px;
                                margin: 25px 0 15px;
                            }
                            
                            .card {
                                border: none;
                                border-radius: 8px;
                                box-shadow: 0 2px 15px rgba(0,0,0,0.05);
                                margin-bottom: 25px;
                                transition: transform 0.3s ease, box-shadow 0.3s ease;
                            }
                            
                            .card:hover {
                                transform: translateY(-5px);
                                box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                            }
                            
                            .card-header {
                                border-radius: 8px 8px 0 0 !important;
                                font-weight: 600;
                            }
                            
                            .list-check {
                                list-style: none;
                                padding-left: 0;
                            }
                            
                            .list-check li {
                                position: relative;
                                padding-left: 25px;
                                margin-bottom: 10px;
                            }
                            
                            .list-check li:before {
                                content: '✓';
                                position: absolute;
                                left: 0;
                                color: #27ae60;
                                font-weight: bold;
                            }
                            
                            .alert {
                                border-left: 4px solid #3498db;
                                border-radius: 4px;
                            }
                            
                            .change-item {
                                display: flex;
                                margin-bottom: 20px;
                                padding-bottom: 20px;
                                border-bottom: 1px solid #eee;
                            }
                            
                            .change-item:last-child {
                                border-bottom: none;
                                margin-bottom: 0;
                                padding-bottom: 0;
                            }
                            
                            .change-icon {
                                width: 50px;
                                height: 50px;
                                background: #f1f9ff;
                                border-radius: 50%;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                margin-right: 15px;
                                flex-shrink: 0;
                                color: #3498db;
                                font-size: 1.2rem;
                            }
                            
                            .change-content h5 {
                                color: #2c3e50;
                                margin-bottom: 5px;
                            }
                            
                            .timeline-vertical {
                                position: relative;
                                padding-left: 30px;
                            }
                            
                            .timeline-vertical:before {
                                content: '';
                                position: absolute;
                                left: 15px;
                                top: 0;
                                bottom: 0;
                                width: 2px;
                                background: #e0e0e0;
                            }
                            
                            .timeline-item {
                                position: relative;
                                margin-bottom: 20px;
                                padding-left: 30px;
                            }
                            
                            .timeline-badge {
                                position: absolute;
                                left: 0;
                                top: 0;
                                width: 32px;
                                height: 32px;
                                border-radius: 50%;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                color: white;
                                transform: translateX(-50%);
                                margin-left: 15px;
                            }
                            
                            .timeline-content {
                                background: #f9f9f9;
                                padding: 15px;
                                border-radius: 6px;
                                border-left: 3px solid #3498db;
                            }
                            
                            .timeline-content h6 {
                                color: #2c3e50;
                                margin-bottom: 5px;
                                font-weight: 600;
                            }
                            
                            .principles-grid {
                                display: grid;
                                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                                gap: 20px;
                                margin-top: 20px;
                            }
                            
                            .principle-card {
                                background: #f8fafc;
                                padding: 20px;
                                border-radius: 8px;
                                text-align: center;
                                transition: all 0.3s ease;
                                border: 1px solid #e9ecef;
                            }
                            
                            .principle-card:hover {
                                transform: translateY(-5px);
                                box-shadow: 0 5px 15px rgba(0,0,0,0.05);
                            }
                            
                            .principle-icon {
                                width: 60px;
                                height: 60px;
                                background: #e3f2fd;
                                border-radius: 50%;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                margin: 0 auto 15px;
                                color: #1976d2;
                                font-size: 24px;
                            }
                            
                            .principle-card h5 {
                                color: #2c3e50;
                                margin-bottom: 10px;
                                font-weight: 600;
                            }
                            
                            .principle-card p {
                                color: #7f8c8d;
                                font-size: 0.9rem;
                                margin-bottom: 0;
                            }
                            
                            .plan-card {
                                background: #fff;
                                border: 1px solid #e0e0e0;
                                border-radius: 8px;
                                padding: 20px;
                                height: 100%;
                                transition: all 0.3s ease;
                            }
                            
                            .plan-card:hover {
                                border-color: #3498db;
                                box-shadow: 0 5px 15px rgba(52, 152, 219, 0.1);
                            }
                            
                            .plan-card h4 {
                                color: #2c3e50;
                                font-size: 1.1rem;
                                margin-bottom: 15px;
                                display: flex;
                                align-items: center;
                            }
                            
                            .plan-features {
                                list-style: none;
                                padding-left: 0;
                                margin-bottom: 20px;
                            }
                            
                            .plan-features li {
                                position: relative;
                                padding-left: 25px;
                                margin-bottom: 10px;
                                color: #555;
                            }
                            
                            .plan-features li:before {
                                content: '•';
                                position: absolute;
                                left: 0;
                                color: #3498db;
                                font-weight: bold;
                            }
                            
                            .action-buttons {
                                margin: 25px 0;
                            }
                            
                            .nav-buttons {
                                display: flex;
                                flex-wrap: wrap;
                                gap: 8px;
                            }
                            
                            @media (max-width: 768px) {
                                .principles-grid {
                                    grid-template-columns: 1fr;
                                }
                                
                                .card {
                                    margin-bottom: 20px;
                                }
                                
                                .action-buttons .btn {
                                    width: 100%;
                                    margin-bottom: 10px;
                                }
                                
                                .action-buttons .btn:last-child {
                                    margin-bottom: 0;
                                }
                                
                                .nav-buttons {
                                    justify-content: center;
                                }
                            }
                        </style>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <?php include './footerSource.php'; ?>

</body>
</html>