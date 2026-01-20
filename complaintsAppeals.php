<!DOCTYPE html>
<html lang="en">
    <head>
        <?php include './headSource.php'; ?>
        <style>
            :root {
                --primary-color: #0056b3;
                --secondary-color: #17a2b8;
                --accent-color: #ff6b6b;
                --light-bg: #f8f9fa;
                --dark-text: #2c3e50;
                --gradient-primary: linear-gradient(135deg, #0056b3 0%, #17a2b8 100%);
            }
            
            body {
                font-family: 'Poppins', sans-serif;
                color: var(--dark-text);
                line-height: 1.8;
                background-color: #f5f7fa;
            }
            
            .section {
                padding: 80px 0;
                position: relative;
                overflow: hidden;
            }
            
            .heading_main h2 {
                position: relative;
                display: inline-block;
                margin-bottom: 30px;
                font-weight: 700;
                text-transform: uppercase;
                color: var(--primary-color);
            }
            
            .heading_main h2:after {
                content: '';
                position: absolute;
                left: 0;
                bottom: -10px;
                width: 60px;
                height: 3px;
                background: var(--gradient-primary);
                border-radius: 3px;
            }
            
            .heading_main h2 span {
                color: var(--secondary-color);
                position: relative;
                z-index: 1;
            }
            
            .card-style {
                background: white;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
                padding: 30px;
                margin-bottom: 30px;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                border: none;
                height: 100%;
            }
            
            .card-style:hover {
                transform: translateY(-10px);
                box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            }
            
            .card-style i {
                font-size: 50px;
                margin-bottom: 20px;
                background: var(--gradient-primary);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            
            .btn-download {
                background: var(--gradient-primary);
                color: white !important;
                border: none;
                padding: 12px 30px;
                border-radius: 50px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 1px;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(0, 86, 179, 0.3);
            }
            
            .btn-download:hover {
                transform: translateY(-3px);
                box-shadow: 0 7px 20px rgba(0, 86, 179, 0.4);
                color: white;
            }
            
            .process-step {
                position: relative;
                padding-left: 80px;
                margin-bottom: 40px;
            }
            
            .step-number {
                position: absolute;
                left: 0;
                top: 0;
                width: 60px;
                height: 60px;
                background: var(--gradient-primary);
                color: white;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                font-weight: 700;
                box-shadow: 0 5px 15px rgba(0, 86, 179, 0.3);
            }
            
            .process-content h4 {
                color: var(--primary-color);
                margin-bottom: 15px;
                font-weight: 600;
            }
            
            .img-container {
                position: relative;
                border-radius: 15px;
                overflow: hidden;
                box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
                height: 100%;
            }
            
            .img-container img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.5s ease;
            }
            
            .img-container:hover img {
                transform: scale(1.05);
            }
            
            .section-title {
                text-align: center;
                margin-bottom: 60px;
            }
            
            .section-title h2 {
                font-size: 42px;
                font-weight: 700;
                margin-bottom: 20px;
                position: relative;
                display: inline-block;
            }
            
            .section-title h2:after {
                content: '';
                position: absolute;
                left: 50%;
                bottom: -15px;
                transform: translateX(-50%);
                width: 80px;
                height: 4px;
                background: var(--gradient-primary);
                border-radius: 2px;
            }
            
            .section-title p {
                font-size: 18px;
                color: #666;
                max-width: 700px;
                margin: 0 auto;
            }
            
            @media (max-width: 991px) {
                .section {
                    padding: 60px 0;
                }
                
                .section-title h2 {
                    font-size: 32px;
                }
            }
            
            @media (max-width: 767px) {
                .section {
                    padding: 40px 0;
                }
                
                .section-title h2 {
                    font-size: 28px;
                }
                
                .process-step {
                    padding-left: 0;
                    padding-top: 70px;
                    text-align: center;
                }
                
                .step-number {
                    left: 50%;
                    transform: translateX(-50%) translateY(-50%);
                    top: 0;
                }
            }
        </style>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <head>
        <?php include './headerSource.php'; ?>
    </head>
    <!-- Start Banner -->

    <!-- Hero Section -->
    <div class="section margin-top_80" style="background: linear-gradient(135deg, rgba(0, 86, 179, 0.05) 0%, rgba(23, 162, 184, 0.05) 100%);">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 font-weight-bold mb-4" style="color: var(--primary-color);">Complaints & <span style="color: var(--secondary-color);">Appeals</span></h1>
                    <p class="lead mb-4" style="color: #555;">Your feedback is important to us. We take all complaints and appeals seriously and are committed to resolving them fairly and efficiently.</p>
                    <a href="#complaint-section" class="btn btn-primary btn-lg px-4 py-3" style="background: var(--gradient-primary); border: none; border-radius: 50px; font-weight: 600; letter-spacing: 0.5px;">
                        File a Complaint <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
                <div class="col-lg-6 mt-5 mt-lg-0">
                    <img src="images/complaint-hero.svg" alt="Complaints & Appeals" class="img-fluid" onerror="this.src='https://img.freepik.com/free-vector/customer-feedback-concept-illustration_114360-2802.jpg?w=740&t=st=1678901234~exp=1678901834~hmac=...';">
                </div>
            </div>
        </div>
    </div>

    <!-- Complaint Process -->
    <div id="complaint-section" class="section margin-top_50">
        <div class="container">
            <div class="section-title">
                <h2>Our <span>Complaint</span> Process</h2>
                <p>We follow a structured approach to ensure all complaints are handled fairly and efficiently</p>
            </div>
            
            <div class="row">
                <div class="col-lg-6">
                    <div class="process-step">
                        <div class="step-number">1</div>
                        <div class="process-content">
                            <h4>Submission</h4>
                            <p>Complaints can be submitted verbally or in writing. Our Technical Committee will acknowledge receipt and begin the review process.</p>
                        </div>
                    </div>
                    
                    <div class="process-step">
                        <div class="step-number">2</div>
                        <div class="process-content">
                            <h4>Analysis & Response</h4>
                            <p>We conduct a thorough analysis and provide a detailed response, typically within 48 hours of receipt.</p>
                        </div>
                    </div>
                    
                    <div class="process-step">
                        <div class="step-number">3</div>
                        <div class="process-content">
                            <h4>Resolution</h4>
                            <p>We work towards a fair resolution, implementing corrective actions when necessary, with regular updates throughout the process.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card-style">
                        <i class="fas fa-comment-dots"></i>
                        <h3 class="mb-4" style="color: var(--primary-color);">Submit a Complaint</h3>
                        <p>In the event of a complaint received against AITF, our Technical Committee will perform a thorough analysis and respond to the complainant, outlining how we will address the issue.</p>
                        <p>We implement appropriate corrective actions within two months, which are approved by the approval committee and communicated to all relevant parties to prevent recurrence.</p>
                        <div class="mt-4">
                            <a href="text/complaint_form.DOC" class="btn-download">
                                <i class="fas fa-download mr-2"></i> Download Complaint Form
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-5">
                <div class="col-md-6 mb-4">
                    <div class="card-style h-100">
                        <i class="fas fa-shield-alt"></i>
                        <h4>Impartial Handling</h4>
                        <p>If a complaint cannot be resolved by AITF-MCL, it is escalated to the Stakeholder Committee, which ensures impartial handling and appropriate action.</p>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card-style h-100">
                        <i class="fas fa-search"></i>
                        <h4>Thorough Investigation</h4>
                        <p>For written complaints regarding certified management systems, AITF is committed to conducting a comprehensive investigation to identify and address the root cause.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Appeals Process -->
    <div class="section margin-top_50" style="background-color: #f9fbfe;">
        <div class="container">
            <div class="section-title">
                <h2>Appeals <span>Process</span></h2>
                <p>We ensure a fair and transparent appeals process for all certification decisions</p>
            </div>
            
            <div class="row align-items-center">
                <div class="col-lg-6 order-lg-2 pl-lg-5">
                    <div class="process-step">
                        <div class="step-number">1</div>
                        <div class="process-content">
                            <h4>Submission of Appeal</h4>
                            <p>Submit your appeal in writing, including all relevant documentation and the specific decision you wish to appeal.</p>
                        </div>
                    </div>
                    
                    <div class="process-step">
                        <div class="step-number">2</div>
                        <div class="process-content">
                            <h4>Review by Technical Committee</h4>
                            <p>Our Technical Committee conducts a thorough review of all documentation and evidence related to the appeal.</p>
                        </div>
                    </div>
                    
                    <div class="process-step">
                        <div class="step-number">3</div>
                        <div class="process-content">
                            <h4>Decision & Communication</h4>
                            <p>You will receive a written response within 60 days, detailing our findings and any actions to be taken.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 order-lg-1">
                    <div class="img-container">
                        <img src="images/appeals-process.jpg" alt="Appeals Process" class="img-fluid" onerror="this.src='https://img.freepik.com/free-vector/appeals-process-concept-illustration_114360-2832.jpg?w=740&t=st=1678901456~exp=1678902056~hmac=...';">
                    </div>
                </div>
            </div>
            
            <div class="row mt-5">
                <div class="col-md-12">
                    <div class="card-style">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3 class="mb-3" style="color: var(--primary-color);">Need to File an Appeal?</h3>
                                <p class="mb-0">If you wish to appeal a decision made by AITF regarding certification, please ensure you provide all relevant documentation to support your case. Our Technical Committee will review your appeal thoroughly and respond within the stipulated timeframe.</p>
                            </div>
                            <div class="col-md-4 text-md-right mt-3 mt-md-0">
                                <a href="#" class="btn-download">
                                    <i class="fas fa-file-alt mr-2"></i> Download Appeal Form
                                </a>
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
        </div>
    </div>
    
    <!-- Confidentiality Section -->
    <div class="section margin-top_50" style="background-color: #f8fafd;">
        <div class="container">
            <div class="section-title text-left text-md-center">
                <h2><span>Confidentiality</span></h2>
                <p>Ensuring the highest level of information security and privacy</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card-style">
                        <div class="row align-items-center">
                            <div class="col-lg-4 text-center mb-4 mb-lg-0">
                                <i class="fas fa-lock" style="font-size: 80px; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                            </div>
                            <div class="col-lg-8">
                                <div class="process-step">
                                    <div class="process-content">
                                        <p>Confidentiality is committed and facilitated through signed commitments by personnel and procedures requiring limited controlled distribution of records and properly lockable facilities for archiving.</p>
                                        <p class="mb-0">In addition to the restriction of the circulation of documents of the auditee and the certifying unit, AITF-MCL is committed to maintaining confidentiality of information arising from any contact, visit or audit. This information will not be disclosed to any third party except with the customer's prior written consent or when required by the accreditation authority.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Independency Section -->
    <div class="section margin-top_50">
        <div class="container">
            <div class="section-title text-left text-md-center">
                <h2><span>Independency</span></h2>
                <p>Maintaining impartiality and objectivity in all our certification activities</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card-style">
                        <div class="row align-items-center">
                            <div class="col-lg-8 order-lg-1 order-2">
                                <div class="process-step">
                                    <div class="process-content">
                                        <p>AITF has not outsourced and is fully responsible for its decisions relating to granting, maintaining, extending, reducing, suspending, and withdrawing certifications.</p>
                                        <p class="mb-0">AITF-MCL activities are not marketed or offered in any way linked with the activities of organizations that provide management system consultancy. The certification body is committed to taking action to correct inappropriate claims by any consultancy organization stating or implying that certification would be simpler, easier, faster, or less expensive if taken by AITF-MCL. Furthermore, AITF-MCL does not recognize any consultancy organization or consultant that suggests certification would be simpler, easier, faster, or less expensive if a specified consultancy organization were used.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 text-center mb-4 mb-lg-0 order-lg-2 order-1">
                                <i class="fas fa-balance-scale" style="font-size: 80px; background: var(--gradient-primary); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
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