<?php
include './headSource.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - AITF Management Certification Limited</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-grey: #1e293b;
            --secondary-grey: #334155;
            --accent-blue: #3b82f6;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg-light: #f8fafc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Poppins', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-main);
            line-height: 1.6;
        }

        /* Premium Grey Header Section */
        .header-grey-section {
            padding: 180px 0 100px;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            text-align: center;
            position: relative;
            z-index: 1;
            color: #ffffff;
        }

        .header-grey-section h1 {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .header-grey-section p {
            font-size: 1.4rem;
            color: rgba(255, 255, 255, 0.9);
            max-width: 800px;
            margin: 0 auto;
            font-weight: 300;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Services Grid */
        .services {
            padding: 100px 0;
            margin-top: -60px;
            position: relative;
            z-index: 10;
        }

        .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }

        .service-card {
            background: #ffffff;
            padding: 50px 40px;
            border-radius: 20px;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.02);
        }

        .service-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 30px 60px rgba(0,0,0,0.1);
        }

        .service-card i {
            font-size: 3.5rem;
            margin-bottom: 25px;
            color: #3b82f6;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .service-card h3 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: #1e293b;
            font-weight: 700;
        }

        .service-card p {
            color: #64748b;
            font-size: 1.05rem;
            line-height: 1.8;
        }

        /* About Detailed Content */
        .about-detailed {
            padding: 100px 0;
            background-color: #ffffff;
        }

        .about-card {
            background: #f8fafc;
            padding: 60px;
            border-radius: 30px;
            margin-bottom: 40px;
            border-left: 8px solid #3b82f6;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        }

        .about-card h3 {
            font-size: 2.2rem;
            margin-bottom: 25px;
            color: #1e293b;
            font-weight: 800;
        }

        .about-card p {
            font-size: 1.15rem;
            margin-bottom: 25px;
            color: #475569;
            line-height: 1.9;
        }

        /* Values Grid */
        .values-section {
            padding: 100px 0;
            background: #f1f5f9;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
        }

        .value-box {
            background: #ffffff;
            padding: 45px 35px;
            border-radius: 24px;
            text-align: center;
            transition: transform 0.3s ease;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        .value-box:hover {
            transform: scale(1.05);
        }

        .value-box i {
            font-size: 3rem;
            margin-bottom: 25px;
            color: #3b82f6;
        }

        .value-box h4 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #1e293b;
            font-weight: 700;
        }

        .value-box p {
            color: #64748b;
            font-size: 1rem;
        }

        /* CTA */
        .cta-footer {
            padding: 100px 0;
            text-align: center;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: #ffffff;
        }

        .cta-footer h2 {
            font-size: 3rem;
            margin-bottom: 20px;
            font-weight: 800;
        }

        .cta-footer p {
            font-size: 1.3rem;
            margin-bottom: 40px;
            opacity: 0.9;
        }

        .btn-modern {
            display: inline-block;
            padding: 18px 45px;
            background: #3b82f6;
            color: #ffffff;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.3);
        }

        .btn-modern:hover {
            background: #2563eb;
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(59, 130, 246, 0.4);
            color: #ffffff;
        }

        @media (max-width: 768px) {
            .header-grey-section {
                padding: 140px 0 70px;
            }
            .header-grey-section h1 {
                font-size: 2.8rem;
            }
            .service-card {
                padding: 30px 20px;
            }
            .about-card {
                padding: 30px;
            }
        }
    </style>
</head>

<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <!-- Header -->
    <?php include './headerSource.php'; ?>

    <!-- Premium Grey Header Section -->
    <div class="header-grey-section">
        <div class="container">
            <h1>ABOUT AITF</h1>
            <p>Your trusted partner in management system certification</p>
        </div>
    </div>

    <!-- Services Grid -->
    <section class="services">
        <div class="container">
            <div class="service-grid">
                <div class="service-card">
                    <i class="fas fa-users-viewfinder"></i>
                    <h3>Who We Are</h3>
                    <p>AITF (Assessment & Institute for Technical Forum) is dedicated to providing high-quality assessments through qualified auditors to drive Management System.</p>
                </div>
                
                <div class="service-card">
                    <i class="fas fa-award"></i>
                    <h3>NABCB Accredited</h3>
                    <p>Accredited by NABCB, we maintain the highest standards of competence and impartiality, ensuring globally recognized certification services.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Detailed Section -->
    <section class="about-detailed">
        <div class="container">
            <div class="about-card">
                <h3>Our Methodology</h3>
                <p>We at AITF Management Certification Ltd. are committed to providing assessments through qualified auditors to improve the business performance of our customers with value-added audits of clients processes and management systems.</p>
                <p>The National Accreditation Board for Certification Bodies (NABCB) serves as a constituent board of the Quality Council of India (QCI), ensuring that our operations align with international benchmarks.</p>
            </div>
            
            <div class="about-card" style="border-left-color: #10b981;">
                <h3>Management System Benefits</h3>
                <p>Implementing a robust management system brings transparency, consistency, and a culture of continuous improvement to your organization. Benefits include reduced operational failure costs, structured documentation, and enhanced market credibility.</p>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="values-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 style="font-size: 2.5rem; font-weight: 800; color: #1e293b;">Our Core Values</h2>
            </div>
            <div class="values-grid">
                <div class="value-box">
                    <i class="fas fa-scale-balanced"></i>
                    <h4>Impartiality</h4>
                    <p>Impartiality is the cornerstone of our assessment process, ensuring fair and unbiased outcomes for every client.</p>
                </div>
                
                <div class="value-box">
                    <i class="fas fa-fingerprint"></i>
                    <h4>Independence</h4>
                    <p>AITF operates with full autonomy, taking complete responsibility for all certification decisions without external influence.</p>
                </div>
                
                <div class="value-box">
                    <i class="fas fa-user-shield"></i>
                    <h4>Confidentiality</h4>
                    <p>Your data is safe with us. We maintain strict confidentiality protocols to protect all sensitive client information.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Footer Section -->
    <section class="cta-footer">
        <div class="container">
            <h2>Ready to Begin?</h2>
            <p>Connect with our experts to start your certification journey today.</p>
            <a href="contactUS.php" class="btn-modern">Contact Us Now</a>
        </div>
    </section>

    <!-- Footer -->
    <?php include './footerSource.php'; ?>

    <script>
        // Reveal animation on scroll
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth reveal effect can be added here
        });
    </script>
</body>
</html>
