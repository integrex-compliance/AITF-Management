<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './headSource.php'; ?>
    <style>
        .document-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .document-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        .document-icon {
            font-size: 48px;
            color: #007bff;
            margin-bottom: 20px;
        }
        .document-title {
            color: #2c3e50;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .document-description {
            color: #6c757d;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        .btn-document {
            padding: 10px 25px;
            border-radius: 30px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 10px 10px 0;
            transition: all 0.3s ease;
        }
        .btn-view {
            background-color: #007bff;
            color: white;
            border: 2px solid #007bff;
        }
        .btn-view:hover {
            background-color: #0056b3;
            border-color: #0056b3;
            color: white;
        }
        .btn-download {
            background-color: #28a745;
            color: white;
            border: 2px solid #28a745;
        }
        .btn-download:hover {
            background-color: #218838;
            border-color: #1e7e34;
            color: white;
        }
        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }
        .section-header h2 {
            color: #2c3e50;
            font-weight: 700;
            position: relative;
            display: inline-block;
            padding-bottom: 15px;
        }
        .section-header h2:after {
            content: '';
            position: absolute;
            width: 70px;
            height: 3px;
            background: #007bff;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
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

        .document-section {
            padding: 120px 0 80px;
            background-color: #f8f9fa;
            position: relative;
            z-index: 2;
        }
        
        /* Ensure content doesn't hide behind fixed navbar */
        body {
            padding-top: 80px; /* Adjust this value based on your navbar height */
        }
    </style>
</head>

<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>

    <!-- Blue Header Section -->
    <div class="header-blue-section">
        <div class="container">
            <h1>Circular</h1>
            <p>Communication regarding AITF Methodology for ISO 9001:2015 transition period formats.</p>
        </div>
    </div>

    <!-- Document Section -->
    <section class="document-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="document-card text-center">
                        <div class="document-icon">
                            <i class="fa fa-file-pdf-o"></i>
                        </div>
                        <h3 class="document-title">Circular Document</h3>
                        <div class="document-description">
                            <div class="info-section">
                                <h4>Scope</h4>
                                <p>This information is for the individual/Committee involved in the certification process of AITF.</p>
                                
                                <h4>Purpose</h4>
                                <p>To communicate AITF Methodology of maintaining relevant formats of certification process, while transition period of ISO 9001:2008 to ISO 9001:2015.</p>
                                
                                <h4>Transition Details</h4>
                                <p>As mentioned in guideline G 1.10- Transition to ISO 9001:2015, ISO 9001:2008 standard was revised on 15th Sept. 2015 and became invalid after 15th Sept. 2018. During this transition period, AITF is working as per its transition policy to transition its ISO 9001:2008 clients.</p>
                                
                                <h4>Audit Process During Transition</h4>
                                <p>Until 15th Sept. 2018, both standards were valid, and during this transition period, AITF conducted audits for both standards.</p>
                                
                                <h4>Documentation Updates</h4>
                                <p>AITF has revised its existing auditing documents as per the revised standard ISO 9001:2015. The auditing documents of ISO 9001:2008 were differentiated by using Underline, while maintaining the same system format number.</p>
                                
                                <p><strong>Note:</strong> The underlined documents were specifically applicable to ISO 9001:2008 and were in use only during the transition period. After the completion of the transition period (15th Sept. 2018), the underlined documents became obsolete from the management system, and only the non-underlined documents continued to be in use.</p>
                            </div>
                        </div>
                        <style>
                            .info-section {
                                text-align: left;
                                padding: 15px;
                                background-color: #f8f9fa;
                                border-radius: 8px;
                                margin-bottom: 20px;
                            }
                            .info-section h4 {
                                color: #2c3e50;
                                margin-top: 20px;
                                margin-bottom: 10px;
                                font-weight: 600;
                            }
                            .info-section p {
                                margin-bottom: 15px;
                                line-height: 1.6;
                                color: #555;
                            }
                            .info-section strong {
                                color: #2c3e50;
                            }
                        </style>
                        <div class="document-actions">
                            <a href="text/circular_revision_of_documents.pdf" target="_blank" class="btn btn-document btn-view">
                                <i class="fa fa-eye"></i> View Document
                            </a>
                            <a href="text/circular_revision_of_documents.pdf" download class="btn btn-document btn-download">
                                <i class="fa fa-download"></i> Download Document
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include './footerSource.php'; ?>

    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</body>
</html>