<?php
$pageTitle = "Career & Opportunity | AITF";
$pageDescription = "Explore career opportunities, auditor empanelment, and job openings at AITF Management Certification.";
$canonicalUrl = "https://www.aitf.co.in/career";
$turnstileHelperPath = __DIR__ . '/include/turnstile_helper.php';
if (file_exists($turnstileHelperPath)) {
    require_once $turnstileHelperPath;
}

$turnstileConfig = function_exists('aitf_get_turnstile_config') ? aitf_get_turnstile_config() : [
    'site_key' => '',
    'secret_key' => ''
];
$turnstileReady = function_exists('aitf_is_turnstile_ready') ? aitf_is_turnstile_ready() : false;

$errorMessage = isset($_GET['error']) ? trim($_GET['error']) : '';
$successApplicant = isset($_GET['success']) && $_GET['success'] == '1' ? trim($_GET['applicant'] ?? '') : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include __DIR__ . '/headsource.php'; ?>
    <?php if ($turnstileReady): ?>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <?php endif; ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --c-primary: #1d4ed8;
            --c-primary-hover: #1e40af;
            --c-primary-light: #eff6ff;
            --c-primary-subtle: #dbeafe;
            --c-accent: #0284c7;
            --c-dark: #091322;
            --c-dark-surface: #0f1f38;
            --c-text-heading: #0f172a;
            --c-text-body: #334155;
            --c-text-muted: #64748b;
            --c-border: #e2e8f0;
            --c-border-hover: #cbd5e1;
            --c-bg-page: #f8fafc;
            --c-bg-card: #ffffff;
            --c-radius: 16px;
            --c-radius-sm: 10px;
            --c-shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.04);
            --c-shadow: 0 10px 30px -5px rgba(15, 23, 42, 0.06), 0 4px 6px -2px rgba(15, 23, 42, 0.02);
            --c-shadow-hover: 0 20px 35px -5px rgba(15, 23, 42, 0.10), 0 8px 12px -4px rgba(15, 23, 42, 0.03);
            --c-transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--c-bg-page);
            color: var(--c-text-body);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* Transparent Top Navigation Integration */
        #home .top-header:not(.scrolled) {
            --nav-bg: transparent;
            --nav-text: #f1f5f9;
            --nav-hover-text: #ffffff;
            --nav-hover-bg: rgba(255, 255, 255, 0.12);
            background: transparent !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12) !important;
            box-shadow: none !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }

        #home .top-header:not(.scrolled) .navbar a:not(.login-btn),
        #home .top-header:not(.scrolled) .navbar .dropdown>a,
        #home .top-header:not(.scrolled) .nav-primary .nav-link,
        #home .top-header:not(.scrolled) .header-nav .nav-link:not(.login-btn) {
            color: #f1f5f9 !important;
        }

        #home .top-header:not(.scrolled) .mobile-toggle-bar {
            background-color: #f1f5f9 !important;
        }

        /* Page Container */
        .career-wrap {
            width: 100%;
            overflow-x: hidden;
        }

        .career-container {
            width: min(1200px, calc(100% - 40px));
            margin: 0 auto;
            position: relative;
        }

        /* Section Commons */
        .career-section {
            padding: 76px 0;
            position: relative;
        }

        .career-section.alt-bg {
            background-color: #ffffff;
        }

        .career-section-header {
            max-width: 680px;
            margin: 0 auto 48px;
            text-align: center;
        }

        .career-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 14px;
        }

        .career-pill.light {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.22);
            color: #e0f2fe;
            backdrop-filter: blur(8px);
        }

        .career-pill.primary {
            background: var(--c-primary-light);
            border: 1px solid var(--c-primary-subtle);
            color: var(--c-primary);
        }

        .career-section-header h2 {
            margin: 0 0 12px;
            font-size: clamp(28px, 3.4vw, 40px);
            font-weight: 800;
            color: var(--c-text-heading);
            letter-spacing: -0.025em;
            line-height: 1.2;
        }

        .career-section-header p {
            margin: 0;
            color: var(--c-text-muted);
            font-size: 16px;
            line-height: 1.65;
        }

        /* 1. HERO SECTION */
        .career-hero {
            padding: 154px 0 76px;
            background:
                radial-gradient(circle at 18% 22%, rgba(59, 130, 246, 0.28), transparent 38%),
                radial-gradient(circle at 85% 15%, rgba(14, 165, 233, 0.22), transparent 32%),
                linear-gradient(140deg, #091322 0%, #0f2244 55%, #15325c 100%);
            color: #ffffff;
            position: relative;
            isolation: isolate;
        }

        .career-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.04) 1px, transparent 1px);
            background-size: 36px 36px;
            mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.8) 0%, transparent 92%);
            -webkit-mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.8) 0%, transparent 92%);
            pointer-events: none;
            z-index: 0;
        }

        .hero-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(320px, 0.85fr);
            gap: 44px;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .hero-content h1 {
            margin: 0 0 16px;
            font-size: clamp(32px, 4.2vw, 50px);
            font-weight: 800;
            line-height: 1.14;
            letter-spacing: -0.03em;
            color: #ffffff;
        }

        .hero-content h1 span {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-content p {
            margin: 0 0 28px;
            font-size: 17px;
            line-height: 1.7;
            color: #cbd5e1;
            max-width: 580px;
        }

        .hero-cta-group {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .btn-modern {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 12px 24px;
            border-radius: 9999px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: var(--c-transition);
            border: 1px solid transparent;
            line-height: 1.4;
        }

        .btn-modern:hover {
            text-decoration: none;
            transform: translateY(-2px);
        }

        .btn-modern.primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            box-shadow: 0 10px 24px -4px rgba(37, 99, 235, 0.45);
        }

        .btn-modern.primary:hover {
            box-shadow: 0 14px 28px -2px rgba(37, 99, 235, 0.55);
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #ffffff;
        }

        .btn-modern.glass {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            backdrop-filter: blur(10px);
        }

        .btn-modern.glass:hover {
            background: rgba(255, 255, 255, 0.16);
            border-color: rgba(255, 255, 255, 0.35);
            color: #ffffff;
        }

        /* Hero Visual Showcase Card */
        .hero-visual-card {
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: var(--c-radius);
            padding: 14px;
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.35);
            transition: var(--c-transition);
        }

        .hero-visual-card:hover {
            border-color: rgba(255, 255, 255, 0.28);
            transform: translateY(-3px);
            box-shadow: 0 26px 55px rgba(0, 0, 0, 0.45);
        }

        .hero-visual-media {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            background: #f7ede2;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
        }

        .hero-career-img {
            width: 100%;
            height: auto;
            max-height: 310px;
            object-fit: cover;
            display: block;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .hero-visual-card:hover .hero-career-img {
            transform: scale(1.03);
        }

        .hero-floating-chip {
            position: absolute;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
            z-index: 2;
            user-select: none;
        }

        .hero-floating-chip.bottom-right {
            bottom: 12px;
            right: 12px;
            background: rgba(37, 99, 235, 0.90);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .hero-visual-footer {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-top: 12px;
        }

        .hero-visual-stat {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: var(--c-radius-sm);
            padding: 10px 6px;
            text-align: center;
            transition: var(--c-transition);
        }

        .hero-visual-stat:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.22);
        }

        .hero-visual-stat i {
            font-size: 15px;
            color: #38bdf8;
            margin-bottom: 3px;
            display: block;
        }

        .hero-visual-stat strong {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.25;
        }

        .hero-visual-stat span {
            display: block;
            font-size: 10.5px;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* 2. WHY JOIN AITF */
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 22px;
        }

        .value-card {
            background: var(--c-bg-card);
            border: 1px solid var(--c-border);
            border-radius: var(--c-radius);
            padding: 28px 24px;
            box-shadow: var(--c-shadow-sm);
            transition: var(--c-transition);
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .value-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--c-shadow-hover);
            border-color: #cbd5e1;
        }

        .value-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: var(--c-primary-light);
            color: var(--c-primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 18px;
            transition: var(--c-transition);
        }

        .value-card:hover .value-icon {
            background: var(--c-primary);
            color: #ffffff;
        }

        .value-card h3 {
            margin: 0 0 10px;
            font-size: 18px;
            font-weight: 700;
            color: var(--c-text-heading);
            letter-spacing: -0.01em;
        }

        .value-card p {
            margin: 0;
            font-size: 14.5px;
            color: var(--c-text-muted);
            line-height: 1.65;
        }

        /* 3. APPLICATION FORM SECTION ("APPLY FOR JOB") */
        .apply-layout {
            display: grid;
            grid-template-columns: minmax(0, 0.82fr) minmax(360px, 1.18fr);
            gap: 36px;
            align-items: start;
        }

        .apply-sidebar {
            background: #ffffff;
            border: 1px solid var(--c-border);
            border-radius: var(--c-radius);
            padding: 32px 28px;
            box-shadow: var(--c-shadow-sm);
        }

        .apply-sidebar h3 {
            margin: 0 0 10px;
            font-size: 22px;
            font-weight: 800;
            color: var(--c-text-heading);
            letter-spacing: -0.02em;
        }

        .apply-sidebar p {
            margin: 0 0 24px;
            font-size: 14.5px;
            color: var(--c-text-muted);
            line-height: 1.6;
        }

        .process-steps {
            display: flex;
            flex-direction: column;
            gap: 18px;
            margin-bottom: 28px;
        }

        .step-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        .step-num {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--c-primary-light);
            color: var(--c-primary);
            font-size: 12px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .step-text strong {
            display: block;
            font-size: 14px;
            font-weight: 700;
            color: var(--c-text-heading);
            margin-bottom: 2px;
        }

        .step-text span {
            display: block;
            font-size: 13px;
            color: var(--c-text-muted);
            line-height: 1.45;
        }

        .apply-help-card {
            background: #f8fafc;
            border: 1px dashed var(--c-border);
            border-radius: var(--c-radius-sm);
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .apply-help-card i {
            font-size: 20px;
            color: var(--c-primary);
        }

        .apply-help-card div {
            font-size: 13px;
            color: var(--c-text-muted);
            line-height: 1.4;
        }

        .apply-help-card a {
            color: var(--c-primary);
            font-weight: 600;
            text-decoration: none;
        }

        .apply-form-card {
            background: #ffffff;
            border: 1px solid var(--c-border);
            border-radius: var(--c-radius);
            padding: 34px 32px;
            box-shadow: var(--c-shadow);
        }

        .form-heading {
            margin-bottom: 24px;
            padding-bottom: 18px;
            border-bottom: 1px solid var(--c-border);
        }

        .form-heading h3 {
            margin: 0 0 4px;
            font-size: 22px;
            font-weight: 800;
            color: var(--c-text-heading);
        }

        .form-heading p {
            margin: 0;
            font-size: 14px;
            color: var(--c-text-muted);
        }

        .form-alert {
            border-radius: var(--c-radius-sm);
            padding: 12px 16px;
            margin-bottom: 20px;
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 6px;
        }

        .form-label span.req {
            color: #ef4444;
        }

        .form-label span.opt {
            font-weight: 500;
            color: #94a3b8;
            font-size: 12px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
            pointer-events: none;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            border: 1px solid var(--c-border);
            border-radius: var(--c-radius-sm);
            padding: 11px 14px;
            font-family: inherit;
            font-size: 14.5px;
            color: var(--c-text-heading);
            background: #ffffff;
            outline: none;
            transition: var(--c-transition);
        }

        .input-wrap .form-input,
        .input-wrap .form-select {
            padding-left: 38px;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            border-color: var(--c-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .form-textarea {
            min-height: 85px;
            resize: vertical;
        }

        /* Qualification Radio Grid */
        .qualification-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 10px;
            margin-bottom: 8px;
        }

        .qualification-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border: 1px solid var(--c-border);
            border-radius: var(--c-radius-sm);
            background: #f8fafc;
            cursor: pointer;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--c-text-body);
            transition: var(--c-transition);
            user-select: none;
        }

        .qualification-pill:hover {
            border-color: var(--c-primary);
            background: var(--c-primary-light);
        }

        .qualification-pill input[type="radio"] {
            margin: 0;
            accent-color: var(--c-primary);
            cursor: pointer;
        }

        .qualification-pill.active {
            border-color: var(--c-primary);
            background: var(--c-primary-light);
            color: var(--c-primary);
            font-weight: 700;
        }

        .other-qualification-input {
            margin-top: 8px;
            display: none;
        }

        .other-qualification-input.show {
            display: block;
        }

        /* Email & OTP Verification Layout */
        .email-otp-box {
            background: #f8fafc;
            border: 1px solid var(--c-border);
            border-radius: var(--c-radius-sm);
            padding: 16px;
            margin-bottom: 18px;
        }

        .email-input-group {
            display: flex;
            gap: 10px;
        }

        .email-input-group .input-wrap {
            flex: 1;
        }

        .btn-send-otp {
            padding: 10px 18px;
            border: 1px solid var(--c-primary);
            border-radius: var(--c-radius-sm);
            background: var(--c-primary-light);
            color: var(--c-primary);
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--c-transition);
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-send-otp:hover:not(:disabled) {
            background: var(--c-primary);
            color: #ffffff;
        }

        .btn-send-otp:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            border-color: #cbd5e1;
            color: #64748b;
            background: #f1f5f9;
        }

        .otp-verify-row {
            display: none;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px dashed var(--c-border);
        }

        .otp-verify-row.show {
            display: block;
        }

        .otp-inputs-flex {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .otp-code-input {
            max-width: 180px;
            letter-spacing: 4px;
            font-size: 16px;
            font-weight: 800;
            text-align: center;
        }

        .btn-verify-otp {
            padding: 10px 18px;
            border: 0;
            border-radius: var(--c-radius-sm);
            background: #16a34a;
            color: #ffffff;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--c-transition);
            white-space: nowrap;
        }

        .btn-verify-otp:hover {
            background: #15803d;
        }

        .otp-status-msg {
            font-size: 13px;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .otp-status-msg.success {
            color: #16a34a;
            font-weight: 600;
        }

        .otp-status-msg.error {
            color: #dc2626;
            font-weight: 600;
        }

        .otp-status-msg.info {
            color: var(--c-primary);
            font-weight: 500;
        }

        .verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 9999px;
            background: #dcfce7;
            color: #166534;
            font-size: 12px;
            font-weight: 700;
            margin-top: 8px;
        }

        /* File Upload Custom Widget */
        .file-dropzone {
            border: 1.5px dashed var(--c-border);
            border-radius: var(--c-radius-sm);
            padding: 18px;
            background: #f8fafc;
            text-align: center;
            cursor: pointer;
            transition: var(--c-transition);
            position: relative;
        }

        .file-dropzone:hover,
        .file-dropzone.dragover {
            border-color: var(--c-primary);
            background: var(--c-primary-light);
        }

        .file-dropzone input[type="file"] {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
        }

        .file-dropzone-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            pointer-events: none;
        }

        .file-dropzone-icon {
            font-size: 24px;
            color: var(--c-primary);
            margin-bottom: 2px;
        }

        .file-dropzone-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--c-text-heading);
        }

        .file-dropzone-hint {
            font-size: 12px;
            color: var(--c-text-muted);
        }

        .file-selected-badge {
            display: none;
            align-items: center;
            justify-content: space-between;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 8px 12px;
            margin-top: 8px;
            font-size: 13px;
            color: #166534;
            font-weight: 600;
        }

        .file-selected-badge.active {
            display: flex;
        }

        .file-selected-badge i {
            color: #16a34a;
            margin-right: 6px;
        }

        /* Captcha Wrap */
        .captcha-wrap {
            margin: 16px 0;
            display: flex;
            justify-content: flex-start;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            border: 0;
            border-radius: var(--c-radius-sm);
            padding: 13px 20px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            font-family: inherit;
            font-size: 15.5px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--c-transition);
            box-shadow: 0 8px 20px -2px rgba(37, 99, 235, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-submit:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -2px rgba(37, 99, 235, 0.45);
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        .btn-submit:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        /* 4. LEADERSHIP NOTE */
        .director-quote-box {
            background: linear-gradient(135deg, #091322 0%, #0f2244 100%);
            border-radius: var(--c-radius);
            padding: 36px 40px;
            color: #ffffff;
            box-shadow: var(--c-shadow);
            position: relative;
            overflow: hidden;
        }

        .director-quote-box::before {
            content: "“";
            position: absolute;
            right: 28px;
            bottom: -32px;
            font-size: 130px;
            font-family: Georgia, serif;
            color: rgba(255, 255, 255, 0.05);
            line-height: 1;
            pointer-events: none;
        }

        .director-quote-inner {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 28px;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .director-badge-circle {
            width: 72px;
            height: 72px;
            border-radius: 20px;
            background: linear-gradient(135deg, #2563eb 0%, #38bdf8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #ffffff;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.35);
        }

        .director-quote-content p {
            margin: 0 0 12px;
            font-size: 16px;
            line-height: 1.7;
            color: #e2e8f0;
            font-style: italic;
        }

        .director-quote-author {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .director-quote-author strong {
            font-size: 15px;
            color: #ffffff;
        }

        .director-quote-author span {
            font-size: 13px;
            color: #93c5fd;
        }

        /* 5. SUCCESS POPUP OVERLAY */
        .career-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: fadeIn 0.2s ease-out;
        }

        .career-modal-card {
            background: #ffffff;
            border-radius: var(--c-radius);
            max-width: 480px;
            width: 100%;
            padding: 36px 30px;
            text-align: center;
            position: relative;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25);
            border: 1px solid var(--c-border);
            animation: modalScaleUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .career-modal-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #dcfce7;
            color: #16a34a;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 18px;
        }

        .career-modal-card h2 {
            margin: 0 0 10px;
            font-size: 24px;
            font-weight: 800;
            color: var(--c-text-heading);
        }

        .career-modal-card p {
            margin: 0 0 24px;
            font-size: 14.5px;
            color: var(--c-text-muted);
            line-height: 1.6;
        }

        .career-modal-close {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 0;
            background: #f1f5f9;
            color: #64748b;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--c-transition);
        }

        .career-modal-close:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes modalScaleUp {
            from {
                opacity: 0;
                transform: scale(0.94) translateY(10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* 6. RESPONSIVE BREAKPOINTS */
        @media (max-width: 991px) {
            .hero-layout,
            .apply-layout {
                grid-template-columns: 1fr;
                gap: 34px;
            }

            .career-hero {
                padding: 135px 0 60px;
            }

            .career-section {
                padding: 60px 0;
            }

            .director-quote-inner {
                grid-template-columns: 1fr;
                text-align: center;
                justify-items: center;
            }
        }

        @media (max-width: 640px) {
            .career-hero {
                padding: 120px 0 50px;
            }

            .career-container {
                width: calc(100% - 28px);
            }

            .form-grid-2,
            .email-input-group {
                grid-template-columns: 1fr;
                flex-direction: column;
            }

            .apply-form-card {
                padding: 24px 18px;
            }

            .hero-cta-group {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-modern {
                width: 100%;
            }

            .director-quote-box {
                padding: 26px 20px;
            }
        }

        @media (max-width: 480px) {
            .hero-visual-footer {
                grid-template-columns: 1fr;
                gap: 6px;
            }

            .hero-visual-stat {
                display: flex;
                align-items: center;
                text-align: left;
                gap: 10px;
                padding: 8px 12px;
            }

            .hero-visual-stat i {
                margin-bottom: 0;
                font-size: 16px;
            }
        }
    </style>
</head>

<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>

    <div class="career-wrap">
        <?php if ($successApplicant !== ''): ?>
            <?php $safeApplicantName = htmlspecialchars($successApplicant, ENT_QUOTES, 'UTF-8'); ?>
            <div class="career-modal-backdrop" id="careerSuccessModal" role="dialog" aria-modal="true" aria-labelledby="careerSuccessTitle">
                <div class="career-modal-card">
                    <button class="career-modal-close" type="button" data-modal-close aria-label="Close dialog">&times;</button>
                    <div class="career-modal-icon"><i class="fa-solid fa-circle-check"></i></div>
                    <h2 id="careerSuccessTitle">Application Received</h2>
                    <p>Thank you, <strong><?php echo $safeApplicantName; ?></strong>. We have received your profile. Our assessment and technical onboarding team will review your credentials and get in touch shortly.</p>
                    <button class="btn-modern primary" type="button" data-modal-close style="width: 100%;">Done</button>
                </div>
            </div>
        <?php endif; ?>

        <!-- HERO SECTION -->
        <section class="career-hero">
            <div class="career-container hero-layout">
                <div class="hero-content">
                    <span class="career-pill light"><i class="fa-solid fa-briefcase"></i> Careers at AITF</span>
                    <h1>Shape the Future of <span>Quality & Assessment</span></h1>
                    <p>Join a accredited certification and assessment body. Work alongside experienced lead auditors and technical experts conducting independent assessments and evaluations across national and international standards.</p>
                    <div class="hero-cta-group">
                        <a class="btn-modern primary" href="#apply-for-job"><i class="fa-solid fa-paper-plane"></i> Apply for Job</a>
                        <a class="btn-modern glass" href="#why-aitf"><i class="fa-solid fa-circle-info"></i> Why Work With Us</a>
                    </div>
                </div>

                <div class="hero-visual-card">
                    <div class="hero-visual-media">
                        <img src="aitfImages/careerimage.jpg" alt="Career Growth at AITF" class="hero-career-img" width="1470" height="980" fetchpriority="high">
                        <div class="hero-floating-chip bottom-right">
                            <i class="fa-solid fa-certificate"></i> Accredited Body
                        </div>
                    </div>
                    <div class="hero-visual-footer">
                        <div class="hero-visual-stat">
                            <i class="fa-solid fa-graduation-cap"></i>
                            <strong>Training</strong>
                            <span>Technical & Auditing</span>
                        </div>
                        <div class="hero-visual-stat">
                            <i class="fa-solid fa-user-tie"></i>
                            <strong>Lead Auditors</strong>
                            <span>Experienced Team</span>
                        </div>
                        <div class="hero-visual-stat">
                            <i class="fa-solid fa-globe"></i>
                            <strong>ISO Standards</strong>
                            <span>Global Benchmarks</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- VALUE PILLARS SECTION -->
        <section class="career-section alt-bg" id="why-aitf">
            <div class="career-container">
                <div class="career-section-header">
                    <span class="career-pill primary"><i class="fa-solid fa-sparkles"></i> Professional Environment</span>
                    <h2>Build Your Auditing Career with AITF</h2>
                    <p>Gain structured training, work alongside qualified lead auditors, and assess international standards to establish compliance and safety for society.</p>
                </div>

                <div class="values-grid">
                    <div class="value-card">
                        <div class="value-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                        <h3>Auditor Training & Skill Development</h3>
                        <p>Receive comprehensive and structured training programs to continuously develop your auditing competence and advance your career in management system assessments.</p>
                    </div>

                    <div class="value-card">
                        <div class="value-icon"><i class="fa-solid fa-user-tie"></i></div>
                        <h3>Work with Qualified Lead Auditors</h3>
                        <p>Get the opportunity to work alongside highly qualified lead auditors and learn practical, on-field assessment and evaluation methodologies.</p>
                    </div>

                    <div class="value-card">
                        <div class="value-icon"><i class="fa-solid fa-globe"></i></div>
                        <h3>Internationally Recognized Standards</h3>
                        <p>Perform impartial assessments across global ISO frameworks including ISO 9001, ISO 14001, ISO 45001.</p>
                    </div>

                    <div class="value-card">
                        <div class="value-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <h3>Contribute to Society & Compliance</h3>
                        <p>Make a meaningful impact by assessing organizations and establishing safety, environmental, and quality compliance benchmarks for society.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- DIRECT APPLICATION FORM SECTION ("APPLY FOR JOB") -->
        <section class="career-section" id="apply-for-job">
            <div class="career-container apply-layout">
                <!-- Sidebar Guidelines -->
                <div class="apply-sidebar">
                    <span class="career-pill primary"><i class="fa-solid fa-file-signature"></i> Career Portal</span>
                    <h3>Apply for Job</h3>
                    <p>Join our team of qualified auditors and technical assessors. Submit your application below with verified contact details.</p>

                    <div class="process-steps">
                        <div class="step-item">
                            <div class="step-num">1</div>
                            <div class="step-text">
                                <strong>Basic Info & Qualification</strong>
                                <span>Fill your contact info and select your educational background.</span>
                            </div>
                        </div>
                        <div class="step-item">
                            <div class="step-num">2</div>
                            <div class="step-text">
                                <strong>Attach Resume (PDF)</strong>
                                <span>Upload your updated CV / Resume showing your credentials.</span>
                            </div>
                        </div>
                        <div class="step-item">
                            <div class="step-num">3</div>
                            <div class="step-text">
                                <strong>Email OTP Verification</strong>
                                <span>Verify your email with a 6-digit code to validate your submission.</span>
                            </div>
                        </div>
                        <div class="step-item">
                            <div class="step-num">4</div>
                            <div class="step-text">
                                <strong>Evaluation & Onboarding</strong>
                                <span>Our technical committee reviews your profile for scheduling.</span>
                            </div>
                        </div>
                    </div>

                    <div class="apply-help-card">
                        <i class="fa-solid fa-circle-question"></i>
                        <div>
                            Need assistance with your application?<br>
                            Email us at <a href="mailto:certification@aitf.co.in">certification@aitf.co.in</a>
                        </div>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="apply-form-card">
                    <div class="form-heading">
                        <h3>Apply for Job</h3>
                        <p>Please enter your information accurately. All fields marked with * are required.</p>
                    </div>

                    <?php if ($errorMessage !== ''): ?>
                        <div class="form-alert">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                    <?php endif; ?>

                    <form id="careerApplicationForm" action="submitJobApplication.php" method="post" enctype="multipart/form-data" autocomplete="on">
                        <!-- Full Name & Mobile Number -->
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label class="form-label" for="applicant_name">Full Name <span class="req">*</span></label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-user input-icon"></i>
                                    <input type="text" class="form-input" id="applicant_name" name="applicant_name" placeholder="e.g. Rahul Sharma" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="contact_no">Mobile Number <span class="req">*</span></label>
                                <div class="input-wrap">
                                    <i class="fa-solid fa-phone input-icon"></i>
                                    <input type="tel" class="form-input" id="contact_no" name="contact_no" placeholder="10-digit mobile number" required>
                                </div>
                            </div>
                        </div>

                        <!-- Qualification Selection -->
                        <div class="form-group">
                            <label class="form-label">Highest Qualification <span class="req">*</span></label>
                            <div class="qualification-grid">
                                <label class="qualification-pill" id="qualPillGrad">
                                    <input type="radio" name="qualification" value="Graduate" required onchange="handleQualificationChange(this)">
                                    <span>Graduate</span>
                                </label>
                                <label class="qualification-pill" id="qualPillPG">
                                    <input type="radio" name="qualification" value="Post Graduate" onchange="handleQualificationChange(this)">
                                    <span>Post Graduate</span>
                                </label>
                                <label class="qualification-pill" id="qualPillDip">
                                    <input type="radio" name="qualification" value="Diploma" onchange="handleQualificationChange(this)">
                                    <span>Diploma</span>
                                </label>
                                <label class="qualification-pill" id="qualPillOther">
                                    <input type="radio" name="qualification" value="Any other relevant" onchange="handleQualificationChange(this)">
                                    <span>Any other relevant</span>
                                </label>
                            </div>
                            <div class="other-qualification-input" id="otherQualBox">
                                <input type="text" class="form-input" id="qualification_other" name="qualification_other" placeholder="Please specify your degree / certification...">
                            </div>
                        </div>

                        <!-- Role / Area of Interest -->
                        <div class="form-group">
                            <label class="form-label" for="apply_for">Applying For Position <span class="req">*</span></label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-briefcase input-icon"></i>
                                <input type="text" class="form-input" id="apply_for" name="apply_for" placeholder="Enter position name you are interested in" required maxlength="100">
                            </div>
                        </div>

                        <!-- Resume Upload Custom Widget -->
                        <div class="form-group">
                            <label class="form-label" for="resume_file">Attach Your CV <span class="req">*</span></label>
                            <div class="file-dropzone" id="fileDropzone">
                                <input type="file" id="resume_file" name="resume" accept=".pdf" required onchange="handleFileSelect(this)">
                                <div class="file-dropzone-content" id="dropzonePrompt">
                                    <i class="fa-solid fa-cloud-arrow-up file-dropzone-icon"></i>
                                    <span class="file-dropzone-title">Click to upload or drag & drop CV</span>
                                    <span class="file-dropzone-hint">PDF format only (Maximum file size: 5MB)</span>
                                </div>
                            </div>
                            <div class="file-selected-badge" id="fileSelectedBadge">
                                <div><i class="fa-solid fa-file-pdf"></i> <span id="fileNameDisplay">file.pdf</span></div>
                                <span id="fileSizeDisplay" style="color: #64748b; font-size: 12px;"></span>
                            </div>
                        </div>

                        <!-- Short Message / Experience -->
                        <div class="form-group">
                            <label class="form-label" for="message">Short Message / Key Competencies <span class="opt">(Optional)</span></label>
                            <textarea class="form-textarea" id="message" name="message" placeholder="Briefly mention your standard qualifications (e.g. Lead Auditor ISO 9001), total years of experience, or industry sectors..."></textarea>
                        </div>

                        <!-- Email & OTP Verification Box -->
                        <div class="email-otp-box">
                            <label class="form-label" for="email">Email Address (Verification Required) <span class="req">*</span></label>
                            <div class="email-input-group">
                                <div class="input-wrap">
                                    <i class="fa-solid fa-envelope input-icon"></i>
                                    <input type="email" class="form-input" id="email" name="email" placeholder="name@example.com" required oninput="handleEmailInput()">
                                </div>
                                <button class="btn-send-otp" id="btnSendOtp" type="button" onclick="sendEmailOtp()">
                                    <i class="fa-solid fa-key"></i> <span id="btnOtpText">Send OTP</span>
                                </button>
                            </div>

                            <!-- OTP Enter Row -->
                            <div class="otp-verify-row" id="otpVerifyRow">
                                <label class="form-label" for="email_otp">Enter 6-Digit OTP received on email <span class="req">*</span></label>
                                <div class="otp-inputs-flex">
                                    <input type="text" class="form-input otp-code-input" id="email_otp" name="email_otp" maxlength="6" placeholder="------" autocomplete="one-time-code">
                                    <button class="btn-verify-otp" id="btnVerifyOtp" type="button" onclick="verifyEmailOtp()">Verify OTP</button>
                                </div>
                                <div id="otpCountdownText" style="font-size: 12px; color: #64748b; margin-top: 6px;"></div>
                            </div>

                            <div id="otpFeedbackMsg" class="otp-status-msg" style="display: none;"></div>

                            <div id="emailVerifiedBadge" class="verified-badge" style="display: none;">
                                <i class="fa-solid fa-circle-check"></i> Email Verified Successfully
                            </div>
                        </div>

                        <!-- Captcha -->
                        <?php if ($turnstileReady): ?>
                            <div class="captcha-wrap">
                                <div class="cf-turnstile"
                                    data-sitekey="<?php echo htmlspecialchars($turnstileConfig['site_key'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-callback="enableSubmitBtn"
                                    data-expired-callback="disableSubmitBtn"
                                    data-error-callback="disableSubmitBtn"></div>
                            </div>
                        <?php endif; ?>

                        <!-- Submit Button (Disabled until OTP verified) -->
                        <button class="btn-submit" id="submitBtn" type="submit" disabled style="opacity: 0.6; cursor: not-allowed;">
                            <i class="fa-solid fa-paper-plane"></i> Submit Job Application
                        </button>
                        <div id="submitNotice" style="font-size: 12px; color: #64748b; text-align: center; margin-top: 8px;">
                            <i class="fa-solid fa-shield-halved"></i> Please verify your email with OTP to enable submission.
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <!-- LEADERSHIP MESSAGE SECTION -->
        <section class="career-section alt-bg" style="padding-top: 20px; padding-bottom: 50px;">
            <div class="career-container">
                <div class="director-quote-box">
                    <div class="director-quote-inner">
                        <div class="director-badge-circle"><i class="fa-solid fa-user-tie"></i></div>
                        <div class="director-quote-content">
                            <p>"At AITF, we believe that credible certifications are built by auditors and assessors who are impartial, disciplined, and committed to professional integrity. If you bring the dedication to evaluate organisations and uphold standard requirements, you will find here a platform that values your technical expertise."</p>
                            <div class="director-quote-author">
                                <strong>Managing Director</strong>
                                <span>AITF Management Certification</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include './footerSource.php'; ?>

    <!-- Modal Success Script -->
    <?php if ($successApplicant !== ''): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var modal = document.getElementById('careerSuccessModal');
                var closeBtns = document.querySelectorAll('[data-modal-close]');

                function closeModal() {
                    if (!modal) return;
                    modal.style.opacity = '0';
                    modal.style.transition = 'opacity 0.2s ease';
                    setTimeout(function () {
                        if (modal && modal.parentNode) {
                            modal.parentNode.removeChild(modal);
                        }
                    }, 200);
                }

                closeBtns.forEach(function (btn) {
                    btn.addEventListener('click', closeModal);
                });

                if (modal) {
                    modal.addEventListener('click', function (e) {
                        if (e.target === modal) closeModal();
                    });
                }

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') closeModal();
                });

                if (window.history && window.history.replaceState) {
                    window.history.replaceState({}, document.title, 'career.php');
                }
            });
        </script>
    <?php endif; ?>

    <script>
        var isEmailVerified = false;
        var otpTimerInterval = null;
        var verifiedEmailAddress = '';

        // Qualification Radio Pills
        function handleQualificationChange(radio) {
            var pills = document.querySelectorAll('.qualification-pill');
            pills.forEach(function (pill) {
                pill.classList.remove('active');
            });
            if (radio && radio.parentElement) {
                radio.parentElement.classList.add('active');
            }

            var otherBox = document.getElementById('otherQualBox');
            var otherInput = document.getElementById('qualification_other');
            if (radio.value === 'Any other relevant') {
                otherBox.classList.add('show');
                if (otherInput) otherInput.focus();
            } else {
                otherBox.classList.remove('show');
            }
        }

        // File upload feedback
        function handleFileSelect(input) {
            var badge = document.getElementById('fileSelectedBadge');
            var nameDisplay = document.getElementById('fileNameDisplay');
            var sizeDisplay = document.getElementById('fileSizeDisplay');
            var dropzone = document.getElementById('fileDropzone');

            if (input.files && input.files[0]) {
                var file = input.files[0];
                if (!file.name.toLowerCase().endsWith('.pdf')) {
                    alert('Please select a PDF format resume.');
                    input.value = '';
                    if (badge) badge.classList.remove('active');
                    return;
                }
                var sizeKb = Math.round(file.size / 1024);
                var sizeText = sizeKb > 1024 ? (sizeKb / 1024).toFixed(1) + ' MB' : sizeKb + ' KB';

                if (nameDisplay) nameDisplay.textContent = file.name;
                if (sizeDisplay) sizeDisplay.textContent = '(' + sizeText + ')';
                if (badge) badge.classList.add('active');
                if (dropzone) dropzone.style.borderColor = '#16a34a';
            } else {
                if (badge) badge.classList.remove('active');
                if (dropzone) dropzone.style.borderColor = '';
            }
        }

        // Email input change reset
        function handleEmailInput() {
            var emailInput = document.getElementById('email');
            if (isEmailVerified && emailInput.value.trim().toLowerCase() !== verifiedEmailAddress) {
                isEmailVerified = false;
                document.getElementById('emailVerifiedBadge').style.display = 'none';
                document.getElementById('otpVerifyRow').classList.remove('show');
                document.getElementById('btnSendOtp').disabled = false;
                document.getElementById('btnOtpText').textContent = 'Send OTP';
                updateSubmitButtonState();
            }
        }

        // Send OTP
        function sendEmailOtp() {
            var emailInput = document.getElementById('email');
            var email = emailInput ? emailInput.value.trim() : '';
            var btnSend = document.getElementById('btnSendOtp');
            var feedback = document.getElementById('otpFeedbackMsg');
            var otpRow = document.getElementById('otpVerifyRow');

            if (!email || email.indexOf('@') === -1 || email.indexOf('.') === -1) {
                showFeedback('Please enter a valid email address first.', 'error');
                if (emailInput) emailInput.focus();
                return;
            }

            btnSend.disabled = true;
            btnSend.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
            showFeedback('Sending verification OTP to ' + email + '...', 'info');

            var formData = new FormData();
            formData.append('action', 'send_otp');
            formData.append('email', email);

            fetch('sendCareerOtp.php', {
                method: 'POST',
                body: formData
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.status === 'ok') {
                    showFeedback(data.message, 'success');
                    otpRow.classList.add('show');
                    document.getElementById('email_otp').focus();
                    startOtpCountdown(60);
                } else {
                    showFeedback(data.message || 'Error sending OTP. Please try again.', 'error');
                    btnSend.disabled = false;
                    btnSend.innerHTML = '<i class="fa-solid fa-key"></i> Resend OTP';
                }
            })
            .catch(function (err) {
                showFeedback('Network error. Please try again.', 'error');
                btnSend.disabled = false;
                btnSend.innerHTML = '<i class="fa-solid fa-key"></i> Send OTP';
            });
        }

        // Verify OTP
        function verifyEmailOtp() {
            var email = document.getElementById('email').value.trim();
            var otpInput = document.getElementById('email_otp');
            var otp = otpInput ? otpInput.value.trim() : '';
            var btnVerify = document.getElementById('btnVerifyOtp');

            if (!otp || otp.length < 4) {
                showFeedback('Please enter the 6-digit OTP code received on email.', 'error');
                if (otpInput) otpInput.focus();
                return;
            }

            btnVerify.disabled = true;
            btnVerify.textContent = 'Verifying...';

            var formData = new FormData();
            formData.append('action', 'verify_otp');
            formData.append('email', email);
            formData.append('otp', otp);

            fetch('sendCareerOtp.php', {
                method: 'POST',
                body: formData
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                btnVerify.disabled = false;
                btnVerify.textContent = 'Verify OTP';

                if (data.status === 'ok') {
                    isEmailVerified = true;
                    verifiedEmailAddress = email.toLowerCase();
                    showFeedback('Email address verified successfully!', 'success');
                    document.getElementById('otpVerifyRow').classList.remove('show');
                    document.getElementById('emailVerifiedBadge').style.display = 'inline-flex';
                    document.getElementById('btnSendOtp').style.display = 'none';
                    if (otpTimerInterval) clearInterval(otpTimerInterval);
                    document.getElementById('otpCountdownText').textContent = '';
                    updateSubmitButtonState();
                } else {
                    showFeedback(data.message || 'Invalid verification code.', 'error');
                }
            })
            .catch(function (err) {
                btnVerify.disabled = false;
                btnVerify.textContent = 'Verify OTP';
                showFeedback('Verification error. Please try again.', 'error');
            });
        }

        function showFeedback(msg, type) {
            var fb = document.getElementById('otpFeedbackMsg');
            if (!fb) return;
            fb.style.display = 'flex';
            fb.className = 'otp-status-msg ' + type;
            var icon = type === 'success' ? '<i class="fa-solid fa-circle-check"></i>' : (type === 'error' ? '<i class="fa-solid fa-triangle-exclamation"></i>' : '<i class="fa-solid fa-info-circle"></i>');
            fb.innerHTML = icon + ' ' + msg;
        }

        function startOtpCountdown(seconds) {
            var countdownText = document.getElementById('otpCountdownText');
            var btnSend = document.getElementById('btnSendOtp');
            var remaining = seconds;

            if (otpTimerInterval) clearInterval(otpTimerInterval);

            btnSend.disabled = true;
            btnSend.innerHTML = '<i class="fa-solid fa-clock"></i> Wait ' + remaining + 's';

            otpTimerInterval = setInterval(function () {
                remaining--;
                if (remaining > 0) {
                    btnSend.innerHTML = '<i class="fa-solid fa-clock"></i> Wait ' + remaining + 's';
                    countdownText.textContent = 'You can request a new code in ' + remaining + ' seconds.';
                } else {
                    clearInterval(otpTimerInterval);
                    btnSend.disabled = false;
                    btnSend.innerHTML = '<i class="fa-solid fa-rotate-right"></i> Resend OTP';
                    countdownText.textContent = '';
                }
            }, 1000);
        }

        function updateSubmitButtonState() {
            var submitBtn = document.getElementById('submitBtn');
            var notice = document.getElementById('submitNotice');
            if (isEmailVerified) {
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
                if (notice) notice.innerHTML = '<span style="color:#16a34a; font-weight:600;"><i class="fa-solid fa-check"></i> Verified. You can now submit your application.</span>';
            } else {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.6';
                submitBtn.style.cursor = 'not-allowed';
                if (notice) notice.innerHTML = '<i class="fa-solid fa-shield-halved"></i> Please verify your email with OTP to enable submission.';
            }
        }

        // Form submission safety check
        document.getElementById('careerApplicationForm').addEventListener('submit', function (e) {
            if (!isEmailVerified) {
                e.preventDefault();
                alert('Please verify your email address using the OTP before submitting.');
                document.getElementById('email').focus();
            }
        });
    </script>
</body>

</html>