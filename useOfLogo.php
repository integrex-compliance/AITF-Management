<!DOCTYPE html>
<html lang="en">
    <!-- Basic -->
    <head>
        <?php include './headSource.php'; ?>
    </head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <head>
        <?php include './headerSource.php'; ?>
    </head>
    
    <!-- Blue Header Section -->
    <div class="header-blue-section">
        <div class="container">
            <h1>USE OF LOGO GUIDELINE</h1>
            <p>Requirements for the use of AITF and NABCB logos and statements.</p>
        </div>
    </div>

    <!-- Start Banner -->

    <div class="section">
        <div class="container">
            <div class="row">
                <div class="col-md-12" style="position: relative; z-index: 10;">
                    <div class="full pt-0">
                        <div class="d-flex flex-wrap gap-3 justify-content-center mt-3 mb-4">
                            <a href="text/Guideline_Client_to_Use_of_Logo.pdf" download class="btn btn-primary btn-lg">
                                <i class="fa fa-download me-2"></i> Download Full Guidelines (PDF)
                            </a>
                            <a href="text/Guideline_Client_to_Use_of_Logo.pdf" target="_blank" class="btn btn-outline-primary btn-lg">
                                <i class="fa fa-eye me-2"></i> View PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- end section -->

    <div class="section margin-top_50">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title text-primary">AITF Logo Usage Guidelines</h3>
                            <p class="card-text">The following guidelines establish requirements for the use of AITF and NABCB logos, statements, and references to certification. Please ensure compliance with these standards to maintain certification integrity.</p>
                            
                            <div class="guideline-section mt-4">
                                <h4><i class="fa fa-check-circle text-success me-2"></i>Key Requirements</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="guideline-card">
                                            <h5><i class="fa fa-certificate text-warning me-2"></i>General Usage</h5>
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item"><i class="fa fa-angle-right me-2 text-primary"></i> Use only for certified scope and sites</li>
                                                <li class="list-group-item"><i class="fa fa-angle-right me-2 text-primary"></i> Do not use on products or packaging</li>
                                                <li class="list-group-item"><i class="fa fa-angle-right me-2 text-primary"></i> Maintain original logo form and colors</li>
                                                <li class="list-group-item"><i class="fa fa-angle-right me-2 text-primary"></i> No modifications or alterations allowed</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="guideline-card">
                                            <h5><i class="fa fa-ban text-danger me-2"></i>Prohibited Uses</h5>
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item"><i class="fa fa-times-circle me-2 text-danger"></i> No use on product packaging or labels</li>
                                                <li class="list-group-item"><i class="fa fa-times-circle me-2 text-danger"></i> No NABCB logo on visiting cards</li>
                                                <li class="list-group-item"><i class="fa fa-times-circle me-2 text-danger"></i> No use on vehicles or buildings</li>
                                                <li class="list-group-item"><i class="fa fa-times-circle me-2 text-danger"></i> No misleading statements about certification</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="guideline-section mt-4">
                                <h4><i class="fa fa-info-circle text-info me-2"></i>Important Notes</h4>
                                <div class="alert alert-warning">
                                    <h5><i class="fa fa-exclamation-triangle me-2"></i>Immediate Discontinuation Required For:</h5>
                                    <ul class="mb-0">
                                        <li>Suspension or withdrawal of certification</li>
                                        <li>Unauthorized changes to management system</li>
                                        <li>Failure to implement required system changes</li>
                                    </ul>
                                </div>
                                
                                <div class="alert alert-danger mt-3">
                                    <h5><i class="fa fa-exclamation-circle me-2"></i>Misuse Consequences:</h5>
                                    <p class="mb-0">Misuse may result in corrective actions, withdrawal of certification, publication of transgression, and legal action as per the guidelines document.</p>
                                </div>
                            </div>

                            <!-- Download and View buttons have been moved to the top of the page -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
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

        .card {
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .guideline-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #2a5f9e;
        }
        .guideline-card {
            background: white;
            border-radius: 8px;
            padding: 1.25rem;
            height: 100%;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .guideline-card h5 {
            color: #2a5f9e;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }
        .btn-lg {
            padding: 0.75rem 1.75rem;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
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
        }
        .list-group-item {
            border-left: none;
            border-right: none;
            padding: 0.75rem 0.5rem;
            border-color: #f0f0f0;
        }
        .alert {
            border-radius: 8px;
            border-left: 4px solid;
        }
        .alert-warning {
            border-left-color: #ffc107;
        }
        .alert-danger {
            border-left-color: #dc3545;
        }
        @media (max-width: 768px) {
            .btn-lg {
                width: 100%;
                margin-bottom: 10px;
            }
            .guideline-card {
                margin-bottom: 1.5rem;
            }
        }
    </style>

    
    <?php include './footerSource.php'; ?>

</body>
</html>