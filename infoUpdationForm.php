<!DOCTYPE html>
<html lang="en">
    <!-- Basic -->
    <head>
        <?php include __DIR__ . '/headsource.php'; ?>
    </head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <head>
        <?php include './headerSource.php'; ?>
    </head>
    <!-- Start Banner -->

    <div class="section margin-top_80">
        <div class="container">
            <div class="row">
                <div class="col-md-12 layout_padding_2">
                    <div class="full">
                        <div class="heading_main text_align_center">
                            <h2><span>INFORMATION </span>UPDATION</h2>
                        </div>                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- end section -->

    <div class="section margin-top_50">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <div class="document-card p-5 shadow-sm rounded">
                        <div class="mb-4">
                            <i class="fas fa-file-pdf display-1 text-danger mb-3"></i>
                            <h3>Information Updation Form</h3>
                            <p class="text-muted">ISO Certification Document</p>
                        </div>
                        
                        <div class="document-info bg-white p-4 rounded-3 shadow-sm border mb-4 text-start">
                            <div class="d-flex align-items-start mb-3">
                                <div class="me-3">
                                    <div class="bg-soft-primary rounded-circle p-2 d-inline-flex align-items-center justify-content-center">
                                        <i class="fas fa-file-alt text-primary"></i>
                                    </div>
                                </div>
                                <div>
                                    <p class="mb-0 text-dark">This form needs to be filled before the next periodical/surveillance audit for changes made in your organization since the last certification by AITF.</p>
                                </div>
                            </div>
                            
                            <div class="method-section p-3 bg-light rounded-2 mb-4">
                                <h5 class="text-primary mb-3 d-flex align-items-center">
                                    <span class="step-indicator bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center me-2">1</span>
                                    Method of Filling
                                </h5>
                                <ul class="list-unstyled ps-4 mb-0">
                                    <li class="position-relative ps-3 mb-2">
                                        <i class="fas fa-check-circle text-success position-absolute" style="left: 0; top: 5px;"></i>
                                        All the fields in sections 1, 2, 3, 4, 5 and 6 are to be completed
                                    </li>
                                    <li class="position-relative ps-3 mb-2">
                                        <i class="far fa-check-square text-primary position-absolute" style="left: 0; top: 5px;"></i>
                                        Use tick mark (<span class="text-success">✓</span>/<span class="text-primary">☑</span>) where appropriate
                                    </li>
                                    <li class="position-relative ps-3">
                                        <i class="fas fa-times-circle text-danger position-absolute" style="left: 0; top: 5px;"></i>
                                        Mark <span class="text-danger">"N/A"</span> where not applicable
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="alert alert-warning alert-dismissible fade show rounded-3" role="alert">
                                <div class="d-flex">
                                    <i class="fas fa-exclamation-triangle text-warning me-2 mt-1"></i>
                                    <div>
                                        <strong class="d-block mb-1">Important Note</strong>
                                        Surveillance man-days will be decided considering the present information of the organization's status.
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                            
                            <div class="alert alert-primary alert-dismissible fade show rounded-3" role="alert">
                                <div class="d-flex">
                                    <i class="fas fa-info-circle me-2 mt-1"></i>
                                    <div>
                                        <strong class="d-block mb-1">Document Requirements</strong>
                                        Any changes in organization production/Service realization Process flow, organization brochure, and customer specific requirements must be enclosed with this form.
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex flex-column flex-md-row justify-content-center gap-3 mt-4">
                            <a href="text/Information_Updation_form.pdf" target="_blank" 
                               class="btn btn-primary btn-lg px-4">
                                <i class="fas fa-eye me-2"></i>View Document
                            </a>
                            <a href="text/Information_Updation_form.pdf" download 
                               class="btn btn-outline-primary btn-lg px-4">
                                <i class="fas fa-download me-2"></i>Download Document
                            </a>
                        </div>
                        
                        <div class="file-info mt-4 text-muted">
                            <small>PDF Document • Updated on 28 Dec 2023</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .document-card {
            background: linear-gradient(145deg, #ffffff, #f8f9fa);
            border: none;
            border-radius: 16px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .document-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #2a5f9e, #3a8dff);
        }
        
        .document-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .btn-lg {
            padding: 10px 24px;
            font-weight: 500;
            border-radius: 8px;
            min-width: 200px;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: #2a5f9e;
            border-color: #2a5f9e;
        }
        
        .btn-primary:hover {
            background-color: #1e4b7e;
            border-color: #1e4b7e;
            transform: translateY(-2px);
        }
        
        .btn-outline-primary {
            color: #2a5f9e;
            border-color: #2a5f9e;
        }
        
        .btn-outline-primary:hover {
            background-color: #2a5f9e;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(42, 95, 158, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(42, 95, 158, 0.3);
        }
        
        .step-indicator {
            width: 28px;
            height: 28px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .method-section {
            border-left: 3px solid #2a5f9e;
            background-color: #f8faff;
        }
        
        .bg-soft-primary {
            background-color: rgba(42, 95, 158, 0.1);
        }
        
        .alert {
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .alert-primary {
            background-color: #f0f7ff;
            border-left: 4px solid #2a5f9e;
        }
        
        .alert-warning {
            background-color: #fff8e6;
            border-left: 4px solid #ffc107;
        }
        
        @media (max-width: 768px) {
            .document-card {
                padding: 2rem !important;
            }
            
            .btn-lg {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>

    
    <?php include './footerSource.php'; ?>

</body>
</html>
