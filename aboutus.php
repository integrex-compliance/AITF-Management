<?php
include './headSource.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - AITF Management Certification Limited</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            overflow-x: hidden;
            min-height: 100vh;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .hero {
            padding: 100px 0;
            text-align: center;
        }

        .hero h1 {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 20px;
            color: #ffffff;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .hero p {
            font-size: 1.5rem;
            margin-bottom: 40px;
            color: #f0f0f0;
        }

        .services {
            padding: 80px 0;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .services h2 {
            text-align: center;
            font-size: 3rem;
            margin-bottom: 50px;
            color: #ffffff;
        }

        .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .service-card {
            background: rgba(255, 255, 255, 0.15);
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-10px);
        }

        .service-card i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #ffd700;
        }

        .service-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #ffffff;
        }

        .service-card p {
            color: #f0f0f0;
            line-height: 1.6;
        }

        .about-section {
            padding: 80px 0;
        }

        .about-section h2 {
            text-align: center;
            font-size: 3rem;
            margin-bottom: 50px;
            color: #ffffff;
        }

        .about-content {
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 40px;
        }

        .about-content h3 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: #ffd700;
        }

        .about-content p {
            font-size: 1.1rem;
            margin-bottom: 20px;
            color: #f0f0f0;
        }

        .values {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .value-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            text-align: center;
        }

        .value-item i {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: #ffd700;
        }

        .value-item h4 {
            font-size: 1.3rem;
            margin-bottom: 15px;
            color: #ffffff;
        }

        .value-item p {
            color: #f0f0f0;
        }

        .cta {
            text-align: center;
            padding: 60px 0;
        }

        .cta h2 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: #ffffff;
        }

        .cta p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            color: #f0f0f0;
        }

        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: #ffd700;
            color: #333;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: #ffed4e;
            transform: translateY(-3px);
        }

        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1.2rem;
            }
            
            .services h2, .about-section h2 {
                font-size: 2rem;
            }
        }
    </style>
    <style>
        /* Remove any bottom margin from the about section */
        #about {
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }
        
        /* Remove top margin from the methodology section */
        .bg-light {
            margin-top: 0 !important;
            padding-top: 10px !important;
        }
        
        /* Adjust content card spacing */
        .content-card {
            margin: 0 !important;
            padding: 20px !important;
        }
        
        /* Make sure no other elements are adding space */
        .row, .container, .col-md-12 {
            margin: 0 !important;
            padding: 0 !important;
        }
    </style>
</head>
<style>
:root {
    --primary-color: #0f172a;
    --secondary-color: #1e293b;
    --accent-color: #3b82f6;
    --accent-hover: #2563eb;
    --success-color: #22c55e;
    --warning-color: #f59e0b;
    --text-light: #e2e8f0;
    --text-muted: #94a3b8;
    --text-secondary: #cbd5e1;
    --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    --hero-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 40%, #2563eb 80%, #3b82f6 100%);
    --card-bg: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.95));
    --transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    line-height: 1.6;
    color: var(--text-secondary);
    overflow-x: hidden;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
}

/* Simple Static Background */
.animated-bg {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #2563eb 100%);
    z-index: -2;
}

/* Removed Particles - Causing Performance Issues */

/* Removed Geometric Shapes - Causing Performance Issues */

.about-hero {
    background: var(--hero-gradient);
    color: white;
    padding: 80px 0 30px !important;
    position: relative;
    overflow: hidden;
    min-height: auto !important;
    display: flex;
    align-items: center;
    margin: 0 !important;
}

.hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    background: rgba(255, 255, 255, 0.05);
    padding: 50px 40px;
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    max-width: 800px;
    margin: 0 auto;
}

.about-hero h1 {
    font-size: 4rem;
    font-weight: 700;
    margin-bottom: 10px;
    background: linear-gradient(135deg, #ffffff, #e0f2fe);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: -2px;
}

.about-hero h1::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 2px;
    background: linear-gradient(90deg, transparent, #60a5fa, transparent);
    border-radius: 1px;
}

.about-hero p {
    font-size: 1.3rem;
    color: #e2e8f0;
    max-width: 800px;
    margin: 0 auto 15px;
    line-height: 1.5;
    font-weight: 300;
}

.hero-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-hero {
    padding: 15px 35px;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 50px;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    border: 2px solid transparent;
}

.btn-primary {
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    color: white;
    box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
    background: linear-gradient(135deg, #1d4ed8, #2563eb);
}

.btn-hero i {
    font-size: 1.1rem;
}

.btn-outline {
    background: transparent;
    color: white;
    border-color: rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(10px);
}

.btn-outline:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.5);
    transform: translateY(-3px);
}

.section {
    padding: 0 !important;
    position: relative;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    margin: 0 !important;
}

.section:nth-child(even) {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
}

.section-title {
    font-size: 3rem;
    font-weight: 800;
    color: #e2e8f0;
    margin-bottom: 15px;
    position: relative;
    padding-bottom: 15px;
    text-align: center;
    letter-spacing: -1px;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 5px;
    background: linear-gradient(90deg, #2563eb, #3b82f6, #60a5fa);
    border-radius: 3px;
    box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
}

.section-subtitle {
    font-size: 1.2rem;
    color: #94a3b8;
    margin-bottom: 25px;
    max-width: 800px;
    text-align: center;
    margin-left: auto;
    margin-right: auto;
    font-weight: 400;
    line-height: 1.8;
}

.content-card {
    background: rgba(30, 41, 59, 0.8);
    border-radius: 16px;
    padding: 30px !important;
    position: relative;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1;
    overflow: hidden;
    backdrop-filter: blur(20px);
    color: #e2e8f0;
}

.content-card p {
    color: #e2e8f0;
    line-height: 1.8;
    font-size: 1.1rem;
    margin-bottom: 20px;
    text-align: right;
    font-weight: 300;
    letter-spacing: 0.3px;
}

.content-card h3 {
    color: #60a5fa;
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 20px;
    position: relative;
    padding-bottom: 10px;
}

.content-card h3::after {
    content: '';
    position: absolute;
    bottom: 0;
    right: 0;
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, transparent, #60a5fa);
    border-radius: 3px;
}

.content-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #2563eb, #3b82f6, #6366f1, #8b5cf6);
    background-size: 300% 100%;
    transform: scaleX(0);
    transition: transform 0.4s ease;
    animation: gradientMove 3s ease-in-out infinite;
}

@keyframes gradientMove {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.content-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
    border-color: rgba(96, 165, 250, 0.3);
}

.content-card:hover::before {
    transform: scaleX(1);
}

.content-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 50% 50%, rgba(59, 130, 246, 0.03) 0%, transparent 70%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.content-card:hover::after {
    opacity: 1;
}

.feature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 40px;
    margin: 60px 0;
}

.feature-item {
    background: linear-gradient(145deg, rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.9));
    padding: 45px;
    border-radius: 24px;
    border: 2px solid rgba(59, 130, 246, 0.3);
    transition: var(--transition);
    position: relative;
    overflow: hidden;
    box-shadow: 
        0 10px 30px rgba(0, 0, 0, 0.3),
        0 0 60px rgba(59, 130, 246, 0.1);
    color: #e2e8f0;
}

.feature-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.08), rgba(59, 130, 246, 0.08), rgba(99, 102, 241, 0.08));
    opacity: 0;
    transition: opacity 0.4s ease;
}

.feature-item:hover {
    transform: translateY(-20px) scale(1.03);
    box-shadow: 
        0 30px 80px rgba(0, 0, 0, 0.5),
        0 0 100px rgba(59, 130, 246, 0.3);
    border-color: #60a5fa;
    background: linear-gradient(145deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.95));
}

.feature-item:hover::before {
    opacity: 1;
}

.feature-icon {
    width: 90px;
    height: 90px;
    background: linear-gradient(135deg, #2563eb, #3b82f6, #6366f1);
    border-radius: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 36px;
    margin-bottom: 30px;
    transition: var(--transition);
    box-shadow: 0 15px 40px rgba(37, 99, 235, 0.4);
    position: relative;
    overflow: hidden;
}

.feature-icon::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transform: rotate(45deg);
    transition: transform 0.6s;
}

.feature-item:hover .feature-icon {
    transform: rotateY(180deg) scale(1.1);
    background: linear-gradient(135deg, #1d4ed8, #2563eb, #4f46e5);
}

.feature-item:hover .feature-icon::before {
    transform: rotate(45deg) translate(100%, 100%);
}

.feature-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #f1f5f9;
    margin-bottom: 15px;
    position: relative;
    z-index: 2;
}

.feature-description {
    color: #cbd5e1;
    line-height: 1.8;
    position: relative;
    z-index: 2;
    font-size: 1rem;
}

.benefit-list {
    list-style: none;
    padding: 0;
    margin: 40px 0;
}

.benefit-list li {
    padding: 20px;
    border-bottom: 1px solid rgba(59, 130, 246, 0.2);
    display: flex;
    align-items: flex-start;
    transition: var(--transition);
    border-radius: 12px;
    margin-bottom: 10px;
    background: rgba(15, 23, 42, 0.3);
    color: #e2e8f0;
    font-size: 1.05rem;
    line-height: 1.7;
}

.benefit-list li:hover {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.15), rgba(22, 163, 74, 0.15));
    transform: translateX(10px);
    border-color: rgba(34, 197, 94, 0.3);
}

.benefit-list li:last-child {
    border-bottom: none;
}

.benefit-list li::before {
    content: '✓';
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
    border-radius: 50%;
    margin-right: 20px;
    font-weight: bold;
    flex-shrink: 0;
    box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
}

.process-timeline {
    position: relative;
    padding: 20px 0;
}

#about {
    padding: 20px 0 0 0 !important;
    margin-bottom: 0 !important;
    position: relative;
    opacity: 0;
    transform: translateX(-50px);
    animation: slideInLeft 0.6s ease-out forwards;
}

.process-item:nth-child(1) { animation-delay: 0.1s; }
.process-item:nth-child(2) { animation-delay: 0.2s; }
.process-item:nth-child(3) { animation-delay: 0.3s; }
.process-item:nth-child(4) { animation-delay: 0.4s; }
.process-item:nth-child(5) { animation-delay: 0.5s; }
.process-item:nth-child(6) { animation-delay: 0.6s; }
.process-item:nth-child(7) { animation-delay: 0.7s; }

@keyframes slideInLeft {
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.process-number {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #2563eb, #3b82f6);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.5rem;
    margin-right: 40px;
    flex-shrink: 0;
    box-shadow: 0 10px 30px rgba(37, 99, 235, 0.3);
    transition: var(--transition);
}

.process-item:hover .process-number {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 15px 40px rgba(37, 99, 235, 0.4);
}

.process-content {
    flex: 1;
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.9));
    padding: 30px;
    border-radius: 16px;
    box-shadow: 
        0 10px 30px rgba(0, 0, 0, 0.3),
        0 0 60px rgba(59, 130, 246, 0.1);
    border: 2px solid rgba(59, 130, 246, 0.3);
    transition: var(--transition);
    color: #e2e8f0;
}

.process-item:hover .process-content {
    transform: translateY(-5px);
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.1);
}

.process-title {
    font-size: 1.6rem;
    font-weight: 700;
    color: #f1f5f9;
    margin-bottom: 15px;
}

.process-description {
    color: #cbd5e1;
    line-height: 1.8;
    font-size: 1rem;
}

.contact-info {
    background: linear-gradient(135deg, #0f172a, #1e293b);
    color: white;
    padding: 60px;
    border-radius: 24px;
    margin-top: 60px;
    position: relative;
    overflow: hidden;
}

.contact-info::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 30% 30%, rgba(59, 130, 246, 0.2) 0%, transparent 50%);
}

.contact-info h3 {
    color: white;
    margin-bottom: 30px;
    font-size: 2rem;
    font-weight: 700;
    position: relative;
    z-index: 2;
}

.contact-info p {
    color: #cbd5e1;
    margin-bottom: 15px;
    position: relative;
    z-index: 2;
    font-size: 1.1rem;
    line-height: 1.8;
}

.contact-info a {
    color: #60a5fa;
    text-decoration: none;
    font-weight: 500;
    transition: var(--transition);
}

.contact-info a:hover {
    color: #93c5fd;
    text-decoration: underline;
    transform: translateX(5px);
    display: inline-block;
}

/* Scroll animations */
.scroll-animate {
    opacity: 0;
    transform: translateY(50px);
    transition: all 0.8s ease;
}

.scroll-animate.visible {
    opacity: 1;
    transform: translateY(0);
}

/* Responsive Design */
@media (max-width: 1200px) {
    .about-hero h1 {
        font-size: 3.5rem;
    }
    
    .section-title {
        font-size: 2.5rem;
    }
    
    .feature-grid {
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
    }
}

@media (max-width: 992px) {
    .about-hero {
        padding: 100px 0 60px;
        min-height: auto;
    }
    
    .about-hero h1 {
        font-size: 3rem;
    }
    
    .about-hero p {
        font-size: 1.2rem;
    }
    
    .section {
        padding: 80px 0;
    }
    
    .section-title {
        font-size: 2.2rem;
    }
    
    .content-card {
        padding: 40px;
    }
    
    .feature-item {
        padding: 30px;
    }
}

@media (max-width: 768px) {
    .about-hero h1 {
        font-size: 2.5rem;
    }
    
    .about-hero p {
        font-size: 1.1rem;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .content-card {
        padding: 30px 25px;
    }
    
    .process-item {
        flex-direction: column;
    }
    
    .process-number {
        margin-right: 0;
        margin-bottom: 20px;
        width: 60px;
        height: 60px;
        font-size: 1.2rem;
    }
    
    .feature-grid {
        grid-template-columns: 1fr;
        gap: 25px;
    }
    
    .contact-info {
        padding: 40px 30px;
    }
    
    .hero-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .btn-hero {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
}

@media (max-width: 576px) {
    .about-hero h1 {
        font-size: 2rem;
    }
    
    .section-title {
        font-size: 1.8rem;
    }
    
    .content-card {
        padding: 25px 20px;
    }
    
    .contact-info {
        padding: 30px 20px;
    }
}
</style>

<body>
    <!-- Header -->
    <?php include './headerSource.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>AITF CERTIFICATION</h1>
            <p>Your trusted partner in management system certification since 2011</p>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services">
        <div class="container">
            <h2>Our Services</h2>
            <div class="service-grid">
                <div class="service-card">
                    <i class="fas fa-certificate"></i>
                    <h3>Who We Are</h3>
                    <p>AITF (Assessment & Institute for Technical Forum) is committed to providing assessments through qualified auditors to improve business performance.</p>
                </div>
                
                <div class="service-card">
                    <i class="fas fa-shield-alt"></i>
                    <h3>NABCB Accredited</h3>
                    <p>NABCB assesses the competence and impartiality of AITF based on international standards, ensuring reliable certification services.</p>
                </div>
                
                            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <h2>About AITF</h2>
            
            <div class="about-content">
                <h3>Our Methodology</h3>
                <p>We at AITF Management Certification Ltd. are committed to providing assessments through qualified auditors to improve the business performance of our customers with value-added audits of clients' processes/systems.</p>
                <p>The National Accreditation Board for Certification Bodies (NABCB) is a key entity in India's quality infrastructure, serving as a constituent board of the Quality Council of India (QCI).</p>
            </div>
            
            <div class="about-content">
                <h3>Management System Benefits</h3>
                <p>After implementing our management systems, organizations receive numerous benefits including structured documentation, consistency in quality, transparency in duties, reduced failure costs, and continuous improvement opportunities.</p>
            </div>
            
            <div class="values">
                <div class="value-item">
                    <i class="fas fa-balance-scale"></i>
                    <h4>Impartiality</h4>
                    <p>We maintain impartiality while providing management system certification services, ensuring objectivity in all activities.</p>
                </div>
                
                <div class="value-item">
                    <i class="fas fa-shield-alt"></i>
                    <h4>Independency</h4>
                    <p>AITF is fully responsible and does not outsource decisions related to certification, ensuring complete independence.</p>
                </div>
                
                <div class="value-item">
                    <i class="fas fa-lock"></i>
                    <h4>Confidentiality</h4>
                    <p>We maintain strict confidentiality and do not disclose client information without prior written consent.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2>Ready to Get Certified?</h2>
            <p>Join thousands of organizations that trust AITF for their certification needs</p>
            <a href="contactUS.php" class="btn">Get Started Today</a>
        </div>
    </section>

    <!-- Footer -->
    <?php include './footerSource.php'; ?>

    <!-- JavaScript for Basic Functionality -->
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Remove preloader
        window.addEventListener('load', function() {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                preloader.style.display = 'none';
            }
        });
    </script>
</body>
</html>
