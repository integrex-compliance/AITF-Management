<!DOCTYPE html>
<html lang="en">

<head>
    <?php include './headSource.php'; ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        :root {
            --primary: #3b82f6;
            /* Premium blue */
            --primary-light: #60a5fa;
            --primary-dark: #2563eb;
            --secondary: #1d4ed8;
            /* Darker blue for contrast */
            --accent: #60a5fa;
            /* Lighter blue for accents */
            --dark: #0f172a;
            /* Dark blue background */
            --darker: #020617;
            /* Even darker for contrast */
            --light: #f8fafc;
            /* Light text */
            --glass: rgba(30, 58, 138, 0.2);
            --glass-border: rgba(99, 102, 241, 0.3);
            --text: #1e293b;
            /* Dark blue-gray text */
            --text-secondary: #475569;
            /* Slightly darker for secondary text */
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            --card-shadow: 0 10px 30px -15px rgba(2, 12, 27, 0.1);
            --card-hover-shadow: 0 20px 50px -10px rgba(2, 12, 27, 0.2);
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text);
            line-height: 1.8;
            overflow-x: hidden;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: subpixel-antialiased;
            -moz-osx-font-smoothing: auto;
            font-size: 1.05rem;
        }

        .policy-hero {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            color: white;
            padding: 120px 0 100px;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-radius: 0 0 30px 30px;
            margin-bottom: 60px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .policy-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgcGF0dGVyblRyYW5zZm9ybT0icm90YXRlKDQ1KSI+PHJlY3Qgd2lkdGg9IjUwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJyZ2JhKDI1NSwyNTUsMjU1LDAuMDMpIi8+PC9wYXR0ZXJuPjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJ1cmwoI3BhdHRlcm4pIi8+PC9zdmc+');
            opacity: 0.1;
            z-index: 1;
        }

        .policy-hero .container {
            position: relative;
            z-index: 2;
            max-width: 900px;
        }

        .policy-hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            background: linear-gradient(90deg, #fff, #e0f2fe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
            position: relative;
            z-index: 2;
            letter-spacing: -0.5px;
            margin-top: 1rem;
        }

        .policy-hero h1 span {
            color: #fff;
            position: relative;
            display: inline-block;
            padding: 0 15px;
            z-index: 1;
            margin: 0 5px;
        }

        .policy-hero h1 span::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.15);
            z-index: -1;
            border-radius: 6px;
            transform: none;
        }

        .policy-hero p.lead {
            font-size: 1.3rem;
            max-width: 700px;
            margin: 0 auto 3rem;
            color: #ffffff;
            position: relative;
            z-index: 2;
            line-height: 1.8;
            font-weight: 400;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            padding: 20px 25px;
            background: rgba(30, 58, 138, 0.3);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.15);
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        /* Add some decorative elements */
        .policy-hero::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 0;
            right: 0;
            height: 100px;
            background: #f8f9ff;
            transform: skewY(-2deg);
            z-index: 1;
        }

        .policy-section {
            padding: 80px 0;
            position: relative;
            background: white;
            border-radius: 20px;
            margin: 30px auto;
            max-width: 1200px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.03);
            transition: var(--transition);
            overflow: hidden;
        }

        .policy-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .policy-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgcGF0dGVyblRyYW5zZm9ybT0icm90YXRlKDQ1KSI+PHJlY3Qgd2lkdGg9IjUwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJyZ2JhKDU5LDEzMCwyNDYsMC4wMikiLz48L3BhdHRlcm4+PC9kZWZzPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InVybCgjcGF0dGVybikiLz48L3N2Zz4=');
            opacity: 0.3;
            z-index: 0;
        }

        .policy-section::before {
            content: '';
            position: absolute;
            top: -50px;
            left: 0;
            right: 0;
            height: 100px;
            background: var(--light-bg);
            transform: skewY(-2deg);
            z-index: -1;
        }

        .policy-card {
            background: white !important;
            border-radius: 16px;
            padding: 35px 30px;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            height: 100%;
            border: 1px solid rgba(59, 130, 246, 0.1);
            position: relative;
            overflow: hidden;
            transform: translateZ(0);
            -webkit-transform: translateZ(0);
            -webkit-backface-visibility: hidden;
            -webkit-font-smoothing: subpixel-antialiased;
        }

        .policy-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0) 100%);
            z-index: 1;
            opacity: 0;
            transition: var(--transition);
        }

        .policy-card:hover::before {
            opacity: 1;
        }

        .policy-card:hover {
            transform: translateY(-10px) scale(1.01);
            box-shadow: var(--card-hover-shadow);
        }

        .policy-card h3 {
            color: var(--primary);
            margin-bottom: 1.8rem;
            font-weight: 700;
            font-size: 1.8rem;
            position: relative;
            display: inline-block;
            line-height: 1.3;
            letter-spacing: -0.3px;
        }

        .policy-card h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -10px;
            width: 50px;
            height: 3px;
            background: var(--primary);
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .policy-card:hover h3::after {
            width: 80px;
        }

        .policy-card p {
            color: var(--text-secondary) !important;
            margin-bottom: 1.5rem;
            font-size: 1.05rem !important;
            line-height: 1.8 !important;
            font-weight: 400 !important;
            letter-spacing: 0.01em;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: subpixel-antialiased;
            text-shadow: 0 0 0.5px rgba(0, 0, 0, 0.2);
        }

        .policy-feature {
            display: flex;
            align-items: flex-start;
            margin-bottom: 25px;
            background: rgba(59, 130, 246, 0.03);
            padding: 22px 25px;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px dashed rgba(59, 130, 246, 0.1);
        }

        .policy-feature:hover {
            background: rgba(67, 97, 238, 0.08);
            transform: translateX(5px);
        }

        .feature-icon {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-right: 20px;
            flex-shrink: 0;
            box-shadow: 0 10px 20px -5px rgba(67, 97, 238, 0.3);
            transition: var(--transition);
        }

        .policy-feature:hover .feature-icon {
            transform: rotate(5deg) scale(1.1);
            box-shadow: 0 15px 30px -5px rgba(67, 97, 238, 0.4);
        }

        .feature-content {
            flex: 1;
        }

        .feature-content h4 {
            color: var(--dark-text);
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .feature-content p {
            margin-bottom: 0;
            font-size: 0.95rem;
            color: var(--light-text);
        }

        .policy-image {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.1);
            height: 100%;
            transition: var(--transition);
            position: relative;
        }

        .policy-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(67, 97, 238, 0.3), rgba(63, 55, 201, 0.1));
            opacity: 0;
            transition: var(--transition);
            z-index: 1;
        }

        .policy-image:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px -10px rgba(67, 97, 238, 0.2);
        }

        .policy-image:hover::before {
            opacity: 1;
        }

        .policy-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s cubic-bezier(0.2, 0.96, 0.34, 0.99);
        }

        .policy-image:hover img {
            transform: scale(1.05);
        }

        .commitment-section {
            padding: 100px 0;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            position: relative;
            overflow: hidden;
        }

        .commitment-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgcGF0dGVyblRyYW5zZm9ybT0icm90YXRlKDQ1KSI+PHJlY3Qgd2lkdGg9IjUwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJyZ2JhKDU5LDEzMCwyNDYsMC4wMikiLz48L3BhdHRlcm4+PC9kZWZzPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InVybCgjcGF0dGVybikiLz48L3N2Zz4=');
            opacity: 0.3;
            z-index: 0;
        }

        .commitment-section .container {
            position: relative;
            z-index: 2;
        }

        .commitment-item {
            background: white !important;
            padding: 35px 25px;
            border-radius: 16px;
            text-align: center;
            height: 100%;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(59, 130, 246, 0.1);
            position: relative;
            z-index: 1;
            overflow: hidden;
            transform: translateZ(0);
            -webkit-transform: translateZ(0);
            -webkit-backface-visibility: hidden;
            -webkit-font-smoothing: subpixel-antialiased;
        }

        .commitment-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0) 100%);
            z-index: -1;
            opacity: 0;
            transition: var(--transition);
        }

        .commitment-item:hover::before {
            opacity: 1;
        }

        .commitment-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border-color: rgba(59, 130, 246, 0.2);
        }

        .commitment-icon {
            width: 90px;
            height: 90px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.2rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 1;
            box-shadow: 0 15px 30px rgba(59, 130, 246, 0.2);
        }

        .commitment-icon::after {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 30px;
            z-index: -1;
            opacity: 0.5;
            transition: var(--transition);
        }

        .commitment-item:hover .commitment-icon {
            transform: translateY(-10px) scale(1.1);
            box-shadow: 0 15px 30px rgba(59, 130, 246, 0.3);
        }

        .commitment-item:hover .commitment-icon::after {
            transform: scale(1.1);
            opacity: 0.8;
        }

        .commitment-item h4 {
            color: var(--dark);
            margin: 0 0 20px 0;
            font-weight: 700;
            font-size: 1.5rem;
            position: relative;
            display: inline-block;
            padding-bottom: 12px;
            line-height: 1.4;
        }

        .commitment-item h4::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 2px;
            background: rgba(255, 255, 255, 0.5);
            transition: all 0.3s ease;
        }

        .commitment-item:hover h4::after {
            width: 60px;
            background: #fff;
        }

        .commitment-item p {
            color: var(--text-secondary) !important;
            margin: 0 0 1rem 0;
            font-size: 1.05rem !important;
            line-height: 1.8 !important;
            font-weight: 400 !important;
            letter-spacing: 0.01em;
            padding: 0 5px;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: subpixel-antialiased;
            text-shadow: 0 0 0.5px rgba(0, 0, 0, 0.2);
        }

        /* Animations */
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

        /* Responsive Styles */
        @media (max-width: 1199.98px) {
            .policy-hero h1 {
                font-size: 3rem;
            }

            .policy-section {
                padding: 60px 0;
            }
        }

        @media (max-width: 991.98px) {
            .policy-hero {
                padding: 100px 0;
                clip-path: none;
            }

            .policy-hero h1 {
                font-size: 2.8rem;
            }

            .policy-hero p.lead {
                font-size: 1.2rem;
                padding: 15px 20px;
                margin: 15px 20px 30px;
            }

            .policy-card {
                padding: 40px 30px;
            }
        }

        @media (max-width: 767.98px) {
            .policy-hero {
                padding: 80px 0 60px;
                margin-bottom: 40px;
                border-radius: 0 0 20px 20px;
            }

            .policy-hero h1 {
                font-size: 2.2rem;
                padding: 0 20px;
                margin-bottom: 1.2rem;
            }

            .policy-hero p.lead {
                font-size: 1.15rem;
                padding: 0 15px;
                margin-bottom: 2rem;
            }

            .policy-hero h1 span::after {
                height: 12px;
                bottom: 3px;
            }

            .policy-hero p.lead {
                font-size: 1.1rem;
                padding: 12px 15px;
                margin: 10px 15px 20px;
                padding: 0 30px;
            }

            .policy-section {
                padding: 70px 0;
            }

            .policy-card {
                padding: 30px 25px;
                margin-bottom: 30px;
            }

            .policy-feature {
                flex-direction: column;
                text-align: center;
                padding: 25px 20px;
            }

            .feature-icon {
                margin: 0 auto 15px;
            }

            .commitment-section {
                padding: 80px 0;
                clip-path: polygon(0 5%, 100% 0, 100% 100%, 0% 100%);
            }

            .commitment-item {
                padding: 0 15px;
                margin-bottom: 30px;
            }

            .commitment-icon {
                font-size: 2.8rem;
                margin-bottom: 20px;
            }
        }

        @media (max-width: 576px) {
            .policy-hero {
                padding: 80px 0 60px;
            }

            .policy-hero h1 {
                font-size: 1.8rem;
                line-height: 1.3;
            }

            .policy-hero h1 span::after {
                height: 10px;
                bottom: 2px;
            }

            .policy-card h3 {
                font-size: 1.5rem;
            }

            .commitment-section {
                clip-path: none;
            }
        }
    </style>
</head>

<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>

    <!-- Hero Section -->
    <section class="policy-hero">
        <div class="container">
            <h1>ORGANIZATION POLICY</h1>
            <p class="lead" style="max-width: 800px; margin: 0 auto; font-size: 1.2rem; opacity: 0.9;">
                Our commitment to excellence, integrity, and continuous improvement in quality management systems
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="policy-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="policy-card">
                        <h3>Our Commitment to Excellence</h3>
                        <p>AITF-MCL is dedicated to providing exceptional assessment services for QMS certification in
                            strict compliance with ISO/IEC 17021 standards and accreditation board guidelines. We uphold
                            the highest levels of professionalism and ethical conduct in all our operations.</p>

                        <div class="policy-feature">
                            <div class="feature-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Integrity & Impartiality</h4>
                                <p>We maintain strict independence and impartiality in all our assessment activities,
                                    ensuring unbiased and objective evaluations for all clients.</p>
                            </div>
                        </div>

                        <div class="policy-feature">
                            <div class="feature-icon">
                                <i class="fas fa-balance-scale"></i>
                            </div>
                            <div class="feature-content">
                                <h4>Regulatory Compliance</h4>
                                <p>We adhere to all applicable legal requirements and industry regulations, ensuring our
                                    services meet the highest standards of compliance.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="policy-image">
                        <img src="aitfImages/policy-iso-01.jpg" alt="Quality Management System" class="img-fluid">
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-lg-4 mb-4">
                    <div class="policy-card">
                        <h3>Conflict of Interest</h3>
                        <p>We are committed to identifying, analyzing, and managing any potential conflicts of interest
                            to ensure the integrity of our certification process and maintain stakeholder confidence.
                        </p>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="policy-card">
                        <h3>Continuous Improvement</h3>
                        <p>We continuously enhance our management systems and processes to deliver superior
                            certification services and exceed customer expectations.</p>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="policy-card">
                        <h3>Professional Development</h3>
                        <p>We invest in regular training and development for our auditors, technical experts, and
                            management staff to ensure they remain at the forefront of industry standards and best
                            practices.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Commitment Section -->
    <section class="commitment-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4 mb-4 mb-md-0">
                    <div class="commitment-item">
                        <!-- <div class="commitment-icon">
                            <i class="fas fa-handshake"></i>
                        </div> -->

                    </div>
                </div>

                <div class="col-md-4 mb-4 mb-md-0">
                    <div class="commitment-item">
                        <div class="commitment-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4>Operational Excellence</h4>
                        <p>Continuously improving our processes and systems to deliver efficient, effective, and
                            value-adding certification services.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="commitment-item">
                        <div class="commitment-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>Stakeholder Trust</h4>
                        <p>Building and maintaining trust through transparency, integrity, and consistent delivery of
                            high-quality services.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include './footerSource.php'; ?>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Add smooth scrolling to all links
        $("a").on('click', function (event) {
            if (this.hash !== "") {
                event.preventDefault();
                var hash = this.hash;
                $('html, body').animate({
                    scrollTop: $(hash).offset().top
                }, 800, function () {
                    window.location.hash = hash;
                });
            }
        });
    </script>
</body>

</html>