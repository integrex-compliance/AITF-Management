<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './headSource.php'; ?>
    <!-- Add Particle.js Library -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    
    <style>
        /* Simple Basic Styling */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #1a237e, #0d47a1);
            color: white;
            text-align: center;
            padding: 80px 20px;
            margin-bottom: 40px;
        }
        
        .hero-content h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            font-weight: bold;
        }
        
        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        
        .content-card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .section-title h2 {
            color: #1a237e;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .simple-list {
            list-style: none;
            padding: 0;
        }
        
        .simple-list li {
            padding: 10px 0;
            padding-left: 25px;
            position: relative;
            border-bottom: 1px solid #eee;
        }
        
        .simple-list li:before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #0d47a1;
            font-weight: bold;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: #0d47a1;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
            border: none;
            cursor: pointer;
        }
        
        .btn-light {
            background: white;
            color: #0d47a1;
            border: 2px solid #0d47a1;
        }
        
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }
            .content-card {
                padding: 20px;
            }
        }
    </style>
    
    <script>
    // Simple page initialization
    document.addEventListener('DOMContentLoaded', function() {
        // No complex animations needed
    });
    </script>
</head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1>Integrated Management System</h1>
            <p>Transform Your Career with Expert ISO 9001 Training & Certification</p>
            <div>
                <button class="btn btn-light">Enroll Now</button>
                <button class="btn">Learn More</button>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container py-5">
        <!-- Course Overview -->
        <div class="content-card">
            <div class="section-title">
                <h2>Course Overview</h2>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h4><i class="fas fa-chalkboard-teacher text-primary mr-2"></i> Training</h4>
                    <p>Internal Auditor Course: IMS</p>
                    
                    <h4 class="mt-4"><i class="far fa-clock text-primary mr-2"></i> Duration</h4>
                    <p>2 Days Intensive Training</p>
                </div>
                <div class="col-md-6">
                    <h4><i class="fas fa-certificate text-primary mr-2"></i> Certification</h4>
                    <p>Certificate of Internal Quality Auditor for ISO 9001 will be issued upon successful completion</p>
                </div>
            </div>
        </div>

        <!-- Course Content -->
        <div class="content-card">
            <div class="section-title">
                <h2>Course Materials</h2>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <ul class="simple-list">
                        <li>QMS Slides including Exercises</li>
                        <li>Comprehensive Case Studies</li>
                        <li>Interactive Workbook</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <ul class="simple-list">
                        <li>Sample Quality Management System Manual</li>
                        <li>Practical Audit Templates</li>
                        <li>Additional Reference Materials</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Learning Outcomes -->
        <div class="content-card">
            <div class="section-title">
                <h2>Learning Outcomes</h2>
            </div>
            <p>By the end of this course, you will be able to:</p>
            <div class="row">
                <div class="col-md-6">
                    <ul class="simple-list">
                        <li>Develop and implement an internal audit system</li>
                        <li>Perform comprehensive internal audits per ISO 9001</li>
                        <li>Maximize value from internal audit processes</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <ul class="simple-list">
                        <li>Drive continuous improvement through audits</li>
                        <li>Master auditor selection and characteristics</li>
                        <li>Understand ISO 9000 fundamentals and audit reporting</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Who Should Attend -->
        <div class="content-card">
            <div class="section-title">
                <h2>Who Should Attend?</h2>
            </div>
            <p>This comprehensive two-day course is designed for professionals who want to develop or enhance their internal quality auditing skills, including:</p>
            <div class="row mt-4">
                <div class="col-md-6">
                    <ul class="simple-list">
                        <li>Quality Managers & Engineers</li>
                        <li>Departmental Managers</li>
                        <li>Process Owners</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <ul class="simple-list">
                        <li>Internal Auditors</li>
                        <li>Management Representatives</li>
                        <li>Quality Management Professionals</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Course Structure -->
        <div class="content-card">
            <div class="section-title">
                <h2>Course Structure</h2>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h4><i class="far fa-calendar-alt text-primary mr-2"></i> Schedule</h4>
                    <p>9:00 AM - 6:00 PM (2 Days)</p>
                    
                    <h4 class="mt-4"><i class="fas fa-chalkboard text-primary mr-2"></i> Methodology</h4>
                    <p>Interactive sessions combining theory and practical exercises</p>
                </div>
                <div class="col-md-6">
                    <h4><i class="fas fa-users text-primary mr-2"></i> Class Size</h4>
                    <p>Limited to 10 delegates for optimal learning experience</p>
                    
                    <h4 class="mt-4"><i class="fas fa-laptop-code text-primary mr-2"></i> Format</h4>
                    <p>Classroom training with hands-on exercises and case studies</p>
                </div>
            </div>
        </div>

        <!-- Eligibility & Registration -->
        <div class="content-card">
            <div class="section-title">
                <h2>Eligibility & Registration</h2>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h4><i class="fas fa-user-check text-primary mr-2"></i> Prerequisites</h4>
                    <p>Basic knowledge of Quality Assurance principles and ISO 9000 series standards is recommended.</p>
                </div>
                <div class="col-md-6">
                    <h4><i class="fas fa-file-signature text-primary mr-2"></i> How to Apply</h4>
                    <p>Download the Participant Nomination Form and email to <a href="mailto:training1@aitf.co.in">training1@aitf.co.in</a> or <a href="mailto:training2@aitf.co.in">training2@aitf.co.in</a>.</p>
                    <p class="mt-2"><small>* Advance payment required to reserve your seat. Cancellation policy: 2 days prior notice required.</small></p>
                </div>
            </div>
        </div>
    </div>
    <!-- end section -->
    <?php include './footerSource.php'; ?>
</body>
</html>