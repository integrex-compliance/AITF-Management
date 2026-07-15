<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './headSource.php'; ?>
    <style>
        :root {
            --primary-color: #1565C0;
            --secondary-color: #1976D2;
            --accent-color: #0097A7;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --text-light: #f8fafc;
            --card-bg: #ffffff;
            --card-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            --card-hover-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: var(--primary-color); border-radius: 10px; }

        body {
            font-family: 'Inter', 'Poppins', sans-serif;
            background: #f1f5f9;
            color: var(--text-dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Hero Banner */
        .conatiner {
            position: relative;
            height: 50vh;
            min-height: 340px;
            overflow: hidden;
        }

        .conatiner::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.3) 0%, rgba(15, 23, 42, 0.6) 60%, rgba(15, 23, 42, 0.85) 100%);
            z-index: 1;
        }

        .gfg {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .conatiner:hover .gfg {
            transform: scale(1.03);
        }

        /* Section Label */
        .section-label {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--primary-color);
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-label i {
            font-size: 1rem;
        }

        .section-heading {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0;
        }

        /* Cards */
        .glass-card {
            background: var(--card-bg);
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: var(--card-shadow);
            padding: 28px;
            margin-bottom: 16px;
            transition: box-shadow 0.3s ease, transform 0.3s ease;
        }

        .glass-card:hover {
            box-shadow: var(--card-hover-shadow);
            transform: translateY(-2px);
        }

        /* Main heading card */
        .heading-card {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
            border: none;
            padding: 48px 40px;
            text-align: center;
        }

        .heading-card .heading_main_training h2 {
            font-size: 2rem;
            font-weight: 800;
            color: #ffffff;
            -webkit-text-fill-color: #ffffff;
            background: none;
            margin-bottom: 12px;
            letter-spacing: -0.02em;
        }

        .heading-card .heading_main_training h2:after {
            width: 60px;
            height: 3px;
            background: #38bdf8;
        }

        .heading-card .lead {
            color: #94a3b8;
            font-size: 1rem;
            margin-bottom: 0;
        }

        /* Main heading */
        .heading_main_training h2 {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
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
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            border-radius: 2px;
        }

        .section {
            padding: 50px 0;
        }

        .margin-top_50 {
            margin-top: -60px;
            position: relative;
            z-index: 2;
        }

        /* List items with chevrons */
        .list-check-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 0.92rem;
            color: var(--text-dark);
        }

        .list-check-item i {
            color: var(--primary-color);
            margin-top: 3px;
            flex-shrink: 0;
            font-size: 0.8rem;
        }

        /* Info alert */
        .info-strip {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            font-size: 0.88rem;
            color: #1e40af;
            margin-top: 16px;
        }

        .info-strip i {
            color: #2563eb;
            font-size: 1rem;
            flex-shrink: 0;
        }

        /* Icon boxes */
        .icon-box {
            min-width: 36px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #eff6ff !important;
            border: 1px solid #dbeafe;
            border-radius: 10px !important;
            box-shadow: none;
        }

        .icon-box i {
            font-size: 0.85rem;
            color: var(--primary-color) !important;
        }

        .icon-box.p-3 {
            width: 48px;
            height: 48px;
            min-width: 48px;
        }

        .icon-box.p-3 i {
            font-size: 1.2rem;
        }

        .icon-box.p-4 {
            width: 60px;
            height: 60px;
            min-width: 60px;
            border-radius: 16px !important;
        }

        .icon-box.p-4 i {
            font-size: 1.5rem;
        }

        .icon-box.bg-white {
            background: rgba(255,255,255,0.15) !important;
            border-color: rgba(255,255,255,0.25);
        }

        .icon-box.bg-white i {
            color: #ffffff !important;
        }

        /* Duration badge */
        .duration-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            background: linear-gradient(135deg, #1565C0, #1976D2);
            color: #ffffff;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.92rem;
            box-shadow: 0 2px 10px rgba(21, 101, 192, 0.3);
        }

        .duration-time {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--text-muted);
            font-size: 0.88rem;
            padding: 6px 14px;
            background: #f1f5f9;
            border-radius: 50px;
        }

        .duration-note {
            font-size: 0.85rem;
            color: var(--text-muted);
            font-style: italic;
            margin-top: 12px;
            padding: 10px 14px;
            background: #f8fafc;
            border-radius: 10px;
            border-left: 3px solid var(--primary-color);
        }

        /* Learning outcome cards */
        .outcome-item {
            padding: 16px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 12px;
            transition: border-color 0.2s ease;
        }

        .outcome-item:hover {
            border-color: #93c5fd;
        }

        .outcome-item h6 {
            font-size: 0.92rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 4px;
        }

        .outcome-item p {
            font-size: 0.82rem;
            color: var(--text-muted);
            margin: 0;
        }

        /* Module items */
        .module-item {
            padding: 14px 16px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            transition: border-color 0.2s ease;
        }

        .module-item:hover {
            border-color: #93c5fd;
        }

        .module-item h6 {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 3px;
        }

        .module-item p {
            font-size: 0.82rem;
            color: var(--text-muted);
            margin: 0;
        }

        /* How to Apply section */
        .apply-card {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
            border: none;
            color: #ffffff;
        }

        .apply-card h4, .apply-card h5, .apply-card p, .apply-card span {
            color: #e2e8f0;
        }

        .apply-card h5 {
            color: #ffffff;
            font-weight: 600;
        }

        .apply-card .text-muted {
            color: #94a3b8 !important;
        }

        .apply-card a.text-primary {
            color: #38bdf8 !important;
        }

        .apply-card .icon-box {
            background: rgba(56, 189, 248, 0.15) !important;
            border-color: rgba(56, 189, 248, 0.25);
        }

        .apply-card .icon-box i {
            color: #38bdf8 !important;
        }

        .apply-card .icon-box.bg-white {
            background: rgba(255,255,255,0.1) !important;
            border-color: rgba(255,255,255,0.2);
        }

        .apply-card .icon-box.bg-white i {
            color: #ffffff !important;
        }

        .apply-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 32px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
            transition: all 0.3s ease;
        }

        .apply-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.5);
            color: #ffffff;
        }

        /* Success check list */
        .benefit-list li {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 0.9rem;
            color: var(--text-dark);
        }

        .benefit-list li i {
            color: #16a34a;
            font-size: 0.8rem;
        }

        .section { padding: 50px 0; }
        .layout_padding { padding-top: 20px; }

        @media (max-width: 768px) {
            .conatiner { height: 40vh; min-height: 260px; }
            .heading-card { padding: 32px 24px; }
            .heading-card .heading_main_training h2 { font-size: 1.5rem; }
            .glass-card { padding: 20px; }
            .margin-top_50 { margin-top: -40px; }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        * {
            scrollbar-width: thin;
            scrollbar-color: var(--primary-color) #f1f5f9;
        }
    </style>
</head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>
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
                    <div class="glass-card heading-card animate-fadeInUp" style="animation-delay: 0.2s;">
                        <div class="heading_main_training text-center">
                            <h2>Environmental Management Systems (ISO 14001:2015)</h2>
                            <p class="lead">Enhancing Environmental Performance Through Effective Management Systems</p>
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
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <div class="section-label"><i class="fas fa-graduation-cap"></i>Training</div>
                            </div>
                            <div class="col-md-9">
                                <div class="section-heading">Internal Auditor Course: ISO 14001</div>
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
                                <div class="section-label"><i class="fas fa-gift"></i>Participants Receive</div>
                            </div>
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="list-check-item"><i class="fas fa-chevron-right"></i> EMS Slides including Exercises</div>
                                        <div class="list-check-item"><i class="fas fa-chevron-right"></i> ISO 14001 standard</div>
                                        <div class="list-check-item"><i class="fas fa-chevron-right"></i> Sample Environment Management System Manual</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="list-check-item"><i class="fas fa-chevron-right"></i> Case Studies</div>
                                        <div class="list-check-item"><i class="fas fa-chevron-right"></i> Workbook</div>
                                        <div class="list-check-item"><i class="fas fa-chevron-right"></i> Certificate of Internal Auditor for ISO 14001</div>
                                    </div>
                                </div>
                                <div class="info-strip">
                                    <i class="fas fa-info-circle"></i> A test will be conducted at the end of training and all successful candidates will be issued a certificate.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="glass-card animate-fadeInUp" style="animation-delay: 0.5s;">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <div class="section-label"><i class="far fa-clock"></i>Duration</div>
                            </div>
                            <div class="col-md-9">
                                <div class="d-flex flex-column">
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <span class="duration-badge"><i class="fas fa-calendar-day"></i> 2 Days</span>
                                        <span class="duration-time"><i class="far fa-calendar-alt"></i> 9:00 AM - 6:00 PM</span>
                                    </div>
                                    <p class="duration-note mb-0">The above duration training is customised as per organizational need. Please contact us in case of requirement.</p>
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
                                <div class="section-label"><i class="fas fa-bullseye"></i>Learning Outcomes</div>
                            </div>
                            <div class="col-md-9">
                                <h5 class="mb-3" style="font-size: 1rem; font-weight: 600;">By the end of the course you will be able to:</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="outcome-item">
                                            <div class="d-flex align-items-start">
                                                <div class="icon-box me-3 mt-1">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <div>
                                                    <h6>Develop Audit Systems</h6>
                                                    <p>Create effective environmental internal audit systems</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="outcome-item">
                                            <div class="d-flex align-items-start">
                                                <div class="icon-box me-3 mt-1">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <div>
                                                    <h6>Conduct Audits</h6>
                                                    <p>Perform environmental audits per ISO 14001 requirements</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="outcome-item">
                                            <div class="d-flex align-items-start">
                                                <div class="icon-box me-3 mt-1">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <div>
                                                    <h6>Maximize Value</h6>
                                                    <p>Get optimal value from internal audit processes</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="outcome-item">
                                            <div class="d-flex align-items-start">
                                                <div class="icon-box me-3 mt-1">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <div>
                                                    <h6>Drive Improvement</h6>
                                                    <p>Implement audits that ensure continuous improvement</p>
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
                    <div class="glass-card animate-fadeInUp" style="animation-delay: 0.7s;">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="section-label"><i class="fas fa-chalkboard-teacher"></i>Course Facilitators</div>
                            </div>
                            <div class="col-md-9">
                                <div class="d-flex align-items-start">
                                    <div class="icon-box p-3 me-3 flex-shrink-0">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0" style="font-size: 0.92rem; color: var(--text-muted); line-height: 1.7;">All course facilitators are highly qualified and experienced in both training and assessment of Environmental Management Systems. Our experts combine practical industry experience with exceptional training skills to deliver engaging and effective learning experiences.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="glass-card animate-fadeInUp" style="animation-delay: 0.8s;">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="section-label"><i class="fas fa-user-check"></i>Who Should Attend</div>
                            </div>
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="icon-box bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                                <i class="fas fa-bullseye text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1" style="font-weight: 600;">Course Purpose</h6>
                                                <p class="mb-0" style="font-size: 0.88rem; color: var(--text-muted);">This comprehensive two-day program equips participants with essential knowledge and skills for conducting internal environmental audits.</p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-start">
                                            <div class="icon-box me-3 mt-1">
                                                <i class="fas fa-user-tie"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1" style="font-weight: 600;">Ideal For</h6>
                                                <p class="mb-0" style="font-size: 0.88rem; color: var(--text-muted);">Quality Managers, Departmental Managers, and professionals responsible for environmental management systems.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start h-100">
                                            <div class="icon-box bg-primary bg-opacity-10 p-2 rounded-circle me-3">
                                                <i class="fas fa-users text-primary"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1" style="font-weight: 600;">Who Will Benefit</h6>
                                                <ul class="list-unstyled benefit-list">
                                                    <li><i class="fas fa-check-circle"></i> Quality Professionals</li>
                                                    <li><i class="fas fa-check-circle"></i> Environmental Managers</li>
                                                    <li><i class="fas fa-check-circle"></i> Technical Personnel</li>
                                                    <li><i class="fas fa-check-circle"></i> Administrative Staff</li>
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
                                <div class="section-label"><i class="fas fa-clipboard-check"></i>Eligibility</div>
                            </div>
                            <div class="col-md-9">
                                <div class="d-flex align-items-start">
                                    <div class="icon-box p-3 me-3 flex-shrink-0">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <div>
                                        <p class="mb-0" style="font-size: 0.92rem; color: var(--text-muted); line-height: 1.7;">Participants should have a basic understanding of Environmental Management System (EMS) principles and familiarity with ISO 14001 standards to fully benefit from this course.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="glass-card animate-fadeInUp" style="animation-delay: 1.0s;">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="section-label"><i class="fas fa-book-open"></i>Course Content</div>
                            </div>
                            <div class="col-md-9">
                                <div class="course-schedule mb-4">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="icon-box me-3">
                                            <i class="far fa-clock"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1" style="font-weight: 600; font-size: 0.92rem;">Course Schedule</h6>
                                            <p class="mb-0" style="font-size: 0.85rem; color: var(--text-muted);">9:00 AM - 6:00 PM | Maximum 10 Participants</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <h5 class="mb-3" style="font-size: 1rem; font-weight: 600;">Course Methodology:</h5>
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

                                <h5 class="mb-3" style="font-size: 1rem; font-weight: 600;">Course Modules:</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="module-item mb-3">
                                            <div class="d-flex align-items-start">
                                                <div class="icon-box me-3 mt-1">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <div>
                                                    <h6>Core Concepts</h6>
                                                    <p>Understanding EMS principles and ISO 14001 requirements</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="module-item mb-3">
                                            <div class="d-flex align-items-start">
                                                <div class="icon-box me-3 mt-1">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <div>
                                                    <h6>Audit Planning</h6>
                                                    <p>Developing effective audit programs and checklists</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="module-item mb-3">
                                            <div class="d-flex align-items-start">
                                                <div class="icon-box me-3 mt-1">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <div>
                                                    <h6>Audit Execution</h6>
                                                    <p>Conducting effective environmental audits</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="module-item mb-3">
                                            <div class="d-flex align-items-start">
                                                <div class="icon-box me-3 mt-1">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <div>
                                                    <h6>Reporting</h6>
                                                    <p>Documenting findings and preparing audit reports</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="module-item mb-3">
                                            <div class="d-flex align-items-start">
                                                <div class="icon-box me-3 mt-1">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <div>
                                                    <h6>Corrective Actions</h6>
                                                    <p>Implementing and verifying corrective measures</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="module-item">
                                            <div class="d-flex align-items-start">
                                                <div class="icon-box me-3 mt-1">
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <div>
                                                    <h6>Auditor Competencies</h6>
                                                    <p>Developing essential auditor skills and attributes</p>
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
                    <div class="glass-card apply-card animate-fadeInUp" style="animation-delay: 1.1s;">
                        <div class="row align-items-center">
                            <div class="col-lg-3 text-center text-lg-start mb-4 mb-lg-0">
                                <div class="icon-box p-4 d-inline-block">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                                <h4 class="mt-3" style="color: #38bdf8; font-weight: 700; letter-spacing: 0.04em;">HOW TO APPLY</h4>
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
                                                <p class="mb-0 text-muted">Email completed form to <a href="mailto:training1@aitf.co.in" class="text-primary">training@aitf.co.in</a></p>
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
