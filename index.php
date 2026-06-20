<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<!-- Basic -->

<head>
    <?php include './headSource.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/perfect-learn/css/methodology-styles.css">
    <link rel="stylesheet" href="popup-advertisement.css">
    <script src="popup-advertisement.js"></script>
    <style>
        /* Box Navigation */
        /* Modern Navigation Container */
        .box-nav-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 25px;
            margin: 0 auto;
            max-width: 1300px;
            padding: 0 30px;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            perspective: 1000px;
        }

        /* Box Navigation Styles */
        .box-nav-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 14px;
            margin: 24px auto 10px;
            padding: 0 16px;
            width: 100%;
            position: relative;
            z-index: 5;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            transform: none;
        }

        /* Stylish Navigation Items */
        .box-nav-item {
            width: 220px;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.12), rgba(255, 255, 255, 0.05));
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 30px 20px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2), 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transform: translateY(0) rotateX(0) rotateY(0);
            transform-style: preserve-3d;
            will-change: transform, box-shadow;
            text-align: center;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            box-shadow: 0 4px 15px rgba(26, 58, 106, 0.1);
            border: 2px solid rgba(26, 58, 106, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100px;
            position: relative;
            z-index: 5;
            overflow: hidden;
        }

        .box-nav-item {
            background: #ffffff;
            color: #0f172a;
            transform: translateY(0);
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
            border: 1px solid rgba(148, 163, 184, 0.35);
        }

        /* Active State */
        .box-nav-item.active {
            transform: translateY(-4px);
            box-shadow: 0 18px 35px rgba(15, 23, 42, 0.18);
            border-width: 2px;
            animation: none;
        }

        /* Icon Styling */
        .box-nav-icon {
            font-size: 46px;
            margin-bottom: 20px;
            color: #f97316;
            transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            z-index: 2;
            text-shadow: none;
            background: none;
            -webkit-background-clip: initial;
            -webkit-text-fill-color: currentColor;
            display: inline-block;
            padding: 10px;
        }

        .box-nav-item:hover .box-nav-icon {
            transform: scale(1.1);
            color: #2a5a9a;
        }

        /* Title Styling */
        .box-nav-title {
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.6;
            margin: 10px 0 0;
            position: relative;
            z-index: 2;
            text-shadow: none;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            letter-spacing: 0.5px;
            text-transform: uppercase;
            background: none;
            -webkit-background-clip: initial;
            -webkit-text-fill-color: currentColor;
            transition: color 0.3s ease;
            position: relative;
            z-index: 2;
        }

        .box-nav-item .box-nav-icon,
        .box-nav-item .box-nav-title {
            color: inherit;
        }

        /* Add a subtle shine effect on hover */
        /* Shine Effect */
        .box-nav-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg,
                    transparent,
                    rgba(255, 255, 255, 0.1),
                    rgba(255, 255, 255, 0.3),
                    rgba(255, 255, 255, 0.1),
                    transparent);
            transition: 0.8s;
            animation: shine 8s infinite;
        }

        @keyframes shine {
            0% {
                transform: translateX(-100%) skewX(-20deg);
            }

            20% {
                transform: translateX(200%) skewX(-20deg);
            }

            100% {
                transform: translateX(200%) skewX(-20deg);
            }
        }

        .box-nav-item:hover::before {
            left: 100%;
        }

        /* Responsive adjustments */
        /* Responsive Design */
        @media (max-width: 1200px) {
            .box-nav-item {
                width: 180px;
                padding: 25px 15px;
            }

            .box-nav-icon {
                font-size: 38px;
            }

            .box-nav-title {
                font-size: 15px;
            }
        }

        @media (max-width: 992px) {
            .box-nav-container {
                gap: 15px;
            }

            .box-nav-item {
                width: 160px;
                padding: 20px 12px;
                border-radius: 16px;
            }

            .box-nav-icon {
                font-size: 36px;
                margin-bottom: 15px;
            }

            .box-nav-title {
                font-size: 14px;
                line-height: 1.5;
            }
        }

        @media (max-width: 768px) {
            .box-nav-container {
                gap: 15px;
                transform: none;
                margin-top: 24px;
            }

            .box-nav-item {
                width: calc(50% - 10px);
                max-width: 180px;
                padding: 20px 15px;
            }
        }

        @media (max-width: 992px) {
            .box-nav-container {
                margin: 22px 0 14px;
            }

            .box-nav-item {
                width: calc(50% - 8px);
                padding: 18px 10px;
                border-radius: 14px;
            }

            .box-nav-icon {
                font-size: 32px;
                margin-bottom: 12px;
            }

            .box-nav-title {
                font-size: 13px;
                line-height: 1.4;
            }
        }

        @media (max-width: 768px) {
            .management-slider-section {
                padding: 32px 0 44px;
            }

            .slider-container {
                min-height: 280px;
                padding: 0 15px;
            }

            .slide-content {
                padding: 22px 16px;
                min-height: 230px;
                width: 95%;
                border-radius: 20px;
            }

            .slide h3 {
                font-size: 22px;
                margin-bottom: 15px;
                padding-bottom: 10px;
                letter-spacing: 0.5px;
            }

            .slide-description {
                font-size: 14px;
                margin-bottom: 20px;
                padding: 15px 20px;
                line-height: 1.6;
                border-radius: 12px;
            }

            .slide-icon {
                width: 70px;
                height: 70px;
                margin-top: 20px;
                font-size: 28px;
                margin-bottom: 15px;
                border-radius: 20px;
            }

            .slider-btn {
                padding: 10px 20px;
                font-size: 12px;
                min-width: 140px;
                margin-top: 20px;
            }

            .slider-arrow {
                width: 44px;
                height: 44px;
                font-size: 16px;
                top: 45%;
            }

            .prev-arrow {
                left: 5px;
            }

            .next-arrow {
                right: 5px;
            }

            .slider-dots {
                bottom: 14px;
                padding: 10px 15px;
                gap: 8px;
            }

            .slider-dot {
                width: 12px;
                height: 12px;
            }

            .box-nav-container {
                gap: 8px;
                padding: 0 10px;
                transform: none;
                margin: 24px 0 15px;
            }

            .box-nav-item {
                width: calc(50% - 6px);
                padding: 12px 8px;
                border-radius: 12px;
                height: 80px;
            }

            .box-nav-icon {
                font-size: 24px;
                margin-bottom: 6px;
                padding: 3px;
            }

            .box-nav-title {
                font-size: 11px;
                line-height: 1.2;
                letter-spacing: 0.2px;
            }

            /* Enhanced touch interactions for mobile */
            .slide-content {
                transform: translateZ(0) !important;
            }

            .slide-content:hover {
                transform: translateY(-10px) scale(1.01) !important;
            }

            .slide-content:active {
                transform: translateY(-5px) scale(0.99) !important;
                transition: transform 0.1s ease !important;
            }
        }

        @media (max-width: 480px) {
            .management-slider-section {
                padding: 22px 0 34px;
            }

            .slide-content {
                padding: 20px 12px;
                min-height: 210px;
            }

            .slide h3 {
                font-size: 18px;
                margin-bottom: 12px;
            }

            .slide-description {
                font-size: 13px;
                padding: 12px 15px;
                margin-bottom: 15px;
            }

            .slide-icon {
                width: 60px;
                height: 60px;
                margin-top: 15px;
                font-size: 24px;
                margin-bottom: 12px;
            }

            .slider-btn {
                padding: 8px 16px;
                font-size: 11px;
                min-width: 120px;
            }

            .slider-arrow {
                width: 45px;
                height: 45px;
                font-size: 16px;
            }

            .box-nav-item {
                width: calc(50% - 4px);
                padding: 10px 6px;
                height: 70px;
                border-radius: 10px;
            }

            .box-nav-icon {
                font-size: 20px;
                margin-bottom: 4px;
            }

            .box-nav-title {
                font-size: 10px;
                line-height: 1.1;
            }
        }

        .slider-dots {
            position: absolute;
            bottom: 50px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            z-index: 10;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 50px;
            width: max-content;
            margin: 0 auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.8);
        }

        .slider-container.loaded .slider-dots {
            opacity: 1;
            transform: translateY(0);
        }

        .login-error-banner {
            position: fixed;
            top: 80px;
            left: 50%;
            transform: translateX(-50%) translateY(-10px);
            padding: 12px 18px;
            border-radius: 14px;
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            border: 1px solid #fecaca;
            box-shadow: 0 14px 35px rgba(127, 29, 29, 0.35);
            display: none;
            align-items: center;
            gap: 12px;
            z-index: 9999;
            max-width: 420px;
            width: calc(100% - 32px);
            opacity: 0;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .login-error-banner.visible {
            display: flex;
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }

        .login-error-icon {
            width: 32px;
            height: 32px;
            border-radius: 999px;
            background: #b91c1c;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fee2e2;
            font-weight: 700;
            font-size: 18px;
        }

        .login-error-text-main {
            font-size: 14px;
            font-weight: 600;
            color: #7f1d1d;
        }

        .login-error-text-sub {
            font-size: 12px;
            color: #b91c1c;
            margin-top: 2px;
        }

        .login-error-close {
            margin-left: auto;
            background: transparent;
            border: none;
            color: #7f1d1d;
            font-size: 16px;
            cursor: pointer;
            padding: 0 4px;
            line-height: 1;
        }

        /* Typing Animation Styles for AITF */
        .aitf-typing {
            display: inline-block;
            position: relative;
            font-weight: bold;
        }

        .red-char {
            color: #dc2626;
        }

        .blue-char {
            color: #032887ff;
        }
    </style>
</head>

<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">

    <?php include './headerSource.php'; ?>
    <script type="text/javascript">
        /// some script
        // jquery ready start
        $(document).ready(function () {
            <?php if (isset($_SESSION['login_error']) && $_SESSION['login_error']) {
                unset($_SESSION['login_error']); ?>
                $('#login-error-banner').addClass('visible');
            <?php } ?>

            $('.login-error-close').on('click', function () {
                $('#login-error-banner').fadeOut(200);
            });


            // Start the animation when page loads
            // Removed old AITF animation

            // jQuery code
            //////////////////////// Prevent closing from click inside dropdown
            $(document).on('hover', '.dropdown-menu', function (e) {
                e.stopPropagation();
            });
        }); // jquery end
    </script>

    <div id="login-error-banner" class="login-error-banner">
        <div class="login-error-icon">!</div>
        <div>
            <div class="login-error-text-main">Login failed</div>
            <div class="login-error-text-sub">Invalid username or password. Please try again.</div>
        </div>
        <button type="button" class="login-error-close" aria-label="Close">&times;</button>
    </div>

    <style>
        /* Premium Hero Section Styles */
        .modern-hero-section {
            background: #050b1a;
            position: relative;
            padding: 95px 0 60px;
            overflow: hidden;
            color: #ffffff;
            font-family: 'Montserrat', sans-serif;
            min-height: 82vh;
            display: flex;
            align-items: center;
        }

        .hero-bg-image {
            position: absolute;
            top: 85px;
            left: -4%;
            right: -4%;
            bottom: -4%;
            z-index: 0;
            pointer-events: none;
            opacity: 0;
            transform: scale(1.05) translateY(10px);
            animation: heroImageIntro 1s cubic-bezier(0.16, 1, 0.3, 1) 0.1s forwards;
            mask-image: none;
            -webkit-mask-image: none;
        }

        .hero-bg-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center 85%;
            filter: grayscale(3%) brightness(1.0) contrast(1.05);
            animation: heroImgKenBurns 25s ease-in-out infinite alternate;
        }

        @keyframes heroImgKenBurns {
            0% {
                transform: scale(1) translate(0, 0);
            }
            100% {
                transform: scale(1.04) translate(-0.5%, -0.5%);
            }
        }

        .hero-bg-overlay {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 54% 48%, transparent 0%, rgba(5, 11, 26, 0.30) 42%, rgba(5, 11, 26, 0.75) 88%),
                linear-gradient(90deg, rgba(5, 11, 26, 0.96) 0%, rgba(5, 11, 26, 0.80) 24%, rgba(5, 11, 26, 0.18) 48%, rgba(5, 11, 26, 0.30) 100%),
                linear-gradient(180deg, rgba(5, 11, 26, 0.50) 0%, rgba(5, 11, 26, 0.05) 40%, rgba(5, 11, 26, 0.70) 100%);
            opacity: 0;
            z-index: 1;
            animation: heroOverlayIntro 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards 0.5s;
        }

        @keyframes heroImageIntro {
            0% {
                opacity: 0;
                transform: scale(1.05) translateY(10px);
                filter: blur(4px);
            }
            50% {
                opacity: 0.6;
                transform: scale(1.02) translateY(3px);
                filter: blur(1px);
            }
            100% {
                opacity: 0.92;
                transform: scale(1) translateY(0);
                filter: blur(0px);
            }
        }

        @keyframes heroOverlayIntro {
            0% {
                opacity: 0;
            }

            100% {
                opacity: 0.78;
            }
        }

        /* Subtle Grid Background */
        .modern-hero-section::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(59, 130, 246, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(59, 130, 246, 0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            mask-image: radial-gradient(circle at center, black, transparent 90%);
            pointer-events: none;
            z-index: 0;
            opacity: 0;
            animation: fadeIn 0.5s forwards 0.4s;
            /* Delayed grid fade-in */
        }

        /* Cinematic Dark Curtain - First Load */
        .hero-intro-curtain {
            position: absolute;
            inset: 0;
            background: #050b1a;
            z-index: 50;
            animation: curtainFadeOut 0.4s cubic-bezier(0.4, 0, 0.2, 1) 0.15s forwards;
            pointer-events: none;
        }

        @keyframes curtainFadeOut {
            0% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                visibility: hidden;
            }
        }

        /* Soft Blue Glows */
        .hero-glow-1 {
            position: absolute;
            top: 20%;
            right: 10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.1) 0%, transparent 70%);
            pointer-events: none;
            filter: blur(60px);
            z-index: 0;
            opacity: 0;
            animation: fadeIn 0.6s forwards 0.3s;
            /* Delayed glow fade-in */
        }

        .hero-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: clamp(34px, 5vw, 72px);
            position: relative;
            z-index: 10;
            width: 100%;
        }

        /* Left Side Content */
        .hero-text-content {
            flex: 1.12;
            max-width: 660px;
            padding: 14px 0 16px;
            position: relative;
        }

        .hero-text-content::before {
            content: '';
            position: absolute;
            inset: -16px -22px -18px -22px;
            z-index: -1;
            border-radius: 28px;
            background: linear-gradient(90deg, rgba(5, 11, 26, 0.60), rgba(5, 11, 26, 0.14));
            border: 1px solid rgba(148, 163, 184, 0.08);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.18);
            backdrop-filter: blur(1px);
        }

        .hero-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.8px;
            color: #60a5fa;
            margin-bottom: 18px;
            padding: 5px 12px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 50px;
            animation: fadeInDown 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.3s both;
        }

        .hero-label::before {
            content: '';
            width: 8px;
            height: 8px;
            background: #3b82f6;
            border-radius: 50%;
            box-shadow: 0 0 10px #3b82f6;
        }

        .hero-main-title {
            font-size: clamp(40px, 4.4vw, 64px);
            font-weight: 900;
            line-height: 1.05;
            margin: 0 0 20px;
            color: #ffffff;
            letter-spacing: -1.5px;
            text-shadow: 0 14px 36px rgba(0, 0, 0, 0.42);
            animation: fadeInLeft 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.4s both;
        }

        .hero-main-title span {
            display: block;
            background: linear-gradient(135deg, #3b82f6 0%, #5b7cfa 46%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 12px 22px rgba(59, 130, 246, 0.18));
        }

        .hero-sub-title {
            position: relative;
            font-family: 'Inter', sans-serif;
            font-size: clamp(15px, 1.2vw, 18px);
            line-height: 1.75;
            color: #d7e3f3;
            margin: 0 0 28px;
            max-width: 600px;
            padding: 14px 18px 14px 20px;
            border-left: 3px solid #3b82f6;
            border-radius: 0 16px 16px 0;
            background: linear-gradient(90deg, rgba(15, 23, 42, 0.65), rgba(15, 23, 42, 0.14));
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06), 0 12px 32px rgba(0, 0, 0, 0.14);
            text-shadow: 0 4px 14px rgba(0, 0, 0, 0.28);
            animation: fadeInLeft 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.5s both;
        }

        .hero-btns {
            display: flex;
            gap: 16px;
            animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.6s both;
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }

        .hero-btn {
            padding: 13px 26px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none !important;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .hero-btn-primary {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4);
        }

        .hero-btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-3px);
            box-shadow: 0 15px 30px -5px rgba(37, 99, 235, 0.5);
        }

        .hero-btn-secondary {
            background: rgba(255, 255, 255, 0.03);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .hero-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
        }

        /* Right Side - Ecosystem Visual */
        .hero-visual-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            min-height: 480px;
            opacity: 0;
            transform: scale(0.96) translateY(10px);
            animation: visualIntro 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards 0.4s;
        }

        @keyframes visualIntro {
            0% {
                opacity: 0;
                transform: scale(0.96) translateY(10px);
            }

            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .hero-visual-content::before {
            content: '';
            position: absolute;
            width: 700px;
            height: 700px;
            background: radial-gradient(circle, rgba(37, 200, 235, 0.08) 0%, rgba(30, 58, 138, 0.03) 50%, transparent 80%);
            z-index: 1;
            pointer-events: none;
        }

        .ecosystem-container {
            position: relative;
            width: 440px;
            height: 440px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Turbine Pillar */
        .turbine-pillar {
            position: absolute;
            bottom: -40px;
            left: 50%;
            width: 36px;
            height: 260px;
            background: linear-gradient(to top, transparent, rgba(59, 130, 246, 0.15), rgba(96, 165, 250, 0.3));
            transform: translateX(-50%);
            clip-path: polygon(30% 0%, 70% 0%, 100% 100%, 0% 100%);
            z-index: 3;
            pointer-events: none;
        }

        .turbine-pillar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            width: 2px;
            height: 100%;
            background: linear-gradient(to top, transparent, rgba(255, 255, 255, 0.2));
            transform: translateX(-50%);
        }

        /* Background Web structure */
        .web-structure {
            position: absolute;
            inset: 0;
            z-index: 2;
            opacity: 0.4;
        }

        .web-line {
            position: absolute;
            top: 50%;
            left: 50%;
            height: 1px;
            background: linear-gradient(90deg, rgba(96, 165, 250, 0.3), transparent);
            transform-origin: left center;
        }

        /* Center AITF Node */
        .center-node-wrapper {
            position: relative;
            z-index: 35;
        }

        .center-node {
            width: 130px;
            height: 130px;
            background: rgba(15, 23, 42, 0.18);
            border: 2px solid rgba(147, 197, 253, 0.45);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            box-shadow: 0 0 60px rgba(37, 99, 235, 0.48), inset 0 0 28px rgba(255, 255, 255, 0.12);
            position: relative;
            animation: centerFloat 6s infinite ease-in-out;
            overflow: hidden;
        }

        .center-node img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 50%;
            display: block;
        }

        .center-node::after {
            content: '';
            position: absolute;
            inset: -12px;
            border-radius: 50%;
            border: 1px solid rgba(37, 200, 235, 0.5);
            animation: centerPulse 4s infinite;
        }

        /* Orbiting Standards (Turbine Blades) */
        .orbit-ring {
            position: absolute;
            inset: 0;
            border: 1px solid rgba(37, 200, 235, 0.12);
            border-radius: 50%;
            z-index: 5;
        }

        .iso-standard {
            position: absolute;
            width: 120px;
            height: 120px;
            padding: 0;
            background: radial-gradient(circle, rgba(15, 23, 42, 0.72) 0%, rgba(15, 23, 42, 0.12) 66%, transparent 72%);
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 800;
            color: #ffffff;
            backdrop-filter: blur(10px);
            z-index: 30;
            animation: orbitRotation 82s linear infinite;
            transition: filter 0.3s ease, opacity 0.3s ease;
            filter: drop-shadow(0 18px 28px rgba(0, 0, 0, 0.34));
        }

        .iso-standard img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 50%;
            animation: iconFloat 4.8s infinite ease-in-out;
        }

        .iso-standard span {
            font-size: 9px;
            font-weight: 700;
            margin-top: 3px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: center;
            line-height: 1.2;
            padding: 0 5px;
            display: none;
            /* Hide labels to focus on the image circles */
        }

        /* OHSMS - Orange Safety theme */
        .iso-45001 {
            border: none;
            box-shadow: 0 0 34px rgba(245, 158, 11, 0.52), 0 0 78px rgba(245, 158, 11, 0.18);
            animation: orbitRotation 82s linear -54.66s infinite, isoGlowPulse 3.5s ease-in-out 2.8s infinite;
        }

        /* QMS - Blue Quality theme */
        .iso-9001 {
            border: none;
            box-shadow: 0 0 34px rgba(59, 130, 246, 0.48), 0 0 70px rgba(59, 130, 246, 0.16);
            animation: orbitRotation 82s linear 0s infinite, isoGlowPulse 3.5s ease-in-out 3.2s infinite;
        }

        /* EMS - Green Earth theme */
        .iso-14001 {
            border: none;
            box-shadow: 0 0 34px rgba(16, 185, 129, 0.48), 0 0 70px rgba(16, 185, 129, 0.14);
            animation: orbitRotation 82s linear -27.33s infinite, isoGlowPulse 3.5s ease-in-out 3.6s infinite;
        }

        @keyframes isoGlowPulse {
            0%, 100% {
                filter: drop-shadow(0 18px 28px rgba(0, 0, 0, 0.34)) brightness(1);
            }
            50% {
                filter: drop-shadow(0 18px 28px rgba(0, 0, 0, 0.34)) brightness(1.15);
            }
        }

        @keyframes iconFloat {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-5px);
            }
        }

        /* Connection Lines - Enhanced Turbine Blades */
        .connection-line {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 180px;
            height: 3px;
            background: linear-gradient(90deg, #60a5fa 0%, rgba(59, 130, 246, 0.5) 60%, transparent 100%);
            transform-origin: left center;
            opacity: 0.7;
            z-index: 10;
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
            animation: connectionOrbit 82s linear infinite;
        }

        .line-1 {
            animation-delay: 0s;
        }

        .line-2 {
            animation-delay: -27.33s;
        }

        .line-3 {
            animation-delay: -54.66s;
        }

        /* Data Flow Particles */
        .connection-line::after {
            content: '';
            position: absolute;
            top: -3px;
            left: 0;
            width: 8px;
            height: 8px;
            background: #ffffff;
            border-radius: 50%;
            box-shadow: 0 0 12px #60a5fa, 0 0 25px #ffffff;
            animation: dataFlow 4s linear infinite;
            filter: blur(0.5px);
        }

        /* Pulse at Intersection with Center */
        .connection-line::before {
            content: '';
            position: absolute;
            left: -8px;
            top: -8px;
            width: 20px;
            height: 20px;
            background: rgba(96, 165, 250, 0.7);
            border-radius: 50%;
            opacity: 0;
            animation: intersectionPulse 4s ease-out infinite;
        }

        .line-2::after,
        .line-2::before {
            animation-delay: -1.3s;
        }

        .line-3::after,
        .line-3::before {
            animation-delay: -2.6s;
        }

        /* Keyframes */
        @keyframes orbitRotation {
            from {
                transform: rotate(0deg) translateX(180px) rotate(0deg);
            }

            to {
                transform: rotate(360deg) translateX(180px) rotate(-360deg);
            }
        }

        @keyframes connectionOrbit {
            from {
                transform: rotate(0deg) translateY(-50%);
            }

            to {
                transform: rotate(360deg) translateY(-50%);
            }
        }

        @keyframes dataFlow {
            0% {
                left: 100%;
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                left: 0%;
                opacity: 0;
            }
        }

        @keyframes intersectionPulse {
            0% {
                transform: scale(1);
                opacity: 0;
            }

            90% {
                transform: scale(1);
                opacity: 0;
            }

            95% {
                transform: scale(1.3);
                opacity: 0.9;
            }

            100% {
                transform: scale(3);
                opacity: 0;
            }
        }

        @keyframes centerFloat {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-15px);
            }
        }

        @keyframes centerPulse {
            0% {
                transform: scale(1);
                opacity: 0.8;
            }

            100% {
                transform: scale(1.5);
                opacity: 0;
            }
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-40px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

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

        /* Responsive Design */
        @media (max-width: 1200px) {
            .hero-main-title {
                font-size: 46px;
            }

            .ecosystem-container {
                transform: scale(0.82);
            }
        }

        @media (max-width: 991px) {
            .modern-hero-section {
                padding: 90px 0 50px;
            }

            .hero-container {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }

            .hero-text-content {
                max-width: 100%;
                order: 2;
            }

            .hero-visual-content {
                min-height: 380px;
                order: 1;
            }

            .hero-btns {
                justify-content: center;
            }

            .hero-sub-title {
                margin-left: auto;
                margin-right: auto;
            }

            .ecosystem-container {
                transform: scale(0.70);
            }
        }

        @media (max-width: 576px) {
            .hero-main-title {
                font-size: 34px;
            }

            .hero-sub-title {
                font-size: 14px;
            }

            .hero-btns {
                flex-direction: column;
            }

            .hero-btn {
                width: 100%;
                justify-content: center;
            }

            .ecosystem-container {
                transform: scale(0.55);
            }
        }

        /* AITF Journey Section Styles */
        /* AITF Journey Section Styles - Horizontal Scroll Redesign */
        .aitf-journey-section {
            background: #ffffff;
            background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 40px 40px;
            padding: 100px 0;
            position: relative;
            overflow: hidden;
            color: #0a192f;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .journey-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
            width: 100%;
        }

        .journey-header {
            text-align: center;
            margin-bottom: 60px;
        }

        /* Horizontal Timeline Styles */
        .horizontal-timeline-wrapper {
            position: relative;
            padding: 150px 0;
            margin: 40px 0;
            overflow: visible;
        }

        /* The Horizontal Spine */
        .timeline-spine {
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 4px;
            background: #e2e8f0;
            transform: translateY(-50%);
            z-index: 1;
        }

        .timeline-spine-progress {
            position: absolute;
            top: 50%;
            left: 0;
            width: 0%;
            height: 4px;
            background: #b8860b;
            transform: translateY(-50%);
            z-index: 2;
            box-shadow: 0 0 15px rgba(184, 134, 11, 0.4);
        }

        /* Milestone Content Container */
        .milestones-track {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 3;
            width: 100%;
            min-width: 1200px;
            /* Ensure enough space for horizontal layout */
        }

        .milestone-item {
            position: relative;
            width: 250px;
            flex-shrink: 0;
        }

        /* Alternating Top/Bottom Layout */
        .milestone-item.top {
            padding-bottom: 220px;
        }

        .milestone-item.bottom {
            padding-top: 220px;
        }

        .milestone-dot-wrapper {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
        }

        .milestone-dot {
            width: 24px;
            height: 24px;
            background: #ffffff;
            border: 4px solid #e2e8f0;
            border-radius: 50%;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 0 0 0 rgba(184, 134, 11, 0);
        }

        .milestone-item.active .milestone-dot {
            border-color: #b8860b;
            background: #b8860b;
            transform: scale(1.3);
            box-shadow: 0 0 20px rgba(184, 134, 11, 0.6);
        }

        /* Connecting Vertical Line */
        .milestone-connector {
            position: absolute;
            left: 50%;
            width: 2px;
            background: #e2e8f0;
            transform: translateX(-50%);
            transition: all 0.6s ease;
        }

        .milestone-item.top .milestone-connector {
            bottom: 50%;
            height: 0;
            /* Animated */
            margin-bottom: 12px;
        }

        .milestone-item.bottom .milestone-connector {
            top: 50%;
            height: 0;
            /* Animated */
            margin-top: 12px;
        }

        .milestone-item.active .milestone-connector {
            background: #b8860b;
            height: 60px;
        }

        /* Milestone Card */
        .milestone-card {
            background: #ffffff;
            padding: 30px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
            text-align: center;
            opacity: 0;
            transform: translateY(30px) scale(0.9);
            transition: all 0.8s cubic-bezier(0.165, 0.84, 0.44, 1);
            width: 280px;
        }

        .milestone-item.active .milestone-card {
            opacity: 1;
            transform: translateY(0) scale(1.05);
            border-color: #b8860b;
            box-shadow: 0 30px 60px rgba(184, 134, 11, 0.15);
        }

        .milestone-year {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            font-weight: 900;
            color: #b8860b;
            display: block;
            margin-bottom: 8px;
        }

        .milestone-card h4 {
            font-size: 17px;
            font-weight: 800;
            color: #0a192f;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .milestone-card p {
            font-size: 14px;
            line-height: 1.6;
            color: #64748b;
            margin: 0;
        }

        /* Mobile Styles - Keep Vertical on Small Screens */
        @media (max-width: 991px) {
            .milestones-track {
                flex-direction: column;
                min-width: 100%;
                gap: 50px;
            }

            .timeline-spine,
            .timeline-spine-progress {
                left: 30px;
                width: 4px;
                height: 100%;
                transform: none;
                top: 0;
            }

            .milestone-item {
                width: 100%;
                padding: 0 0 0 80px !important;
                text-align: left;
            }

            .milestone-dot-wrapper {
                left: 30px;
                transform: translate(-50%, -50%);
            }

            .milestone-connector {
                display: none;
            }

            .milestone-card {
                text-align: left;
                opacity: 1;
                transform: none;
            }
        }
    </style>

    <div class="modern-hero-section">
        <div class="hero-intro-curtain"></div>
        <div class="hero-bg-image">
            <img src="aitfImages/hero image aitf.png" alt="AITF Hero Background">
        </div>
        <div class="hero-bg-overlay"></div>
        <div class="hero-glow-1"></div>
        <div class="container">
            <div class="hero-container">
                <div class="hero-text-content">
                    <div class="hero-label">NABCB Accredited Certification Body</div>
                    <h1 class="hero-main-title">
                        Your Trusted Partner In<span>Business Excellence Journey</span>
                    </h1>
                    <p class="hero-sub-title">
                        Empowering organizations through accredited, authentic, impartial and ethical certification
                        services for management system standards.
                    </p>
                    <div class="hero-btns">
                        <a href="text/audit_application_form.pdf" class="hero-btn hero-btn-secondary" download>
                            <i class="fas fa-file-download"></i> Download Form
                        </a>
                        <a href="application.php#online-application-form" class="hero-btn hero-btn-primary">
                            <i class="fas fa-edit"></i> Apply Online
                        </a>
                    </div>
                </div>

                <div class="hero-visual-content">
                    <div class="ecosystem-container">
                        <!-- Background Web Structure -->
                        <div class="web-structure">
                            <div class="web-line" style="transform: rotate(30deg); width: 250px;"></div>
                            <div class="web-line" style="transform: rotate(90deg); width: 250px;"></div>
                            <div class="web-line" style="transform: rotate(150deg); width: 250px;"></div>
                            <div class="web-line" style="transform: rotate(210deg); width: 250px;"></div>
                            <div class="web-line" style="transform: rotate(270deg); width: 250px;"></div>
                            <div class="web-line" style="transform: rotate(330deg); width: 250px;"></div>
                        </div>

                        <!-- Turbine Structure -->
                        <div class="turbine-pillar"></div>
                        <div class="orbit-ring"></div>

                        <!-- Center Hub -->
                        <div class="center-node-wrapper">
                            <div class="center-node">
                                <img src="aitfImages/services.png" alt="Our Services">
                            </div>
                        </div>

                        <!-- ISO Standards Nodes -->
                        <div class="iso-standard iso-9001">
                            <img src="aitfImages/qmscircle.png" alt="QMS Icon">
                        </div>
                        <div class="iso-standard iso-14001">
                            <img src="aitfImages/emscircle.png" alt="EMS Icon">
                        </div>
                        <div class="iso-standard iso-45001">
                            <img src="aitfImages/ohsms circle.png" alt="OHSMS Icon">
                        </div>

                        <!-- Active Connection Lines -->
                        <div class="connection-line line-1"></div>
                        <div class="connection-line line-2"></div>
                        <div class="connection-line line-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AITF Journey Section - Horizontal Storytelling -->
    <section class="aitf-journey-section" id="journey-scroll-section">
        <div class="journey-container">
            <div class="journey-header">
                <span class="journey-tag"
                    style="font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 14px; text-transform: uppercase; letter-spacing: 3px; color: #b8860b; display: block; margin-bottom: 15px;">Our
                    Legacy</span>
                <h2 class="journey-title"
                    style="font-family: 'Playfair Display', serif; font-size: 48px; font-weight: 800; color: #0a192f; margin-bottom: 30px; line-height: 1.2;">
                    A Journey of <span style="color: #b8860b;">Integrity & Excellence</span></h2>
            </div>

            <div class="legacy-highlight"
                style="display: flex; flex-direction: column; align-items: center; margin-bottom: 40px;">
                <div class="legacy-number-wrapper"
                    style="position: relative; display: flex; align-items: center; justify-content: center;">
                    <div class="legacy-number" id="legacy-counter"
                        style="font-size: 120px; font-weight: 900; line-height: 1; color: #0a192f; transition: color 0.4s ease;">
                        0</div>
                    <span class="legacy-plus" id="legacy-plus"
                        style="font-size: 80px; font-weight: 900; color: #b8860b; opacity: 0; margin-left: 5px;">+</span>
                </div>
                <div class="legacy-text"
                    style="font-size: 18px; font-weight: 600; text-transform: uppercase; letter-spacing: 5px; color: #64748b;">
                    Years of Excellence</div>
            </div>

            <div class="horizontal-timeline-wrapper">
                <div class="timeline-spine"></div>
                <div class="timeline-spine-progress"></div>

                <div class="milestones-track">
                    <!-- Milestone 1: Top -->
                    <div class="milestone-item top" data-step="1">
                        <div class="milestone-dot-wrapper">
                            <div class="milestone-dot"></div>
                        </div>
                        <div class="milestone-connector"></div>
                        <div class="milestone-card" style="border-top: 5px solid #3b82f6;">
                            <span class="milestone-year" style="color: #3b82f6;">2011</span>
                            <h4>Foundation</h4>
                            <p>Establishment of AITF as a specialized assessment body, focusing on institutional
                                integrity.</p>
                        </div>
                    </div>

                    <!-- Milestone 2: Bottom -->
                    <div class="milestone-item bottom" data-step="2">
                        <div class="milestone-dot-wrapper">
                            <div class="milestone-dot"></div>
                        </div>
                        <div class="milestone-connector"></div>
                        <div class="milestone-card" style="border-bottom: 5px solid #10b981;">
                            <span class="milestone-year" style="color: #10b981;">2015</span>
                            <h4>ISO 9001 Operations</h4>
                            <p>Commencement of Quality Management Certification Assessment services across industrial
                                sectors.</p>
                        </div>
                    </div>

                    <!-- Milestone 3: Top -->
                    <div class="milestone-item top" data-step="3">
                        <div class="milestone-dot-wrapper">
                            <div class="milestone-dot"></div>
                        </div>
                        <div class="milestone-connector"></div>
                        <div class="milestone-card" style="border-top: 5px solid #f59e0b;">
                            <span class="milestone-year" style="color: #f59e0b;">2020</span>
                            <h4>Accredited ISO 14001</h4>
                            <p>Expansion into Environmental Management systems with Accredited status.</p>
                        </div>
                    </div>

                    <!-- Milestone 4: Bottom -->
                    <div class="milestone-item bottom" data-step="4">
                        <div class="milestone-dot-wrapper">
                            <div class="milestone-dot"></div>
                        </div>
                        <div class="milestone-connector"></div>
                        <div class="milestone-card" style="border-bottom: 5px solid #ef4444;">
                            <span class="milestone-year" style="color: #ef4444;">2025</span>
                            <h4>Accredited ISO 45001</h4>
                            <p>Integration of Occupational Health & Safety Management systems under accredited
                                frameworks.</p>
                        </div>
                    </div>

                    <!-- Milestone 5: Top -->
                    <div class="milestone-item top" data-step="5">
                        <div class="milestone-dot-wrapper">
                            <div class="milestone-dot"></div>
                        </div>
                        <div class="milestone-connector"></div>
                        <div class="milestone-card" style="border-top: 5px solid #8b5cf6;">
                            <span class="milestone-year" style="color: #8b5cf6;">Present</span>
                            <h4>NABCB Compliance</h4>
                            <p>Maintaining rigorous standards through continual compliance and technical excellence.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="growth-indicators milestone-summary-grid">
                <div class="growth-card milestone-summary-card">
                    <div class="summary-card-glow"></div>
                    <div class="summary-card-head">
                        <span class="summary-icon"><i class="fa fa-handshake"></i></span>
                        <div>
                            <span class="summary-kicker">Partnership Journey</span>
                            <h3>From first trust to long-term client relationships</h3>
                        </div>
                    </div>
                    <div class="summary-progress-line">
                        <span></span>
                    </div>
                    <div class="summary-steps">
                        <div class="summary-step">
                            <span class="step-dot"></span>
                            <div>
                                <h4>Foundation</h4>
                                <p>Built on impartial assessment practices and disciplined certification processes.</p>
                            </div>
                        </div>
                        <div class="summary-step">
                            <span class="step-dot"></span>
                            <div>
                                <h4>Client Confidence</h4>
                                <p>Strengthened relationships through transparent assessment and professional communication.</p>
                            </div>
                        </div>
                        <div class="summary-step">
                            <span class="step-dot"></span>
                            <div>
                                <h4>Continual Trust</h4>
                                <p>Evaluating organizations with consistent, ethical and value-added certification services.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="growth-card milestone-summary-card accent-card">
                    <div class="summary-card-glow"></div>
                    <div class="summary-card-head">
                        <span class="summary-icon"><i class="fa fa-chart-line"></i></span>
                        <div>
                            <span class="summary-kicker">Assessment Capability</span>
                            <h3>A steady evolution in audit readiness and service scope</h3>
                        </div>
                    </div>
                    <div class="capability-flow">
                        <div class="capability-node">
                            <span>QMS</span>
                            <small>Quality discipline</small>
                        </div>
                        <div class="flow-arrow"><i class="fa fa-arrow-right"></i></div>
                        <div class="capability-node">
                            <span>EMS</span>
                            <small>Environmental responsibility</small>
                        </div>
                        <div class="flow-arrow"><i class="fa fa-arrow-right"></i></div>
                        <div class="capability-node">
                            <span>OHSMS</span>
                            <small>Workplace safety focus</small>
                        </div>
                    </div>
                    <div class="summary-note">
                        <i class="fa fa-shield-alt"></i>
                        <p>Each milestone reflects stronger technical capability, better audit discipline and a broader commitment to management system excellence.</p>
                    </div>
                </div>
            </div>

            <style>
                .milestone-summary-grid {
                    display: grid;
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    gap: 30px;
                    margin-top: 64px;
                }

                .milestone-summary-card {
                    position: relative;
                    overflow: hidden;
                    min-height: 330px;
                    padding: 38px;
                    border-radius: 28px;
                    color: #ffffff;
                    background:
                        linear-gradient(145deg, rgba(10, 25, 47, 0.98), rgba(15, 36, 68, 0.96)),
                        radial-gradient(circle at 15% 0%, rgba(184, 134, 11, 0.18), transparent 38%);
                    border: 1px solid rgba(255, 255, 255, 0.08);
                    box-shadow: 0 28px 80px rgba(10, 25, 47, 0.18);
                    transition: transform 0.35s ease, box-shadow 0.35s ease;
                }

                .milestone-summary-card:hover {
                    transform: translateY(-8px);
                    box-shadow: 0 38px 100px rgba(10, 25, 47, 0.26);
                }

                .summary-card-glow {
                    position: absolute;
                    width: 240px;
                    height: 240px;
                    right: -90px;
                    top: -100px;
                    border-radius: 50%;
                    background: rgba(184, 134, 11, 0.24);
                    filter: blur(18px);
                    pointer-events: none;
                }

                .accent-card .summary-card-glow {
                    background: rgba(59, 130, 246, 0.22);
                }

                .summary-card-head {
                    position: relative;
                    display: flex;
                    align-items: flex-start;
                    gap: 18px;
                    margin-bottom: 26px;
                    z-index: 2;
                }

                .summary-icon {
                    width: 58px;
                    height: 58px;
                    flex: 0 0 auto;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 20px;
                    background: linear-gradient(135deg, #b8860b, #daa520);
                    color: #ffffff;
                    font-size: 24px;
                    box-shadow: 0 18px 34px rgba(184, 134, 11, 0.24);
                }

                .accent-card .summary-icon {
                    background: linear-gradient(135deg, #2563eb, #38bdf8);
                    box-shadow: 0 18px 34px rgba(37, 99, 235, 0.24);
                }

                .summary-kicker {
                    display: block;
                    margin-bottom: 7px;
                    color: #f8d477;
                    font-size: 12px;
                    font-weight: 900;
                    letter-spacing: 1.8px;
                    text-transform: uppercase;
                }

                .accent-card .summary-kicker {
                    color: #93c5fd;
                }

                .summary-card-head h3 {
                    margin: 0;
                    color: #ffffff;
                    font-size: 24px;
                    font-weight: 850;
                    line-height: 1.25;
                    letter-spacing: -0.3px;
                }

                .summary-progress-line {
                    position: relative;
                    height: 4px;
                    margin: 0 0 24px 76px;
                    border-radius: 999px;
                    background: rgba(255, 255, 255, 0.08);
                    overflow: hidden;
                }

                .summary-progress-line span {
                    display: block;
                    width: 82%;
                    height: 100%;
                    border-radius: inherit;
                    background: linear-gradient(90deg, #b8860b, #f8d477, rgba(248, 212, 119, 0.35));
                }

                .summary-steps {
                    position: relative;
                    display: grid;
                    gap: 18px;
                    z-index: 2;
                }

                .summary-step {
                    display: grid;
                    grid-template-columns: 20px 1fr;
                    gap: 14px;
                    align-items: flex-start;
                }

                .step-dot {
                    width: 14px;
                    height: 14px;
                    margin-top: 6px;
                    border-radius: 50%;
                    background: #f8d477;
                    box-shadow: 0 0 0 6px rgba(184, 134, 11, 0.14), 0 0 22px rgba(248, 212, 119, 0.50);
                }

                .summary-step h4 {
                    margin: 0 0 5px;
                    color: #ffffff;
                    font-size: 16px;
                    font-weight: 850;
                }

                .summary-step p,
                .summary-note p {
                    margin: 0;
                    color: #cbd5e1;
                    font-size: 14.5px;
                    line-height: 1.65;
                }

                .capability-flow {
                    position: relative;
                    display: grid;
                    grid-template-columns: 1fr auto 1fr auto 1fr;
                    gap: 12px;
                    align-items: stretch;
                    z-index: 2;
                }

                .capability-node {
                    min-height: 118px;
                    padding: 18px 14px;
                    border-radius: 22px;
                    background: rgba(255, 255, 255, 0.08);
                    border: 1px solid rgba(255, 255, 255, 0.10);
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    text-align: center;
                }

                .capability-node span {
                    display: block;
                    margin-bottom: 8px;
                    color: #ffffff;
                    font-size: 22px;
                    font-weight: 900;
                    letter-spacing: -0.3px;
                }

                .capability-node small {
                    color: #cbd5e1;
                    font-size: 12.5px;
                    line-height: 1.45;
                    font-weight: 650;
                }

                .flow-arrow {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #93c5fd;
                    opacity: 0.85;
                }

                .summary-note {
                    position: relative;
                    z-index: 2;
                    display: flex;
                    gap: 14px;
                    margin-top: 24px;
                    padding: 18px;
                    border-radius: 20px;
                    background: rgba(37, 99, 235, 0.12);
                    border: 1px solid rgba(147, 197, 253, 0.14);
                }

                .summary-note i {
                    color: #93c5fd;
                    margin-top: 4px;
                    font-size: 18px;
                }

                @media (max-width: 991px) {
                    .milestone-summary-grid {
                        grid-template-columns: 1fr;
                    }
                }

                @media (max-width: 575px) {
                    .milestone-summary-card {
                        padding: 28px 22px;
                    }

                    .summary-card-head {
                        flex-direction: column;
                    }

                    .summary-progress-line {
                        margin-left: 0;
                    }

                    .capability-flow {
                        grid-template-columns: 1fr;
                    }

                    .flow-arrow {
                        transform: rotate(90deg);
                    }
                }
            </style>
        </div>
    </section>

    <!-- Why Choose AITF Section -->
    <section class="why-choose-aitf"
        style="padding: 100px 0; background: #f8fafc; overflow: hidden; position: relative;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0" style="position: relative; z-index: 2;">
                    <div class="why-tag"
                        style="display: inline-block; padding: 6px 15px; background: rgba(184, 134, 11, 0.1); color: #b8860b; border-radius: 50px; font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px;">
                        Why Choose AITF</div>
                    <h2 class="why-title"
                        style="font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 800; color: #0a192f; margin-bottom: 25px; line-height: 1.2;">
                        Our Commitment to <span style="color: #b8860b;">Excellence</span></h2>
                    <p class="why-text"
                        style="font-size: 18px; line-height: 1.8; color: #475569; margin-bottom: 35px; text-align: justify;">
                        At <strong>AITF Management Certification Ltd.</strong>, we are dedicated to delivering
                        comprehensive assessment services through our team of highly qualified auditors. Our primary
                        objective is to enhance our clients' business performance by conducting value-added audits of
                        their processes and management systems.
                    </p>

                    <div class="feature-list" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="feature-item"
                            style="display: flex; align-items: center; gap: 15px; padding: 15px; background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.3s ease;">
                            <div class="feature-icon"
                                style="width: 45px; height: 45px; background: #b8860b; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">
                                <i class="fa fa-user-tie"></i>
                            </div>
                            <span style="font-weight: 600; color: #0a192f;">Qualified Auditors</span>
                        </div>
                        <div class="feature-item"
                            style="display: flex; align-items: center; gap: 15px; padding: 15px; background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.3s ease;">
                            <div class="feature-icon"
                                style="width: 45px; height: 45px; background: #b8860b; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">
                                <i class="fa fa-chart-line"></i>
                            </div>
                            <span style="font-weight: 600; color: #0a192f;">Business Growth</span>
                        </div>
                        <div class="feature-item"
                            style="display: flex; align-items: center; gap: 15px; padding: 15px; background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.3s ease;">
                            <div class="feature-icon"
                                style="width: 45px; height: 45px; background: #b8860b; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">
                                <i class="fa fa-search-plus"></i>
                            </div>
                            <span style="font-weight: 600; color: #0a192f;">Value-Added Audits</span>
                        </div>
                        <div class="feature-item"
                            style="display: flex; align-items: center; gap: 15px; padding: 15px; background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: transform 0.3s ease;">
                            <div class="feature-icon"
                                style="width: 45px; height: 45px; background: #b8860b; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">
                                <i class="fa fa-check-double"></i>
                            </div>
                            <span style="font-weight: 600; color: #0a192f;">Process Integrity</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6" style="position: relative;">
                    <div class="image-stack" style="position: relative; padding: 30px;">
                        <!-- Background Decorative Element -->
                        <div class="decorative-blob"
                            style="position: absolute; top: -10%; right: -10%; width: 100%; height: 100%; background: radial-gradient(circle, rgba(184, 134, 11, 0.08) 0%, transparent 70%); z-index: 1; border-radius: 50%;">
                        </div>

                        <!-- Main Image Container with Premium Frame -->
                        <div class="main-img-container"
                            style="position: relative; z-index: 2; border-radius: 40px; padding: 15px; background: white; box-shadow: 0 40px 100px rgba(10, 25, 47, 0.15); transition: all 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);">
                            <div class="img-inner-wrapper"
                                style="border-radius: 28px; overflow: hidden; position: relative;">
                                <img src="aitfImages/commitment.png" alt="AITF commitment to excellence"
                                    style="width: 100%; height: auto; display: block; transition: transform 0.8s cubic-bezier(0.165, 0.84, 0.44, 1);">
                                <div class="img-overlay"
                                    style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(10, 25, 47, 0.4) 0%, transparent 40%); opacity: 0; transition: opacity 0.5s ease;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .feature-item:hover {
                transform: translateY(-8px);
                background: #b8860b !important;
                box-shadow: 0 15px 30px rgba(184, 134, 11, 0.2) !important;
            }

            .feature-item:hover span {
                color: white !important;
            }

            .feature-item:hover .feature-icon {
                background: white !important;
                color: #b8860b !important;
            }

            .main-img-container:hover {
                transform: translateY(-10px) scale(1.02);
            }

            .main-img-container:hover .img-inner-wrapper img {
                transform: scale(1.1);
            }

            .main-img-container:hover .img-overlay {
                opacity: 1;
            }
        </style>
    </section>

    <!-- Our Core Principles Section -->
    <section class="core-principles"
        style="padding: 100px 0; background: #ffffff; overflow: hidden; position: relative;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 order-2 order-lg-1" style="position: relative;">
                    <div class="image-stack-left" style="position: relative; padding: 30px;">
                        <!-- Background Decorative Element -->
                        <div class="decorative-blob-left"
                            style="position: absolute; top: -10%; left: -10%; width: 100%; height: 100%; background: radial-gradient(circle, rgba(16, 185, 129, 0.08) 0%, transparent 70%); z-index: 1; border-radius: 50%;">
                        </div>

                        <!-- Main Image Container with Premium Frame -->
                        <div class="principle-img-container"
                            style="position: relative; z-index: 2; border-radius: 40px; padding: 15px; background: white; box-shadow: 0 40px 100px rgba(16, 185, 129, 0.12); transition: all 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);">
                            <div class="img-inner-wrapper"
                                style="border-radius: 28px; overflow: hidden; position: relative;">
                                <img src="aitfImages/coreprienciple.png" alt="AITF core principles"
                                    style="width: 100%; height: auto; display: block; transition: transform 0.8s cubic-bezier(0.165, 0.84, 0.44, 1);">
                                <div class="principle-overlay"
                                    style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(6, 78, 59, 0.3) 0%, transparent 40%); opacity: 0; transition: opacity 0.5s ease;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 order-1 order-lg-2 mb-5 mb-lg-0"
                    style="position: relative; z-index: 2; padding-left: 50px;">
                    <div class="principle-tag"
                        style="display: inline-block; padding: 6px 15px; background: rgba(16, 185, 129, 0.1); color: #10b981; border-radius: 50px; font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px;">
                        Core Values</div>
                    <h2 class="principle-title"
                        style="font-family: 'Playfair Display', serif; font-size: 42px; font-weight: 800; color: #0a192f; margin-bottom: 25px; line-height: 1.2;">
                        Our Core <span style="color: #10b981;">Principles</span></h2>
                    <p class="principle-text"
                        style="font-size: 18px; line-height: 1.8; color: #475569; margin-bottom: 35px; text-align: justify;">
                        We maintain the highest standards of <strong>competence, independence, and impartiality</strong>
                        in our assessment processes. Our team of certified auditors is committed to helping
                        organizations achieve and demonstrate compliance with international standards, driving
                        continuous improvement and operational excellence across all business functions.
                    </p>
                </div>
            </div>
        </div>

        <style>
            .p-feature-item:hover {
                transform: translateX(10px);
            }

            .principle-img-container:hover {
                transform: translateY(-10px) scale(1.02);
                box-shadow: 0 50px 110px rgba(16, 185, 129, 0.18);
            }

            .principle-img-container:hover .img-inner-wrapper img {
                transform: scale(1.1);
            }

            .principle-img-container:hover .principle-overlay {
                opacity: 1;
            }
        </style>
    </section>

    <!-- Current Certification Services Section -->
    <section class="current-cert-services">
        <div class="container">
            <div class="cert-services-heading">
                <span class="cert-services-kicker">Certification Services</span>
                <h2>Currently Providing Certification Services</h2>
                <p>
                    AITF currently provides professional certification services for Quality Management,
                    Environmental Management and Occupational Health &amp; Safety Management systems.
                </p>
            </div>

            <div class="row cert-services-grid">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="cert-service-card qms-card">
                        <div class="cert-service-image">
                            <img src="aitfImages/qmsaudit.png" alt="QMS certification audit">
                        </div>
                        <div class="cert-service-content">
                            <span class="cert-standard-pill">ISO 9001</span>
                            <h3>QMS Certification</h3>
                            <h4>Quality Management System</h4>
                            <p>
                                Helps organizations improve process consistency, customer satisfaction,
                                operational control and continual improvement through a structured quality framework.
                            </p>
                            <ul>
                                <li><i class="fa fa-check"></i> Process and quality control</li>
                                <li><i class="fa fa-check"></i> Customer-focused improvement</li>
                                <li><i class="fa fa-check"></i> Stronger compliance confidence</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="cert-service-card ems-card">
                        <div class="cert-service-image">
                            <img src="aitfImages/emsauditf.png" alt="EMS certification audit">
                        </div>
                        <div class="cert-service-content">
                            <span class="cert-standard-pill">ISO 14001</span>
                            <h3>EMS Certification</h3>
                            <h4>Environmental Management System</h4>
                            <p>
                                Supports organizations in managing environmental responsibilities, reducing impact,
                                improving resource efficiency and demonstrating responsible operations.
                            </p>
                            <ul>
                                <li><i class="fa fa-check"></i> Environmental impact control</li>
                                <li><i class="fa fa-check"></i> Resource efficiency practices</li>
                                <li><i class="fa fa-check"></i> Sustainable business approach</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="cert-service-card ohsms-card">
                        <div class="cert-service-image">
                            <img src="aitfImages/ohsmsaudit.png" alt="OHSMS certification audit">
                        </div>
                        <div class="cert-service-content">
                            <span class="cert-standard-pill">ISO 45001</span>
                            <h3>OHSMS Certification</h3>
                            <h4>Occupational Health &amp; Safety Management System</h4>
                            <p>
                                Enables organizations to build safer workplaces, identify hazards, reduce risks
                                and strengthen health and safety performance for their workforce.
                            </p>
                            <ul>
                                <li><i class="fa fa-check"></i> Workplace risk reduction</li>
                                <li><i class="fa fa-check"></i> Safer operational practices</li>
                                <li><i class="fa fa-check"></i> Health and safety performance</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="cert-services-cta">
                <p>Need certification support for your organization?</p>
                <a href="application.php#online-application-form">Apply for Certification <i class="fa fa-arrow-right"></i></a>
            </div>
        </div>

        <style>
            .current-cert-services {
                position: relative;
                overflow: hidden;
                padding: 105px 0 95px;
                background:
                    radial-gradient(circle at 8% 12%, rgba(37, 99, 235, 0.10), transparent 28%),
                    radial-gradient(circle at 92% 18%, rgba(16, 185, 129, 0.12), transparent 30%),
                    linear-gradient(180deg, #f8fbff 0%, #eef6ff 100%);
                font-family: 'Inter', sans-serif;
            }

            .current-cert-services::before {
                content: '';
                position: absolute;
                inset: 0;
                background-image:
                    linear-gradient(rgba(37, 99, 235, 0.045) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(37, 99, 235, 0.045) 1px, transparent 1px);
                background-size: 42px 42px;
                mask-image: linear-gradient(180deg, rgba(0,0,0,0.75), transparent 92%);
                pointer-events: none;
            }

            .current-cert-services .container {
                position: relative;
                z-index: 2;
            }

            .cert-services-heading {
                max-width: 850px;
                margin: 0 auto 52px;
                text-align: center;
            }

            .cert-services-kicker {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 8px 16px;
                margin-bottom: 16px;
                border-radius: 999px;
                background: rgba(37, 99, 235, 0.10);
                color: #2563eb;
                font-size: 13px;
                font-weight: 800;
                letter-spacing: 1.8px;
                text-transform: uppercase;
            }

            .cert-services-heading h2 {
                margin: 0 0 16px;
                font-family: 'Playfair Display', serif;
                font-size: clamp(34px, 4vw, 52px);
                font-weight: 900;
                color: #0a192f;
                line-height: 1.12;
            }

            .cert-services-heading p {
                margin: 0 auto;
                max-width: 760px;
                color: #475569;
                font-size: 18px;
                line-height: 1.8;
            }

            .cert-services-grid {
                align-items: stretch;
            }

            .cert-service-card {
                height: 100%;
                overflow: hidden;
                border-radius: 30px;
                background: rgba(255, 255, 255, 0.96);
                border: 1px solid rgba(148, 163, 184, 0.22);
                box-shadow: 0 24px 70px rgba(15, 23, 42, 0.10);
                transition: transform 0.35s ease, box-shadow 0.35s ease;
                position: relative;
            }

            .cert-service-card::before {
                content: '';
                position: absolute;
                inset: 0 0 auto;
                height: 6px;
                background: linear-gradient(90deg, #2563eb, #38bdf8);
                z-index: 2;
            }

            .cert-service-card.ems-card::before {
                background: linear-gradient(90deg, #059669, #34d399);
            }

            .cert-service-card.ohsms-card::before {
                background: linear-gradient(90deg, #f59e0b, #ef4444);
            }

            .cert-service-card:hover {
                transform: translateY(-10px);
                box-shadow: 0 34px 92px rgba(15, 23, 42, 0.16);
            }

            .cert-service-image {
                height: 245px;
                overflow: hidden;
                background: #0f172a;
                position: relative;
            }

            .cert-service-image::after {
                content: '';
                position: absolute;
                inset: 0;
                background: linear-gradient(180deg, transparent 45%, rgba(15, 23, 42, 0.58) 100%);
                pointer-events: none;
            }

            .cert-service-image img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
                transition: transform 0.65s ease;
            }

            .cert-service-card:hover .cert-service-image img {
                transform: scale(1.08);
            }

            .cert-service-content {
                padding: 30px 28px 32px;
            }

            .cert-standard-pill {
                display: inline-flex;
                margin-bottom: 16px;
                padding: 7px 13px;
                border-radius: 999px;
                background: #eff6ff;
                color: #1d4ed8;
                font-weight: 900;
                font-size: 12px;
                letter-spacing: 0.8px;
            }

            .ems-card .cert-standard-pill {
                background: #ecfdf5;
                color: #047857;
            }

            .ohsms-card .cert-standard-pill {
                background: #fff7ed;
                color: #c2410c;
            }

            .cert-service-content h3 {
                margin: 0 0 6px;
                color: #0f172a;
                font-size: 25px;
                font-weight: 900;
                letter-spacing: -0.4px;
            }

            .cert-service-content h4 {
                margin: 0 0 16px;
                color: #2563eb;
                font-size: 14px;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.7px;
                line-height: 1.5;
            }

            .ems-card .cert-service-content h4 {
                color: #059669;
            }

            .ohsms-card .cert-service-content h4 {
                color: #d97706;
            }

            .cert-service-content p {
                margin: 0 0 20px;
                color: #526173;
                font-size: 15.5px;
                line-height: 1.75;
            }

            .cert-service-content ul {
                list-style: none;
                padding: 0;
                margin: 0;
                display: grid;
                gap: 10px;
            }

            .cert-service-content li {
                display: flex;
                align-items: flex-start;
                gap: 10px;
                color: #334155;
                font-size: 14.5px;
                font-weight: 650;
                line-height: 1.45;
            }

            .cert-service-content li i {
                width: 22px;
                height: 22px;
                flex: 0 0 auto;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                background: #dbeafe;
                color: #2563eb;
                font-size: 11px;
                margin-top: -1px;
            }

            .ems-card .cert-service-content li i {
                background: #d1fae5;
                color: #059669;
            }

            .ohsms-card .cert-service-content li i {
                background: #ffedd5;
                color: #ea580c;
            }

            .cert-services-cta {
                margin: 26px auto 0;
                padding: 24px 28px;
                max-width: 820px;
                border-radius: 26px;
                background: linear-gradient(135deg, #0f172a, #1e3a8a);
                color: #ffffff;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 20px;
                box-shadow: 0 24px 70px rgba(15, 23, 42, 0.20);
            }

            .cert-services-cta p {
                margin: 0;
                color: #e2e8f0;
                font-size: 18px;
                font-weight: 800;
            }

            .cert-services-cta a {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                padding: 13px 20px;
                border-radius: 999px;
                background: linear-gradient(135deg, #38bdf8, #2563eb);
                color: #ffffff;
                text-decoration: none;
                font-weight: 900;
                white-space: nowrap;
                box-shadow: 0 15px 34px rgba(37, 99, 235, 0.26);
                transition: transform 0.25s ease, box-shadow 0.25s ease;
            }

            .cert-services-cta a:hover {
                color: #ffffff;
                text-decoration: none;
                transform: translateY(-2px);
                box-shadow: 0 20px 42px rgba(37, 99, 235, 0.34);
            }

            @media (max-width: 991px) {
                .current-cert-services {
                    padding: 78px 0;
                }

                .cert-services-cta {
                    flex-direction: column;
                    text-align: center;
                }
            }

            @media (max-width: 575px) {
                .cert-services-heading p {
                    font-size: 16px;
                }

                .cert-service-image {
                    height: 220px;
                }

                .cert-service-content {
                    padding: 26px 22px 28px;
                }

                .cert-services-cta a {
                    width: 100%;
                }
            }
        </style>
    </section>

    <!-- Premium Management Systems Slider Section -->
    <div class="section management-slider-section" style="position: relative; z-index: 10;">
        <div class="slider-container" style="position: relative;">
            <button class="slider-arrow prev-arrow" aria-label="Previous slide">
                <span class="fa fa-angles-left"></span>
            </button>

            <div class="slider-track">
                <!-- Slide 1 - Quality Management System -->
                <div class="slide active">
                    <div class="slide-content">
                        <div class="slide-icon">
                            <i class="fa fa-award"></i>
                        </div>
                        <h3>QUALITY MANAGEMENT SYSTEM</h3>
                        <p class="slide-description">Ensures consistent product and service quality by meeting customer
                            and regulatory requirements through ISO 9001 standards.</p>
                        <a href="contactUs.php" class="slider-btn">APPLY NOW <i class="fa fa-arrow-right"></i></a>
                    </div>
                </div>

                <!-- Slide 2 - Environmental Management System -->
                <div class="slide">
                    <div class="slide-content">
                        <div class="slide-icon">
                            <i class="fa fa-leaf"></i>
                        </div>
                        <h3>ENVIRONMENTAL MANAGEMENT SYSTEM</h3>
                        <p class="slide-description">Helps organizations minimize environmental impact, comply with
                            laws, and improve sustainability under ISO 14001.</p>
                        <a href="contactUs.php" class="slider-btn">APPLY NOW <i class="fa fa-arrow-right"></i></a>
                    </div>
                </div>

                <!-- Slide 3 - Safety Management System -->
                <div class="slide">
                    <div class="slide-content">
                        <div class="slide-icon">
                            <i class="fa fa-shield-halved"></i>
                        </div>
                        <h3>OCCUPATIONAL HEALTH &amp; SAFETY MANAGEMENT SYSTEM (OHSMS)</h3>
                        <p class="slide-description">Builds a robust OH&S framework to reduce risks, protect employee
                            well-being, and drive continual improvement under ISO 45001.</p>
                        <a href="contactUs.php" class="slider-btn">APPLY NOW <i class="fa fa-arrow-right"></i></a>
                    </div>
                </div>

            </div>

            <button class="slider-arrow next-arrow" aria-label="Next slide">
                <span class="fa fa-angles-right"></span>
            </button>

            <div class="slider-nav">
                <span class="slider-dot" data-slide="0"></span>
                <span class="slider-dot" data-slide="1"></span>
                <span class="slider-dot" data-slide="2"></span>
            </div>

            <!-- Box Navigation Controls Removed -->
        </div>
        <div class="slider-glow"></div>
    </div>
    <!-- end section -->

    <!-- NABCB Accredited ISO Certification Section -->
    <div class="section layout_padding nabcb-section"
        style="position: relative; padding: 60px 0 !important; background: linear-gradient(135deg, #0a192f 0%, #172a45 100%);">
        <div class="nabcb-particles" id="nabcb-particles"></div>
        <div class="container" style="position: relative; z-index: 2;">
            <div class="row">
                <div class="col-md-12 text-center">
                    <div class="section-header">
                        <span class="section-tag">Accredited CERTIFICATION</span>
                        <h2 class="section-title">NABCB Accredited <span class="highlight">ISO Certification</span></h2>
                        <div class="title-divider">
                            <span class="divider-line"></span>
                            <span class="divider-icon">✧</span>
                            <span class="divider-line"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="info-card">
                        <div class="info-card-inner">
                            <div class="info-card-front">
                                <div class="icon-wrapper">
                                    <i class="fa fa-award"></i>
                                </div>
                                <h3>NABCB Accreditation</h3>
                                <p>India's Premier Accreditation Body</p>
                                <div class="hover-indicator">
                                    <span>Hover to Learn More</span>
                                    <i class="fa fa-arrow-down"></i>
                                </div>
                            </div>
                            <div class="info-card-back">
                                <p>The National Accreditation Board for Certification Bodies (NABCB) is a key entity in
                                    India's quality infrastructure, serving as a constituent board of the Quality
                                    Council of India (QCI).</p>
                            </div>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-card-inner">
                            <div class="info-card-front">
                                <div class="icon-wrapper">
                                    <i class="fa fa-globe"></i>
                                </div>
                                <h3>International Recognition</h3>
                                <p>Globally Accepted Certifications</p>
                            </div>
                            <div class="info-card-back">
                                <p>A certificate issued by an AITF that is accredited by NABCB and issued with the NABCB
                                    & IAF MLA mark is accepted in all economies, as the accreditation body is an IAF MLA
                                    signatory.</p>
                            </div>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-card-inner">
                            <div class="info-card-front">
                                <div class="icon-wrapper">
                                    <i class="fa fa-check-circle"></i>
                                </div>
                                <h3>Quality Assurance</h3>
                                <p>Trusted Certification Process</p>
                            </div>
                            <div class="info-card-back">
                                <p>NABCB assesses the competence and impartiality of CABs (AITF) based on international
                                    standards and its own criteria, ensuring that the certificates and reports issued
                                    are reliable and trustworthy.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 text-center">
                    <div class="certificate-showcase">
                        <h3 class="showcase-title">Our Accredited Certifications</h3>
                        <div class="certificate-showcase-grid">
                            <div class="certificate-item">
                                <div class="certificate-img-container">
                                    <img src="images/certificate_1.png" alt="ISO 9001:2015 Certification"
                                        class="certificate-thumb">
                                    <div class="certificate-hover">
                                        <a href="images/certificate_1.png" class="view-cert-btn"
                                            data-lightbox="cert-gallery" data-title="ISO 9001:2015 Certification">
                                            <i class="fa fa-search-plus"></i>
                                            <span>View</span>
                                        </a>
                                    </div>
                                </div>
                                <h4>ISO 9001:2015</h4>
                                <p>Quality Management System</p>
                            </div>

                            <div class="certificate-item">
                                <div class="certificate-img-container">
                                    <img src="images/certificate_2.png" alt="ISO 14001:2015 Certification"
                                        class="certificate-thumb">
                                    <div class="certificate-hover">
                                        <a href="images/certificate_2.png" class="view-cert-btn"
                                            data-lightbox="cert-gallery" data-title="ISO 14001:2015 Certification">
                                            <i class="fa fa-search-plus"></i>
                                            <span>View</span>
                                        </a>
                                    </div>
                                </div>
                                <h4>ISO 14001:2015</h4>
                                <p>Environmental Management System</p>
                            </div>

                            <div class="certificate-item">
                                <div class="certificate-img-container">
                                    <img src="images/certificate_3.png" alt="ISO 45001:2018 Certification"
                                        class="certificate-thumb">
                                    <div class="certificate-hover">
                                        <a href="images/certificate_3.png" class="view-cert-btn"
                                            data-lightbox="cert-gallery" data-title="ISO 45001:2018 Certification">
                                            <i class="fa fa-search-plus"></i>
                                            <span>View</span>
                                        </a>
                                    </div>
                                </div>
                                <h4>ISO 45001:2018</h4>
                                <p>Occupational Health & Safety</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>
    </div>


    <style>
        /* NABCB Section - Larger Certification Text */
        .certificate-item h4 {
            font-size: 24px !important;
            font-weight: 700;
            margin: 15px 0 5px;
        }

        .certificate-item p {
            font-size: 20px !important;
            font-weight: 500;
            color: #e0e0e0;
        }

        /* AITF Intro Section Styling */
        .aitf-intro {
            font-family: 'Montserrat', 'Poppins', sans-serif;
            font-size: 1.2rem;
            line-height: 1.9;
            margin-bottom: 1.8rem;
            text-align: justify;
            letter-spacing: 0.5px;
            word-spacing: 1.5px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            padding: 25px 30px;
            font-weight: 500;
            color: #ffffff;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5),
                -1px -1px 3px rgba(0, 0, 0, 0.3);
            position: relative;
            background: rgba(0, 0, 0, 0.4);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .aitf-intro::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            border-radius: 12px;
            z-index: -1;
            opacity: 0.3;
        }

        .aitf-intro strong {
            color: #ffffff;
            font-weight: 700;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7),
                -1px -1px 3px rgba(0, 0, 0, 0.5);
            position: relative;
            padding: 0 3px;
        }

        @media (max-width: 768px) {
            .aitf-intro {
                font-size: 1.1rem;
                line-height: 1.9;
                padding: 20px 15px;
                letter-spacing: 0.4px;
                color: #ffffff;
                background: rgba(0, 0, 0, 0.5);
                text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
            }

            .aitf-intro strong {
                color: #ffffff;
                text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
            }
        }

        /* NABCB Section Styling */
        .nabcb-section {
            position: relative;
            background: linear-gradient(135deg, #0a192f 0%, #172a45 100%);
            color: #fff;
            padding: 60px 0 !important;
            margin: 0;
            overflow: hidden;
            z-index: 1;
            width: 100%;
            box-sizing: border-box;
        }

        .nabcb-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgcGF0dGVyblRyYW5zZm9ybT0icm90YXRlKDQ1KSI+PHJlY3Qgd2lkdGg9IjUwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJyZ2JhKDI1NSwyNTUsMjU1LDAuMDMpIi8+PC9wYXR0ZXNuPjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJ1cmwoI3BhdHRlcm4pIi8+PC9zdmc+');
            z-index: -1;
            opacity: 0.3;
        }

        .section-header {
            margin-bottom: 60px;
            position: relative;
            z-index: 2;
        }

        .section-tag {
            display: inline-block;
            background: rgba(0, 173, 239, 0.2);
            color: #00adef;
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 20px;
            text-transform: uppercase;
            border: 1px solid rgba(0, 173, 239, 0.3);
        }

        .section-title {
            font-size: 42px;
            font-weight: 800;
            margin-bottom: 20px;
            background: linear-gradient(90deg, #fff, #64f0ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 30px rgba(100, 240, 255, 0.3);
            position: relative;
        }

        .section-title .highlight {
            color: #ffd700;
            -webkit-text-fill-color: #ffd700;
        }

        .title-divider {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 25px auto 40px;
            max-width: 400px;
        }

        .divider-line {
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(0, 173, 239, 0.5), transparent);
            flex: 1;
        }

        .divider-icon {
            color: #00adef;
            margin: 0 15px;
            font-size: 20px;
            opacity: 0.7;
        }

        /* Info Cards */
        .info-card {
            perspective: 1000px;
            margin-bottom: 30px;
            height: 220px;
        }

        .info-card-inner {
            position: relative;
            width: 100%;
            height: 100%;
            text-align: center;
            transition: transform 0.8s;
            transform-style: preserve-3d;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border-radius: 15px;
        }

        .info-card:hover .info-card-inner {
            transform: rotateY(180deg);
        }

        .info-card-front,
        .info-card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            -webkit-backface-visibility: hidden;
            backface-visibility: hidden;
            border-radius: 15px;
            padding: 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .info-card-front {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .info-card-back {
            background: linear-gradient(135deg, #00adef 0%, #0077b6 100%);
            color: white;
            transform: rotateY(180deg);
            justify-content: center;
            text-align: center;
            padding: 25px;
            font-size: 15px;
            line-height: 1.6;
        }

        .icon-wrapper {
            width: 70px;
            height: 70px;
            background: rgba(0, 173, 239, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            border: 2px solid rgba(0, 173, 239, 0.3);
        }

        .icon-wrapper i {
            font-size: 30px;
            color: #00adef;
        }

        .info-card h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #fff;
        }

        .info-card p {
            color: #b3e0ff;
            font-size: 14px;
            margin: 0;
        }

        .hover-indicator {
            margin-top: 15px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .hover-indicator i {
            margin-top: 5px;
            animation: bounce 2s infinite;
        }

        /* Certificate Showcase */
        .certificate-showcase {
            margin-top: 60px;
            position: relative;
            z-index: 2;
        }

        .showcase-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 40px;
            color: #fff;
            position: relative;
            display: inline-block;
        }

        .showcase-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #00adef, #00f2fe);
            border-radius: 2px;
        }

        .certificate-showcase-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 40px;
            margin: 0 auto;
            max-width: 1300px;
            padding: 30px 20px;
        }

        .certificate-item {
            background: rgba(10, 25, 47, 0.7);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(100, 255, 218, 0.1);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(8px);
            position: relative;
            z-index: 1;
        }

        .certificate-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 242, 254, 0.1) 0%, rgba(79, 172, 254, 0.1) 100%);
            z-index: -1;
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .certificate-item:hover::before {
            opacity: 1;
        }

        .certificate-item:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(100, 255, 218, 0.3);
        }

        .certificate-img-container {
            position: relative;
            overflow: hidden;
            height: 280px;
            background: #000;
        }

        .certificate-thumb {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            background: #fff;
            padding: 15px;
            box-sizing: border-box;
        }

        .certificate-hover {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(10, 25, 47, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(5px);
        }

        .certificate-item:hover .certificate-hover {
            opacity: 1;
        }

        .certificate-item:hover .certificate-thumb {
            transform: scale(1.05);
        }

        .view-cert-btn {
            display: inline-flex;
            align-items: center;
            padding: 12px 30px;
            background: transparent;
            color: #64ffda;
            border: 2px solid #64ffda;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .view-cert-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            background: rgba(100, 255, 218, 0.1);
            transition: width 0.3s ease;
            z-index: -1;
        }

        .view-cert-btn:hover::before {
            width: 100%;
        }

        .view-cert-btn i {
            margin-right: 10px;
            font-size: 16px;
            transition: transform 0.3s ease;
        }

        .view-cert-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(100, 255, 218, 0.3);
            color: #64ffda;
        }

        .view-cert-btn:hover i {
            transform: scale(1.2);
        }

        .certificate-item h4 {
            color: #64ffda;
            font-size: 20px;
            font-weight: 600;
            margin: 25px 25px 8px;
            letter-spacing: 0.5px;
        }

        .certificate-item p {
            color: #ccd6f6;
            font-size: 15px;
            margin: 0 25px 25px;
            font-size: 28px;
        }

        .section-tag {
            font-size: 12px;
            padding: 6px 15px;
        }

        .certificate-badges {
            flex-direction: column;
            align-items: center;
        }

        .badge {
            width: 180px;
        }
        }
    </style>

    <script>
        // Initialize particles.js for the NABCB section
        document.addEventListener('DOMContentLoaded', function () {
            // Add any necessary JavaScript for animations or interactions
            const cards = document.querySelectorAll('.info-card');

            cards.forEach(card => {
                card.addEventListener('mouseenter', function () {
                    this.querySelector('.info-card-inner').style.transform = 'rotateY(180deg)';
                });

                card.addEventListener('mouseleave', function () {
                    this.querySelector('.info-card-inner').style.transform = 'rotateY(0)';
                });
            });

            // Add parallax effect to floating shapes
            const shapes = document.querySelectorAll('.floating-shapes .shape');

            document.addEventListener('mousemove', (e) => {
                const x = e.clientX / window.innerWidth;
                const y = e.clientY / window.innerHeight;

                shapes.forEach((shape, index) => {
                    const speed = (index + 1) * 0.5;
                    const xMove = (x - 0.5) * 20 * speed;
                    const yMove = (y - 0.5) * 20 * speed;

                    shape.style.transform = `translate(${xMove}px, ${yMove}px) ${window.getComputedStyle(shape).transform.split(' ').slice(2).join(' ')}`;
                });
            });

        });
    </script>
    <!-- End NABCB Section -->


    <!-- Research Section Placeholder -->
    <!-- Add your research section content here -->
    </div>
    </div>

    <?php include './footerSource.php'; ?>
    <script>
        // Management Systems Slider
        document.addEventListener('DOMContentLoaded', function () {
            // Mark slider as loading
            const sliderSection = document.querySelector('.management-slider-section');
            const sliderContainer = document.querySelector('.slider-container');

            if (sliderSection && sliderContainer) {
                // Show loading state
                sliderSection.style.opacity = '1';

                // Initialize slider after a small delay to ensure styles are applied
                setTimeout(() => {
                    sliderContainer.classList.add('loaded');
                    sliderSection.classList.add('loaded');

                    // Show navigation dots with a slight delay
                    const dots = document.querySelector('.slider-dots');
                    if (dots) {
                        setTimeout(() => {
                            dots.style.opacity = '1';
                            dots.style.transform = 'translateY(0)';
                        }, 300);
                    }
                }, 50);
            }
            const sliderTrack = document.querySelector('.slider-track');
            const slides = document.querySelectorAll('.slide');
            const dots = document.querySelectorAll('.slider-dot');
            const prevBtn = document.querySelector('.prev-arrow');
            const nextBtn = document.querySelector('.next-arrow');

            // Start at -1 so first updateActiveSlide(0) fully initializes state
            let currentSlide = -1;
            let slideInterval;
            const slideCount = slides.length;

            // Initialize slider with performance optimizations and safety mechanisms
            function initSlider() {
                // Clear all slide classes first
                const allSlides = document.querySelectorAll('.slide');
                allSlides.forEach(slide => {
                    slide.classList.remove('active', 'prev', 'next');
                });

                // Reset current slide index
                currentSlide = -1;

                // Set initial active states
                updateActiveSlide(0);

                // Add aggressive safety mechanism to prevent stuck slider
                setInterval(() => {
                    const slides = document.querySelectorAll('.slide');
                    const activeSlide = document.querySelector('.slide.active');

                    // If no active slide found, reset to first slide
                    if (!activeSlide && slides.length > 0) {
                        console.log('Safety reset: No active slide found, resetting to slide 0');
                        currentSlide = -1;
                        updateActiveSlide(0);
                        // Restart auto-slide if it stopped
                        startAutoSlide();
                    }

                    // Force progression if stuck on last slide for too long
                    if (currentSlide === slides.length - 1) {
                        setTimeout(() => {
                            if (currentSlide === slides.length - 1) {
                                console.log('Force progression from last slide');
                                nextSlide();
                            }
                        }, 3500); // Slightly less than auto-slide interval
                    }
                }, 3000); // Check every 3 seconds

                // Start auto-slide with slight delay for initial load
                setTimeout(startAutoSlide, 800);

                // Add event listeners for arrows
                prevBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    pauseSlide();
                    prevSlide();
                    startAutoSlide();
                });

                nextBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    pauseSlide();
                    nextSlide();
                    startAutoSlide();
                });

                // Add click events to dots
                dots.forEach((dot, index) => {
                    dot.addEventListener('click', (e) => {
                        e.preventDefault();
                        goToSlide(index);
                    });
                });

                // Add click events to box navigation items
                const boxNavItems = document.querySelectorAll('.box-nav-item');
                boxNavItems.forEach((item, index) => {
                    item.addEventListener('click', (e) => {
                        e.preventDefault();
                        goToSlide(index);
                    });
                });

                // Pause on hover, resume on leave (desktop)
                const sliderContainer = document.querySelector('.slider-container');
                sliderContainer.addEventListener('mouseenter', pauseSlide);
                sliderContainer.addEventListener('mouseleave', startAutoSlide);

                // Enhanced touch events for mobile with better performance
                let touchStartX = 0;
                let touchEndX = 0;
                let touchStartTime = 0;

                sliderTrack.addEventListener('touchstart', (e) => {
                    touchStartX = e.changedTouches[0].screenX;
                    touchStartTime = Date.now();
                    pauseSlide();
                }, { passive: true });

                sliderTrack.addEventListener('touchend', (e) => {
                    touchEndX = e.changedTouches[0].screenX;
                    handleSwipe();
                    startAutoSlide();
                }, { passive: true });

                function handleSwipe() {
                    const swipeThreshold = 50;
                    const timeThreshold = 300; // Maximum time for a swipe
                    const diff = touchStartX - touchEndX;
                    const timeDiff = Date.now() - touchStartTime;

                    // Only register as swipe if it's fast enough and far enough
                    if (Math.abs(diff) > swipeThreshold && timeDiff < timeThreshold) {
                        if (diff > 0) {
                            nextSlide();
                        } else {
                            prevSlide();
                        }
                    }
                }
            }

            // Go to specific slide (user-triggered)
            function goToSlide(index) {
                pauseSlide();
                updateActiveSlide(index);
                startAutoSlide();
            }

            // Update active slide with improved transition handling and robust error handling
            function updateActiveSlide(newIndex) {
                // Get all slides and navigation elements
                const allSlides = document.querySelectorAll('.slide');
                const allDots = document.querySelectorAll('.slider-dot');
                const boxNavItems = document.querySelectorAll('.box-nav-item');

                // Validate slides exist
                if (!allSlides || allSlides.length === 0) {
                    console.error('No slides found');
                    return;
                }

                // Calculate the new index with boundary checks
                const totalSlides = allSlides.length;
                newIndex = newIndex % totalSlides;
                if (newIndex < 0) newIndex = totalSlides - 1;

                // Always update for infinite scroll (remove the check that prevents same index)
                console.log('Updating to slide:', newIndex, 'Total slides:', totalSlides);

                // Clear all classes first to prevent blur issues
                allSlides.forEach(slide => {
                    slide.classList.remove('active', 'prev', 'next');
                });

                // Force immediate update without requestAnimationFrame for reliability
                // Update current slide index
                const oldSlide = currentSlide;
                currentSlide = newIndex;

                // Update slide classes for smooth 3D transitions
                allSlides.forEach((slide, index) => {
                    if (index === currentSlide) {
                        slide.classList.add('active');
                    } else if (index === (currentSlide - 1 + totalSlides) % totalSlides) {
                        slide.classList.add('prev');
                    } else if (index === (currentSlide + 1) % totalSlides) {
                        slide.classList.add('next');
                    }
                });

                // Update dots with smooth transitions
                allDots.forEach((dot, index) => {
                    dot.classList.toggle('active', index === newIndex);
                });

                // Update box navigation
                boxNavItems.forEach((item, index) => {
                    item.classList.toggle('active', index === newIndex);
                });

                console.log('Slide transition complete:', currentSlide);
            }

            // Next slide with robust boundary handling
            function nextSlide() {
                const totalSlides = document.querySelectorAll('.slide').length;
                const nextIndex = (currentSlide + 1) % totalSlides;
                console.log('Next slide: from', currentSlide, 'to', nextIndex);
                updateActiveSlide(nextIndex);
            }

            // Previous slide with robust boundary handling
            function prevSlide() {
                const totalSlides = document.querySelectorAll('.slide').length;
                const prevIndex = (currentSlide - 1 + totalSlides) % totalSlides;
                console.log('Prev slide: from', currentSlide, 'to', prevIndex);
                updateActiveSlide(prevIndex);
            }

            // Auto slide with improved performance and robust cycling
            function startAutoSlide() {
                clearInterval(slideInterval);
                const totalSlides = document.querySelectorAll('.slide').length;
                if (totalSlides <= 1) return;

                slideInterval = setInterval(() => {
                    const nextIndex = (currentSlide + 1) % totalSlides;
                    console.log('Auto slide: from', currentSlide, 'to', nextIndex);

                    // Force update even if same index (prevents stuck on last slide)
                    if (nextIndex === 0 && currentSlide === totalSlides - 1) {
                        currentSlide = -1; // Force reset
                    }

                    updateActiveSlide(nextIndex);
                }, 4000); // Faster, snappier slide change
            }

            // Pause auto slide
            function pauseSlide() {
                clearInterval(slideInterval);
            }

            // Initialize slider after ensuring everything is loaded
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initSlider);
            } else {
                // If DOM is already loaded
                setTimeout(initSlider, 300);
            }
        });

        // Enhanced welcome text animation
        document.addEventListener('DOMContentLoaded', function () {
            const welcomeText = document.getElementById('welcomeText');
            if (welcomeText) {
                // Add animation class after a short delay
                setTimeout(() => {
                    welcomeText.classList.add('animate-welcome');

                    // Add floating effect
                    setInterval(() => {
                        welcomeText.style.transform = 'translateY(-5px)';
                        setTimeout(() => {
                            welcomeText.style.transform = 'translateY(0)';
                        }, 1000);
                    }, 2000);
                }, 100);

                // Enhanced hover effect
                welcomeText.addEventListener('mouseover', function () {
                    this.style.transform = 'scale(1.05) translateY(-5px)';
                    this.style.transition = 'all 0.3s ease';
                });

                welcomeText.addEventListener('mouseout', function () {
                    this.style.transform = 'scale(1) translateY(0)';
                });
            }
        });

        /* counter js */

        (function ($) {
            $.fn.countTo = function (options) {
                options = options || {};

                return $(this).each(function () {
                    // set options for current element
                    var settings = $.extend({}, $.fn.countTo.defaults, {
                        from: $(this).data('from'),
                        to: $(this).data('to'),
                        speed: $(this).data('speed'),
                        refreshInterval: $(this).data('refresh-interval'),
                        decimals: $(this).data('decimals')
                    }, options);

                    // how many times to update the value, and how much to increment the value on each update
                    var loops = Math.ceil(settings.speed / settings.refreshInterval),
                        increment = (settings.to - settings.from) / loops;

                    // references & variables that will change with each update
                    var self = this,
                        $self = $(this),
                        loopCount = 0,
                        value = settings.from,
                        data = $self.data('countTo') || {};

                    $self.data('countTo', data);

                    // if an existing interval can be found, clear it first
                    if (data.interval) {
                        clearInterval(data.interval);
                    }
                    data.interval = setInterval(updateTimer, settings.refreshInterval);

                    // initialize the element with the starting value
                    render(value);

                    function updateTimer() {
                        value += increment;
                        loopCount++;

                        render(value);

                        if (typeof (settings.onUpdate) == 'function') {
                            settings.onUpdate.call(self, value);
                        }

                        if (loopCount >= loops) {
                            // remove the interval
                            $self.removeData('countTo');
                            clearInterval(data.interval);
                            value = settings.to;

                            if (typeof (settings.onComplete) == 'function') {
                                settings.onComplete.call(self, value);
                            }
                        }
                    }

                    function render(value) {
                        var formattedValue = settings.formatter.call(self, value, settings);
                        $self.html(formattedValue);
                    }
                });
            };

            $.fn.countTo.defaults = {
                from: 0, // the number the element should start at
                to: 0, // the number the element should end at
                speed: 1000, // how long it should take to count between the target numbers
                refreshInterval: 100, // how often the element should be updated
                decimals: 0, // the number of decimal places to show
                formatter: formatter, // handler for formatting the value before rendering
                onUpdate: null, // callback method for every time the element is updated
                onComplete: null       // callback method for when the element finishes updating
            };

            function formatter(value, options) {
                return value.toFixed(options.decimals);
            }
        }(jQuery));

        jQuery(function ($) {
            // custom formatting example
            $('.count-number').data('countToOptions', {
                formatter: function (value, options) {
                    return value.toFixed(options.decimals).replace(/\B(?=(?:\d{3})+(?!\d))/g, ',');
                }
            });

            // start all the timers
            $('.timer').each(count);

            function count(options) {
                var $this = $(this);
                options = $.extend({}, options || {}, $this.data('countToOptions') || {});
                $this.countTo(options);
            }
        });
    </script>

    <style>
        @keyframes wave {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        /* Ultra Creative 3D Welcome Section */
        @keyframes float3d {

            0%,
            100% {
                transform: translateY(0) rotateX(0) rotateY(0);
            }

            25% {
                transform: translateY(-10px) rotateX(5deg) rotateY(5deg);
            }

            75% {
                transform: translateY(10px) rotateX(-5deg) rotateY(-5deg);
            }
        }

        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        @keyframes aitfLogoBounceIn {
            0% {
                transform: translateY(40px) scale(0.9);
                opacity: 0;
                filter: brightness(1.2);
            }

            60% {
                transform: translateY(-8px) scale(1.06);
                opacity: 1;
            }

            80% {
                transform: translateY(3px) scale(0.99);
            }

            100% {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }

        @keyframes aitfWordSlideUp {
            from {
                transform: translateY(14px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes aitfWordFadeScale {
            from {
                transform: scale(0.96);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes aitfTypeIn {
            from {
                max-width: 0ch;
            }

            to {
                max-width: 9ch;
            }
        }

        @keyframes aitfWordHighlight {
            0% {
                opacity: 0;
                text-shadow: none;
            }

            60% {
                opacity: 1;
                text-shadow: 0 0 10px rgba(129, 140, 248, 0.9), 0 0 24px rgba(56, 189, 248, 0.6);
            }

            100% {
                opacity: 1;
                text-shadow: 0 0 6px rgba(129, 140, 248, 0.6), 0 0 14px rgba(56, 189, 248, 0.4);
            }
        }

        @keyframes aitfPillIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes aitfMetaIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes cardSheen {

            0%,
            35% {
                transform: translate3d(-30%, 0, 0);
                opacity: 0;
            }

            40%,
            55% {
                transform: translate3d(10%, 0, 0);
                opacity: 1;
            }

            60%,
            100% {
                transform: translate3d(40%, 0, 0);
                opacity: 0;
            }
        }

        /* Subtle continuous motion */
        @keyframes aitfLogoBreath {

            0%,
            100% {
                transform: translateY(0) scale(1);
                text-shadow: 0 0 10px rgba(129, 140, 248, 0.7), 0 0 26px rgba(56, 189, 248, 0.5);
            }

            50% {
                transform: translateY(-3px) scale(1.03);
                text-shadow: 0 0 14px rgba(129, 140, 248, 0.9), 0 0 34px rgba(56, 189, 248, 0.7);
            }
        }

        @keyframes aitfWordDrift {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-2px);
            }
        }

        @keyframes aitfMetaGlow {

            0%,
            100% {
                text-shadow: 0 0 0 rgba(56, 189, 248, 0.0);
                opacity: 0.9;
            }

            50% {
                text-shadow: 0 0 10px rgba(56, 189, 248, 0.7);
                opacity: 1;
            }
        }

        .welcome-3d-container {
            perspective: 1000px;
            margin: 40px 0;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 200px;
        }

        .aitf-hero-text {
            position: relative;
            z-index: 2;
            text-align: center;
            color: #f9fafb;
            font-family: "Poppins", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .aitf-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 4px 16px;
            margin-bottom: 12px;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.5);
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.65), rgba(30, 64, 175, 0.35));
            box-shadow: 0 0 0 1px rgba(15, 23, 42, 0.7);
            font-size: 0.72rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: #e5e7eb;
            backdrop-filter: blur(16px);
            opacity: 0;
            transform: translateY(8px);
            animation: aitfPillIn 0.4s 0.1s ease-out forwards;
        }

        .aitf-logo-abbr {
            font-size: clamp(2.8rem, 9vw, 5.2rem);
            font-weight: 800;
            line-height: 1.05;
            letter-spacing: -0.02em;
            margin: 0 0 4px 0;
            position: relative;
            display: inline-block;
            opacity: 0;
            transform: translateY(20px) scale(1.1);
            animation: aitfLogoBounceIn 0.9s 0.2s cubic-bezier(0.18, 0.89, 0.32, 1.28) forwards,
                aitfLogoBreath 7s 1.4s ease-in-out infinite;
            color: transparent;
        }

        .aitf-logo-abbr::before {
            content: 'A';
            position: absolute;
            left: -0.1em;
            /* Slight left adjustment */
            top: 0;
            color: #8B2F2F;
            /* Solid reddish-brown for A */
            -webkit-text-fill-color: #8B2F2F;
        }

        .aitf-logo-abbr::after {
            content: 'IT';
            position: absolute;
            left: 0.95em;
            /* Position after A */
            top: 0;
            color: #1A2A5A;
            /* Dark blue for IT */
            -webkit-text-fill-color: #1A2A5A;
            letter-spacing: 0.05em;
        }

        .aitf-logo-abbr span {
            position: absolute;
            left: 2.2em;
            /* Moved F even closer to IT */
            top: 0;
            color: #8B2F2F;
            /* Reddish-brown for F */
            -webkit-text-fill-color: #8B2F2F;
            margin-left: -0.1em;
            /* Fine-tune position */
        }

        .aitf-full-form-text {
            color: #e5e7eb;
            font-size: 1.1rem;
            margin-top: 0.5rem;
            font-weight: 400;
            letter-spacing: 0.03em;
            text-align: center;
            opacity: 0.9;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .aitf-full-form {
            margin-top: 1rem;
            display: flex;
            justify-content: center;
            gap: 0.7rem;
            flex-wrap: wrap;
            font-size: clamp(0.95rem, 1.6vw, 1.15rem);
            font-weight: 500;
            color: #e5e7eb;
            letter-spacing: 0.08em;
        }

        .aitf-word {
            display: inline-block;
            opacity: 0;
        }

        .word-1 {
            animation: aitfWordSlideUp 0.55s 1.1s cubic-bezier(0.16, 0.84, 0.44, 1) forwards,
                aitfWordDrift 9s 3s ease-in-out infinite;
        }

        .word-2 {
            animation: aitfWordFadeScale 0.55s 1.55s cubic-bezier(0.16, 0.84, 0.44, 1) forwards,
                aitfWordDrift 9s 3.4s ease-in-out infinite;
        }

        .word-3 {
            opacity: 1;
            overflow: hidden;
            white-space: nowrap;
            max-width: 0ch;
            animation: aitfTypeIn 0.9s 2s steps(9, end) forwards,
                aitfWordDrift 9s 3.8s ease-in-out infinite;
        }

        .word-4 {
            animation: aitfWordHighlight 0.6s 2.9s cubic-bezier(0.16, 0.84, 0.44, 1) forwards,
                aitfWordDrift 9s 4.2s ease-in-out infinite;
        }

        .aitf-meta {
            margin-top: 1.1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.7rem;
            padding: 0.3rem 0.6rem;
            font-size: 0.78rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(226, 232, 240, 0.85);
            opacity: 0;
            transform: translateY(10px);
            animation: aitfMetaIn 0.55s 3.1s cubic-bezier(0.16, 0.84, 0.44, 1) forwards,
                aitfMetaGlow 8s 4s ease-in-out infinite;
        }

        .meta-dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: radial-gradient(circle, #38bdf8, transparent 70%);
            box-shadow: 0 0 12px rgba(56, 189, 248, 0.9);
        }

        .meta-separator {
            width: 42px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(148, 163, 184, 0.8), transparent);
            opacity: 0.8;
        }

        .meta-label {
            white-space: nowrap;
        }

        .aitf-underline {
            margin-top: 18px;
            width: 72%;
            max-width: 420px;
            margin-left: auto;
            margin-right: auto;
            background: linear-gradient(90deg, transparent, rgba(37, 99, 235, 0.25), rgba(56, 189, 248, 0.9), rgba(37, 99, 235, 0.6), transparent);
        }

        .aitf-underline::after {
            animation: slide 2.4s ease-out infinite;
            opacity: 0.95;
        }

        .welcome-3d-underline {
            height: 3px;
            width: 100%;
            background: linear-gradient(90deg, transparent, #4fc3f7, transparent);
            margin: 10px auto;
            position: relative;
            overflow: hidden;
        }

        .welcome-3d-underline::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
            animation: slide 2s linear infinite;
        }

        .welcome-3d-glow {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at center, rgba(79, 195, 247, 0.1), transparent 70%);
            z-index: 1;
            animation: pulse 4s ease-in-out infinite;
        }

        .welcome-3d-particles {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1;
            overflow: hidden;
            border-radius: 20px;
        }

        .welcome-3d-particles::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            right: -50%;
            bottom: -50%;
            background:
                radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 80% 70%, rgba(79, 195, 247, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 40% 60%, rgba(255, 255, 255, 0.1) 0%, transparent 20%);
            animation: rotate 60s linear infinite;
            opacity: 0.5;
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        @keyframes slide {
            0% {
                left: -100%;
            }

            100% {
                left: 100%;
            }
        }

        @keyframes colorChange {
            0% {
                color: #ffffff;
                text-shadow: 0 0 10px #ffffff, 0 0 20px #ffffff, 0 0 30px #00aaff, 0 0 40px #00aaff;
                border-color: #00aaff;
            }

            33% {
                color: #ffff00;
                text-shadow: 0 0 10px #ffff00, 0 0 20px #ffff00, 0 0 30px #ffaa00, 0 0 40px #ffaa00;
                border-color: #ffaa00;
            }

            66% {
                color: #ff9900;
                text-shadow: 0 0 10px #ff9900, 0 0 20px #ff9900, 0 0 30px #ff5500, 0 0 40px #ff5500;
                border-color: #ff5500;
            }

            100% {
                color: #ffffff;
                text-shadow: 0 0 10px #ffffff, 0 0 20px #ffffff, 0 0 30px #00aaff, 0 0 40px #00aaff;
                border-color: #00aaff;
            }
        }

        @keyframes borderGlow {

            0%,
            100% {
                box-shadow: 0 0 10px 3px rgba(0, 170, 255, 0.8),
                    0 0 20px 6px rgba(0, 140, 255, 0.5),
                    0 0 30px 9px rgba(0, 110, 255, 0.3);
            }

            50% {
                box-shadow: 0 0 15px 5px rgba(0, 200, 255, 0.9),
                    0 0 25px 10px rgba(0, 170, 255, 0.6),
                    0 0 35px 15px rgba(0, 140, 255, 0.3);
            }
        }

        .animate-welcome {
            animation: colorChange 6s infinite ease-in-out !important;
            display: inline-block !important;
            padding: 20px 40px !important;
            font-weight: 800 !important;
            font-size: 3.2rem !important;
            letter-spacing: 1px;
            border: 3px solid #00aaff;
            border-radius: 15px;
            background:
                radial-gradient(circle at 10px 10px, rgba(255, 255, 255, 0.1) 2px, transparent 0) 0 0 / 30px 30px,
                radial-gradient(circle at 25px 25px, rgba(255, 255, 255, 0.1) 2px, transparent 0) 15px 15px / 30px 30px,
                rgba(0, 0, 0, 0.3) !important;
            backdrop-filter: blur(5px);
            transition: all 0.5s ease;
            position: relative;
            overflow: hidden;
            animation: borderGlow 4s infinite alternate !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .animate-welcome:hover {
            transform: scale(1.05) !important;
            background:
                radial-gradient(circle at 10px 10px, rgba(255, 255, 255, 0.15) 2px, transparent 0) 0 0 / 30px 30px,
                radial-gradient(circle at 25px 25px, rgba(255, 255, 255, 0.15) 2px, transparent 0) 15px 15px / 30px 30px,
                rgba(0, 0, 0, 0.4) !important;
            box-shadow: 0 0 30px rgba(0, 200, 255, 0.8) !important;
        }
    </style>

    <!-- GSAP & ScrollTrigger for AITF Journey -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

    <script>
        // GSAP Initialization for AITF Journey - Horizontal Scroll
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof gsap !== 'undefined') {
                gsap.registerPlugin(ScrollTrigger);

                // --- Dynamic Legacy Counter ---
                const counter = document.getElementById('legacy-counter');
                const plus = document.getElementById('legacy-plus');

                const updateCounter = (val) => {
                    counter.innerText = Math.floor(val);
                    // Color Logic
                    if (val <= 10) {
                        counter.style.color = '#3b82f6'; // Blue (Starting Phase)
                    } else if (val <= 20) {
                        counter.style.color = '#10b981'; // Green (Expansion Phase)
                    } else {
                        counter.style.color = '#b8860b'; // Gold (Excellence Phase)
                    }
                };

                // Counter Animation with ScrollTrigger
                ScrollTrigger.create({
                    trigger: ".legacy-highlight",
                    start: "top 80%",
                    onEnter: () => {
                        gsap.to({ val: 0 }, {
                            val: 25,
                            duration: 2.5,
                            ease: "power2.out",
                            onUpdate: function () { updateCounter(this.targets()[0].val); },
                            onComplete: () => { gsap.to(plus, { opacity: 1, x: 10, duration: 0.5 }); }
                        });
                    },
                    onLeaveBack: () => {
                        // Reset when scrolling back up so it re-counts
                        gsap.set(plus, { opacity: 0, x: 0 });
                        updateCounter(0);
                    }
                });

                // --- Horizontal Scroll Logic ---
                if (window.innerWidth > 991) {
                    const track = document.querySelector('.milestones-track');
                    const wrapper = document.querySelector('.horizontal-timeline-wrapper');

                    // Pin the section and scroll track horizontally
                    gsap.to(track, {
                        x: () => -(track.scrollWidth - wrapper.offsetWidth),
                        ease: "none",
                        scrollTrigger: {
                            trigger: "#journey-scroll-section",
                            start: "top top",
                            end: () => "+=" + (track.scrollWidth - wrapper.offsetWidth),
                            scrub: 1,
                            pin: true,
                            anticipatePin: 1,
                            invalidateOnRefresh: true
                        }
                    });

                    // Sync spine progress with horizontal scroll
                    gsap.to('.timeline-spine-progress', {
                        width: "100%",
                        ease: "none",
                        scrollTrigger: {
                            trigger: "#journey-scroll-section",
                            start: "top top",
                            end: () => "+=" + (track.scrollWidth - wrapper.offsetWidth),
                            scrub: 1
                        }
                    });

                    // Activate items as they pass the center
                    const milestoneItems = document.querySelectorAll('.milestone-item');
                    milestoneItems.forEach((item) => {
                        ScrollTrigger.create({
                            trigger: item,
                            containerAnimation: gsap.to(track, { x: () => -(track.scrollWidth - wrapper.offsetWidth), ease: "none" }), // Needs to reference the same animation
                            start: "left center",
                            end: "right center",
                            onEnter: () => item.classList.add('active'),
                            onLeaveBack: () => item.classList.remove('active'),
                        });

                        // Fallback simple activation if containerAnimation is tricky in this env
                        gsap.to(item, {
                            scrollTrigger: {
                                trigger: item,
                                start: "left center",
                                containerAnimation: gsap.getProperty(track, "x"), // simplified
                                onEnter: () => item.classList.add('active'),
                            }
                        });
                    });

                    // Direct activation based on track position (more reliable)
                    ScrollTrigger.create({
                        trigger: "#journey-scroll-section",
                        start: "top top",
                        end: () => "+=" + (track.scrollWidth - wrapper.offsetWidth),
                        scrub: 1,
                        onUpdate: (self) => {
                            const progress = self.progress;
                            const items = document.querySelectorAll('.milestone-item');
                            const activeIndex = Math.floor(progress * items.length);
                            items.forEach((item, i) => {
                                if (i <= activeIndex) item.classList.add('active');
                                else item.classList.remove('active');
                            });
                        }
                    });

                } else {
                    // Vertical fallback for mobile
                    const milestoneItems = document.querySelectorAll('.milestone-item');
                    milestoneItems.forEach((item) => {
                        gsap.to(item, {
                            scrollTrigger: {
                                trigger: item,
                                start: "top 80%",
                                onEnter: () => item.classList.add('active')
                            }
                        });
                    });

                    gsap.to('.timeline-spine-progress', {
                        height: "100%",
                        ease: "none",
                        scrollTrigger: {
                            trigger: ".horizontal-timeline-wrapper",
                            start: "top 70%",
                            end: "bottom 70%",
                            scrub: 1
                        }
                    });
                }

                // Bar Charts Animation
                const barFills = document.querySelectorAll('.bar-fill[data-width]');
                barFills.forEach(fill => {
                    gsap.to(fill, {
                        width: fill.getAttribute('data-width'),
                        duration: 1.5,
                        ease: 'power2.out',
                        scrollTrigger: {
                            trigger: fill,
                            start: 'top 90%'
                        }
                    });
                });

                // Histogram Bars Animation
                const histogramBars = document.querySelectorAll('.histogram-bar');
                histogramBars.forEach((bar, index) => {
                    gsap.to(bar, {
                        height: bar.getAttribute('data-height'),
                        duration: 1,
                        delay: index * 0.1,
                        ease: 'power2.out',
                        scrollTrigger: {
                            trigger: '.histogram-container',
                            start: 'top 85%'
                        }
                    });
                });
            }
        });

        // Animate welcome text characters
        document.addEventListener('DOMContentLoaded', function () {
            const chars = document.querySelectorAll('.welcome-3d-char');
            chars.forEach((char, index) => {
                // Random delay for each character
                const delay = Math.random() * 0.5;
                char.style.animation = `float 3s ease-in-out ${delay}s infinite`;

                // Add mouse move effect
                char.addEventListener('mousemove', (e) => {
                    const rect = char.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;

                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;

                    const angleX = (y - centerY) * 0.05;
                    const angleY = (centerX - x) * 0.05;

                    char.style.transform = `rotateX(${angleX}deg) rotateY(${angleY}deg) translateZ(20px)`;
                });

                char.addEventListener('mouseleave', () => {
                    char.style.transform = 'rotateX(0) rotateY(0) translateZ(0)';
                });
            });

            // Create floating particles
            const particlesContainer = document.querySelector('.welcome-3d-particles');
            if (particlesContainer) {
                for (let i = 0; i < 20; i++) {
                    const particle = document.createElement('div');
                    particle.className = 'floating-particle';
                    particle.style.cssText = `
                        position: absolute;
                        width: ${Math.random() * 10 + 5}px;
                        height: ${Math.random() * 10 + 5}px;
                        background: rgba(255, 255, 255, ${Math.random() * 0.5 + 0.1});
                        border-radius: 50%;
                        left: ${Math.random() * 100}%;
                        top: ${Math.random() * 100}%;
                        animation: float ${Math.random() * 5 + 5}s ease-in-out infinite;
                        animation-delay: ${Math.random() * 5}s;
                        opacity: ${Math.random() * 0.5 + 0.1};
                    `;
                    particlesContainer.appendChild(particle);
                }
            }
        });

        // Add smooth hover effect for all buttons with class btn-light
        document.querySelectorAll('.btn-light').forEach(button => {
            button.addEventListener('mouseover', function () {
                this.style.transform = 'translateY(-3px)';
                this.style.boxShadow = '0 7px 20px rgba(0, 0, 0, 0.2)';
            });

            button.addEventListener('mouseout', function () {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 4px 15px rgba(0, 0, 0, 0.1)';
            });
        });

        // Audit Flow Dropdown Toggle Function
        function toggleStep(element) {
            const step = element.closest('.flow-step');
            const stepNumber = parseInt(step.dataset.step);
            const allSteps = document.querySelectorAll('.flow-step');

            // Check if this step is already active
            const isAlreadyActive = step.classList.contains('active');

            // Close all steps first
            allSteps.forEach(otherStep => {
                otherStep.classList.remove('active');
            });

            // If the clicked step was not already active, activate it and all previous steps
            if (!isAlreadyActive) {
                allSteps.forEach(otherStep => {
                    const otherStepNumber = parseInt(otherStep.dataset.step);
                    if (otherStepNumber <= stepNumber) {
                        otherStep.classList.add('active');
                    }
                });
            }

            // Update progress bar based on highest step number
            updateProgress(isAlreadyActive ? 0 : stepNumber);
        }

        // Update progress bar
        function updateProgress(highestStepNumber) {
            const totalSteps = document.querySelectorAll('.flow-step').length;
            const progressPercent = (highestStepNumber / totalSteps) * 100;

            const progressFill = document.querySelector('.progress-fill');
            const currentStepText = document.querySelector('.current-step');

            if (progressFill) {
                progressFill.style.width = progressPercent + '%';
            }

            if (currentStepText) {
                currentStepText.textContent = `Step ${highestStepNumber}`;
            }
        }

        // Initialize all steps as closed, only step 1 active
        document.addEventListener('DOMContentLoaded', function () {
            const allSteps = document.querySelectorAll('.flow-step');
            // Remove active class from all steps first
            allSteps.forEach(step => {
                step.classList.remove('active');
            });

            // Then add active class only to step 1
            const firstStep = document.querySelector('.flow-step[data-step="1"]');
            if (firstStep) {
                firstStep.classList.add('active');
                updateProgress(1);
            }
        });
    </script>
</body>

</html>
