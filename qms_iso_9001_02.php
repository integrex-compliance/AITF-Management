<!DOCTYPE html>
<html lang="en">
    <head>
        <?php include './headSource.php'; ?>
        <style>
            :root {
                --primary-color: #1a56db;
                --secondary-color: #3f83f8;
                --accent-color: #76a9fa;
                --success-color: #0e9f6e;
                --warning-color: #f59e0b;
                --danger-color: #e02424;
                --glass-bg: rgba(255, 255, 255, 0.2);
                --glass-border: rgba(255, 255, 255, 0.25);
                --text-dark: #111827;
                --text-light: #f9fafb;
                --gradient-primary: linear-gradient(135deg, #1a56db 0%, #3f83f8 100%);
                --gradient-accent: linear-gradient(135deg, #3f83f8 0%, #76a9fa 100%);
            }

            /* Modern Gradient Background */
            body {
                background: linear-gradient(
                    135deg, 
                    rgba(249, 250, 251, 0.98) 0%, 
                    rgba(241, 245, 249, 0.95) 50%, 
                    rgba(226, 232, 240, 0.92) 100%
                ), 
                url('https://images.unsplash.com/photo-1451187580459-43490279c0fa?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2072&q=80');
                background-size: cover;
                background-attachment: fixed;
                background-blend-mode: overlay;
                min-height: 100vh;
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                color: var(--text-dark);
                line-height: 1.7;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
                overflow-x: hidden;
                font-size: 1rem;
            }

            /* Banner Styles */
            .conatiner {
                position: relative;
                overflow: hidden;
                height: 70vh;
                min-height: 500px;
                border-radius: 0 0 2rem 2rem;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                margin-bottom: 3rem;
                transition: all 0.5s ease;
            }

            .gfg {
                width: 100%;
                height: 100%;
                object-fit: cover;
                filter: brightness(0.7);
                transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .banner-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(
                    to bottom,
                    rgba(0, 0, 0, 0.2) 0%,
                    rgba(0, 0, 0, 0.6) 100%
                );
            }

            .banner-content {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 100%;
                max-width: 900px;
                padding: 0 20px;
                z-index: 2;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            }

            .banner-content h1 {
                font-size: 3.5rem;
                font-weight: 700;
                margin-bottom: 1.5rem;
                line-height: 1.2;
                opacity: 0;
                transform: translateY(30px);
                animation: fadeInUp 0.8s ease-out 0.3s forwards;
            }

            .banner-content p {
                font-size: 1.5rem;
                opacity: 0;
                transform: translateY(30px);
                animation: fadeInUp 0.8s ease-out 0.5s forwards;
            }

            .conatiner:hover .gfg {
                transform: scale(1.05);
                filter: brightness(0.9);
            }

            /* Enhanced Glass Card */
            .glass-card {
                background: rgba(255, 255, 255, 0.8);
                backdrop-filter: blur(20px) saturate(200%);
                -webkit-backdrop-filter: blur(20px) saturate(200%);
                border: 1px solid rgba(255, 255, 255, 0.6);
                border-radius: 1.25rem;
                padding: 2.5rem;
                margin: 1.5rem 0;
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
                transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
                position: relative;
                overflow: hidden;
                transform: translateY(0);
            }
            
            .glass-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                background: var(--gradient-primary);
                opacity: 0.9;
                transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
                z-index: 1;
            }

            .glass-card:hover {
                transform: translateY(-8px) scale(1.01);
                box-shadow: 0 25px 50px -12px rgba(59, 130, 246, 0.25);
                border-color: rgba(255, 255, 255, 0.8);
            }
            
            .glass-card:hover::before {
                height: 5px;
                opacity: 1;
                background: var(--gradient-accent);
            }
            
            .glass-card:active {
                transform: translateY(-4px) scale(1.005);
                box-shadow: 0 15px 30px -5px rgba(59, 130, 246, 0.2);
            }

            .heading_main_training h2 {
                font-size: 2.75rem;
                font-weight: 800;
                background: var(--gradient-primary);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                text-align: center;
                margin: 2rem 0 3rem;
                position: relative;
                display: inline-block;
                letter-spacing: -0.025em;
                line-height: 1.2;
                text-shadow: 0 2px 10px rgba(26, 86, 219, 0.1);
            }

            .heading_main_training h2::after {
                content: '';
                position: absolute;
                width: 60%;
                height: 4px;
                bottom: -10px;
                left: 20%;
                background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
                border-radius: 2px;
            }

            .layout_padding_2 {
                padding: 2rem 1.5rem;
            }
            
            /* Enhanced typography */
            h1, h2, h3, h4, h5, h6 {
                color: var(--text-dark);
                font-weight: 700;
                margin-top: 0;
                line-height: 1.3;
            }
            
            h1 { font-size: 2.75rem; }
            h2 { font-size: 2.25rem; }
            h3 { font-size: 1.75rem; }
            h4 { font-size: 1.5rem; }
            h5 { font-size: 1.25rem; }
            h6 { font-size: 1.1rem; }
            
            p {
                margin-bottom: 1.25rem;
                color: #374151;
                line-height: 1.7;
            }

            .fa-arrow-circle-right {
                color: var(--primary-color);
                margin-right: 0.5rem;
            }

            /* Modern Section Header */
            .section-header {
                position: relative;
                overflow: hidden;
                padding: 1.5rem 2rem;
                border-radius: 0.75rem;
                margin: 3rem 0 2rem;
                background: linear-gradient(90deg, 
                    rgba(26, 86, 219, 0.08) 0%, 
                    rgba(63, 131, 248, 0.08) 100%);
                border-left: 4px solid var(--primary-color);
                box-shadow: 0 4px 20px rgba(26, 86, 219, 0.08);
                transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            }
            
            .section-header::after {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(90deg, 
                    rgba(26, 86, 219, 0.12) 0%, 
                    rgba(63, 131, 248, 0.12) 100%);
                opacity: 0;
                transition: opacity 0.4s ease;
                z-index: 0;
            }
            
            .section-header h4 {
                position: relative;
                z-index: 1;
                margin: 0;
                color: var(--primary-color);
                font-weight: 700;
                font-size: 1.5rem;
                display: flex;
                align-items: center;
            }
            
            .section-header h4 i {
                margin-right: 0.75rem;
                font-size: 1.75rem;
                color: var(--primary-color);
            }
            
            .section-header:hover {
                transform: translateX(5px);
            }
            
            .section-header:hover {
                transform: translateX(5px);
                box-shadow: 0 8px 25px rgba(26, 86, 219, 0.12);
            }
            
            .section-header:hover::after {
                opacity: 1;
            }
            
            /* Enhanced list styles */
            .glass-card ul, .glass-card ol {
                padding-left: 1.5rem;
                margin: 1.5rem 0;
            }
            
            .glass-card li {
                margin-bottom: 0.75rem;
                position: relative;
                padding-left: 1.75rem;
                color: #4b5563;
            }
            
            .glass-card li::before {
                content: '•';
                color: var(--primary-color);
                font-weight: bold;
                position: absolute;
                left: 0;
                top: 0;
            }
            
            /* Responsive adjustments */
            @media (max-width: 991.98px) {
                .banner-content h1 {
                    font-size: 2.5rem;
                }
                
                .banner-content p {
                    font-size: 1.25rem;
                }
                
                .heading_main_training h2 {
                    font-size: 2.25rem;
                }
                
                .glass-card {
                    padding: 1.75rem;
                }
            }
            
            @media (max-width: 767.98px) {
                .banner-content h1 {
                    font-size: 2rem;
                }
                
                .banner-content p {
                    font-size: 1.1rem;
                }
                
                .heading_main_training h2 {
                    font-size: 2rem;
                }
                
                .section-header h4 {
                    font-size: 1.35rem;
                }
                
                .glass-card {
                    padding: 1.5rem;
                }
            }

            /* Responsive design */
            @media (max-width: 768px) {
                .heading_main_training h2 {
                    font-size: 2rem;
                }
                
                .glass-card {
                    padding: 1.5rem;
                }
            }

            /* Animation for cards */
            /* Enhanced Animations */
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            @keyframes float {
                0%, 100% {
                    transform: translateY(0) rotate(0deg);
                }
                50% {
                    transform: translateY(-15px) rotate(2deg);
                }
            }
            
            @keyframes pulse {
                0%, 100% {
                    transform: scale(1);
                    opacity: 1;
                }
                50% {
                    transform: scale(1.05);
                    opacity: 0.9;
                }
            }

            /* Staggered Animations */
            .glass-card {
                animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                opacity: 0;
                transform: translateY(30px);
            }

            /* Staggered Animation Delays */
            .glass-card:nth-child(1) { animation-delay: 0.2s; }
            .glass-card:nth-child(2) { animation-delay: 0.3s; }
            .glass-card:nth-child(3) { animation-delay: 0.4s; }
            .glass-card:nth-child(4) { animation-delay: 0.5s; }
            .glass-card:nth-child(5) { animation-delay: 0.6s; }
            .glass-card:nth-child(n+6) { animation-delay: 0.7s; }
        </style>
    </head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <head>
        <?php include './headerSource.php'; ?>
    </head>
    
    <!-- Start Banner -->
    <div class="conatiner">
        <!-- Background image with overlay -->
        <div class="position-relative">
            <img class="gfg" src="https://images.unsplash.com/photo-1531970227416-f0cddeb1f748?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Quality Management System">
            <div class="banner-overlay"></div>
            <div class="banner-content text-center">
                <h1 class="display-4 font-weight-bold text-white mb-3">Quality Management System</h1>
                <p class="lead text-white-50">Achieving Excellence Through ISO 9001:2015 Certification</p>
            </div>
        </div>
    </div>
    <!-- End Banner -->
    
    <!-- Animated Background Elements -->
    <div class="position-fixed top-0 start-0 w-100 h-100" style="z-index: -1; pointer-events: none;">
        <div class="position-absolute" style="width: 300px; height: 300px; background: linear-gradient(45deg, rgba(59, 130, 246, 0.1), rgba(96, 165, 250, 0.05)); border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; top: 20%; left: 10%; animation: float 15s ease-in-out infinite;"></div>
        <div class="position-absolute" style="width: 400px; height: 400px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.05)); border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; bottom: 10%; right: 10%; animation: float 20s ease-in-out infinite reverse;"></div>
        <div class="position-absolute" style="width: 200px; height: 200px; background: linear-gradient(225deg, rgba(139, 92, 246, 0.1), rgba(168, 85, 247, 0.05)); border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; top: 60%; left: 30%; animation: float 25s ease-in-out infinite;"></div>
    </div>
    
    <!-- Main Content Section -->
    <div class="section margin-top_50">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="glass-card text-center">
                        <div class="heading_main_training">
                            <h2>Quality Management System <span class="text-primary">(ISO 9001:2015)</span></h2>
                            <p class="lead mt-3">Enhancing Quality Through Excellence and Continuous Improvement</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end section -->
    <!-- section -->
    <div class="section layout_padding py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="glass-card">
                        <div class="section-header">
                            <h4 class="mb-0">Course Name</h4>
                        </div>
                    <div class="mt-3">
                        <p class="lead">Lead Auditor 5 Days Quality Management Systems Lead Auditor Training Course based on ISO 9001</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="glass-card">
                        <div class="section-header">
                            <h4 class="mb-0">Course Features</h4>
                        </div>
                        <div class="mt-3">
                            <p>This course is certified by the registration governing board and satisfies part of the formal training requirements for individuals seeking registration under governing board of QMS Lead Auditor Registration Scheme.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="glass-card">
                        <div class="section-header">
                            <h4 class="mb-0">Learning Objectives</h4>
                        </div>
                        <div class="mt-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> Describe the purpose of a Quality Management System and explain 8 principles of quality management.</p>
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> Explain the purpose, content and interrelationship of ISO 9001, ISO 9000, ISO 9004 and ISO 19011</p>
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> Interpret requirements of ISO 9001 in the context of an audit.</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> Describe roles and responsibilities of auditors and lead auditors.</p>
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> Plan and conduct an audit in accordance with ISO 19011.</p>
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> Report the audit, including writing valid, factual and value-adding non-conformity.</p>
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> Undertake audit follow-up activities, including evaluating the effectiveness of corrective action by process owners.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="glass-card">
                        <div class="section-header">
                            <h4 class="mb-0">Who Should Attend?</h4>
                        </div>
                        <div class="mt-3">
                            <h5 class="font-weight-bold text-primary mb-3">Anyone who is or aspires to be:</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> A Third Party Assessor or Lead Assessor</p>
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> Responsible for undertaking audits of suppliers/sub-contractors</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> Responsible for implementing internal audits and audit programs</p>
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> Responsible for implementing the ISO 9001 series of standards</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="glass-card">
                        <div class="section-header">
                            <h4 class="mb-0">Course Content</h4>
                        </div>
                        <div class="mt-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> ISO 9000 Standards series overview</p>
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> 8 Quality Management Principles</p>
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> Process Approach & PDCA Cycle</p>
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> ISO 9001 Standard Requirements</p>
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> QMS Documentation</p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> ISO 19011 Requirements</p>
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> Auditor Roles & Responsibilities</p>
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> Audit Planning & Execution</p>
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> Checklists & Non-Conformance</p>
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> Audit Reporting</p>
                                </div>
                                <div class="col-md-4">
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> Team Meetings & Follow-up</p>
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> Verification & Audit Records</p>
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> Certification Process</p>
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> Practical Exercises & Case Studies</p>
                                    <p class="mb-2"><span class="fa fa-arrow-circle-right"></span> Final Examination</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12 mb-4">
                    <div class="glass-card">
                        <div class="section-header">
                            <h4 class="mb-0">Course Structure</h4>
                        </div>
                        <div class="mt-3">
                            <p>The course is highly participative and will enable delegates to build on existing skills. Emphasis is placed on practical application of assessment knowledge and skills. Realistic case studies allow delegates to apply skills and knowledge to all stages of the assessment process. This course has been designed with input and feedback internationally from a number of trainers, customers and training consultants.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="glass-card text-center py-4">
                        <h3 class="text-primary mb-0">Duration: <span class="badge bg-primary text-white px-3 py-2">5 Days</span></h3>
                        <p class="mt-3 mb-0">Comprehensive training with hands-on exercises and practical applications</p>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    <!-- end section -->
   
</body>
</html>