<?php
require_once __DIR__ . '/include/session_helper.php';
aitf_start_session();

$loginNotice = trim((string)($_SESSION['login_notice'] ?? ''));
if ($loginNotice === '' && !empty($_SESSION['login_error'])) {
    $loginNotice = 'Invalid login details. Please try again.';
}
if ($loginNotice === '' && ($_GET['reason'] ?? '') === 'not_found') {
    $loginNotice = 'The requested page was not found. Please log in to continue.';
}
if ($loginNotice === '' && ($_GET['reason'] ?? '') === 'login_required') {
    $loginNotice = 'Please log in again to continue.';
}
if ($loginNotice === '' && ($_GET['reason'] ?? '') === 'invalid_url') {
    $loginNotice = 'Invalid URL. Please login again.';
}
unset($_SESSION['login_notice'], $_SESSION['login_error'], $_SESSION['session_expired']);

$turnstileHelperPath = __DIR__ . '/include/turnstile_helper.php';
if (file_exists($turnstileHelperPath)) {
    require_once $turnstileHelperPath;
}

$turnstileConfig = function_exists('aitf_get_turnstile_config') ? aitf_get_turnstile_config() : array(
    'site_key' => '',
    'secret_key' => ''
);
$turnstileReady = function_exists('aitf_is_turnstile_ready') ? aitf_is_turnstile_ready() : false;

$activeMode = 'employee';
if (isset($_GET['director'])) {
    $activeMode = 'director';
} elseif (isset($_GET['client'])) {
    $activeMode = 'client';
} elseif (isset($_GET['auditor'])) {
    $activeMode = 'auditor';
}
$createAccountStep = ($_GET['step'] ?? '') === 'verify' ? 'verify' : 'start';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './headSource.php'; ?>
    <?php if ($turnstileReady): ?>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <?php endif; ?>
    <title>AITF - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0F2742;
            --primary-hover: #1e3a5f;
            --primary-focus: rgba(15, 39, 66, 0.15);
            --bg-color: #FAFAFA;
            --card-bg: #FFFFFF;
            --text-main: #09090B;
            --text-muted: #71717A;
            --border: #E4E4E7;
            --radius: 20px;
            --radius-sm: 12px;
            --shadow: 0 1px 2px rgba(0, 0, 0, 0.05), 0 8px 32px rgba(0, 0, 0, 0.03);
            --shadow-hover: 0 20px 60px rgba(0, 0, 0, 0.08);
            --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { box-sizing: border-box; }
        
        body.aitf-login-page {
            margin: 0;
            min-height: 100vh;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-color);
            background-image: radial-gradient(rgba(15, 39, 66, 0.1) 1px, transparent 1px);
            background-size: 24px 24px;
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 440px;
            z-index: 10;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .brand-logo {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #0F2742 0%, #1e3a5f 100%);
            color: white;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 20px;
            box-shadow: 0 8px 16px rgba(15, 39, 66, 0.2), inset 0 1px 1px rgba(255,255,255,0.2);
        }
        .brand-header h1 {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 8px;
            letter-spacing: -0.02em;
        }
        .brand-header p {
            font-size: 14px;
            color: var(--text-muted);
            margin: 0;
        }

        .auth-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid rgba(229, 231, 235, 0.5);
            padding: 40px;
            position: relative;
            overflow: hidden;
            animation: cardSlideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes cardSlideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .auth-mode-tabs {
            display: flex;
            background: #F4F4F5;
            border-radius: 12px;
            padding: 4px;
            margin-bottom: 24px;
        }
        .auth-mode-btn {
            flex: 1;
            border: none;
            background: transparent;
            padding: 10px 4px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .auth-mode-btn i { font-size: 14px; }
        .auth-mode-btn:hover { color: var(--text-main); }
        .auth-mode-btn.active {
            background: white;
            color: var(--primary);
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .auth-panels { position: relative; }
        .auth-panel {
            display: none;
            animation: fadeIn 0.3s ease;
        }
        .auth-panel.active { display: block; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .auth-field {
            display: flex;
            flex-direction: column;
        }
        .auth-field label {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-main);
        }
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-wrapper i.icon-left {
            position: absolute;
            left: 16px;
            color: #A1A1AA;
            font-size: 15px;
            pointer-events: none;
            transition: var(--transition);
        }
        .auth-input {
            width: 100%;
            height: 48px;
            background: #FFFFFF;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 0 16px 0 44px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-main);
            transition: var(--transition);
            box-shadow: 0 1px 2px rgba(0,0,0,0.02) inset;
        }
        .auth-input::placeholder { color: #A1A1AA; font-weight: 400; }
        .auth-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-focus), 0 1px 2px rgba(0,0,0,0.02) inset;
        }
        .input-wrapper:focus-within i.icon-left {
            color: var(--primary);
        }

        .password-toggle {
            position: absolute;
            right: 8px;
            background: transparent;
            border: none;
            color: #A1A1AA;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }
        .password-toggle:hover {
            background: #F4F4F5;
            color: var(--text-main);
        }

        .auth-submit {
            width: 100%;
            height: 48px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: var(--transition);
            margin-top: 8px;
        }
        .auth-submit:hover:not(:disabled) {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(15, 39, 66, 0.2);
        }
        .auth-submit:active:not(:disabled) {
            transform: translateY(0);
        }
        .auth-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .captcha-wrap {
            display: flex;
            justify-content: center;
            margin-top: 4px;
        }
        .captcha-note {
            font-size: 13px;
            color: var(--text-muted);
            text-align: center;
            padding: 12px;
            background: #F4F4F5;
            border-radius: var(--radius-sm);
            border: 1px dashed var(--border);
            width: 100%;
        }

        .auth-footer-links {
            margin-top: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            font-size: 14px;
            border-top: 1px solid var(--border);
            padding-top: 28px;
        }
        .auth-link {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }
        .auth-link:hover { color: var(--text-main); }
        .primary-link {
            color: var(--primary);
            font-weight: 600;
        }
        .primary-link:hover { text-decoration: underline; }
        .divider { color: var(--border); }

        .login-notice {
            background: #FEF2F2;
            border: 1px solid #FCA5A5;
            color: #B91C1C;
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 24px;
            text-align: center;
        }

        /* Modals */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(9, 9, 11, 0.4);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 16px;
        }
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        .modal-container {
            background: var(--card-bg);
            width: 100%;
            max-width: 440px;
            border-radius: var(--radius);
            box-shadow: 0 24px 48px rgba(0, 0, 0, 0.12), 0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 40px;
            transform: translateY(20px) scale(0.96);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            border: 1px solid rgba(229, 231, 235, 0.5);
        }
        .modal-overlay.active .modal-container {
            transform: translateY(0) scale(1);
        }
        .modal-close {
            position: absolute;
            top: 24px;
            right: 24px;
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }
        .modal-close:hover {
            background: #F4F4F5;
            color: var(--text-main);
        }
        .modal-title {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 8px;
            color: var(--text-main);
            letter-spacing: -0.02em;
        }
        .modal-subtitle {
            font-size: 14px;
            color: var(--text-muted);
            margin: 0 0 32px;
            line-height: 1.5;
        }

        .signup-steps {
            display: flex;
            gap: 12px;
            margin-bottom: 32px;
        }
        .signup-step {
            flex: 1;
            height: 4px;
            background: #F4F4F5;
            border-radius: 2px;
            position: relative;
        }
        .signup-step.active { background: var(--primary); }
        .signup-step-text {
            position: absolute;
            top: 12px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            white-space: nowrap;
        }
        .signup-step.active .signup-step-text { color: var(--text-main); }
        
        .login-loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 99999;
        }
        .login-loading-box {
            background: white;
            padding: 32px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-hover);
            text-align: center;
            border: 1px solid var(--border);
        }
        .login-loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #F4F4F5;
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 16px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .login-loading-title { font-weight: 700; font-size: 15px; color: var(--text-main); }
        .login-loading-subtitle { font-size: 13px; color: var(--text-muted); margin-top: 4px; }
        
        @media (max-width: 500px) {
            .auth-card, .modal-container { padding: 32px 24px; }
            .auth-footer-links { flex-direction: column; gap: 12px; }
            .divider { display: none; }
        }
    </style>
</head>
<body class="aitf-login-page">
    <?php include './headerSource.php'; ?>
    <div id="login-loading-overlay" class="login-loading-overlay">
        <div class="login-loading-box">
            <div class="login-loading-spinner"></div>
            <div class="login-loading-title">Checking credentials</div>
            <div class="login-loading-subtitle">Please wait for a moment.</div>
        </div>
    </div>

    <div class="auth-wrapper">
        <div class="brand-header">
            <div class="brand-logo">A</div>
            <h1>AITF ERP</h1>
            <p>Management Certification Limited</p>
        </div>

        <div class="auth-card">
            <div class="auth-mode-tabs">
                <button type="button" class="auth-mode-btn <?php echo $activeMode === 'employee' ? 'active' : ''; ?>" data-mode="employee">
                    <i class="fa fa-user"></i> Employee
                </button>
                <button type="button" class="auth-mode-btn <?php echo $activeMode === 'director' ? 'active' : ''; ?>" data-mode="director">
                    <i class="fa fa-id-badge"></i> Director
                </button>
                <button type="button" class="auth-mode-btn <?php echo $activeMode === 'client' ? 'active' : ''; ?>" data-mode="client">
                    <i class="fa fa-building"></i> Client
                </button>
                <button type="button" class="auth-mode-btn <?php echo $activeMode === 'auditor' ? 'active' : ''; ?>" data-mode="auditor">
                    <i class="fa fa-clipboard-check"></i> Auditor
                </button>
            </div>

            <?php if ($loginNotice !== ''): ?>
                <div class="login-notice" role="alert">
                    <?php echo htmlspecialchars($loginNotice, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <div class="auth-panels">
                <div class="auth-panel <?php echo $activeMode === 'employee' ? 'active' : ''; ?>" data-panel="employee">
                    <form action="login.php" method="post" class="auth-form" data-requires-captcha="<?php echo $turnstileReady ? '1' : '0'; ?>">
                        <div class="auth-field">
                            <label>Email Address</label>
                            <div class="input-wrapper">
                                <i class="fa fa-envelope icon-left"></i>
                                <input class="auth-input" type="email" name="useremail" placeholder="name@company.com" required>
                            </div>
                        </div>
                        <div class="auth-field">
                            <label>Password</label>
                            <div class="input-wrapper">
                                <i class="fa fa-lock icon-left"></i>
                                <input class="auth-input" type="password" name="password" id="employeePassword" placeholder="••••••••" required>
                                <button type="button" class="password-toggle" data-toggle-password="#employeePassword" aria-label="Show password">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="captcha-wrap">
                            <?php if ($turnstileReady): ?>
                                <div id="employee-login-turnstile" class="cf-turnstile" data-sitekey="<?php echo htmlspecialchars($turnstileConfig['site_key'], ENT_QUOTES, 'UTF-8'); ?>" data-theme="light" data-callback="authCaptchaChanged" data-expired-callback="authCaptchaChanged" data-error-callback="authCaptchaChanged"></div>
                            <?php else: ?>
                                <div class="captcha-note">Captcha will be enabled after keys are configured.</div>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="auth-submit" <?php if ($turnstileReady): ?>disabled<?php endif; ?>>
                            Sign In
                        </button>
                    </form>
                    
                    <div class="auth-footer-links">
                        <a href="#" class="auth-link" onclick="openModal('forgotPasswordModal'); return false;">Forgot Password?</a>
                        <span class="divider">•</span>
                        <a href="#" class="auth-link primary-link" onclick="openModal('registerModal'); return false;">Create New Account</a>
                    </div>
                </div>

                <div class="auth-panel <?php echo $activeMode === 'director' ? 'active' : ''; ?>" data-panel="director">
                    <form action="login.php" method="post" class="auth-form" data-requires-captcha="<?php echo $turnstileReady ? '1' : '0'; ?>">
                        <div class="auth-field">
                            <label>Director Email</label>
                            <div class="input-wrapper">
                                <i class="fa fa-envelope icon-left"></i>
                                <input class="auth-input" type="email" name="directorEmail" placeholder="director@company.com" required>
                            </div>
                        </div>
                        <div class="auth-field">
                            <label>Password</label>
                            <div class="input-wrapper">
                                <i class="fa fa-lock icon-left"></i>
                                <input class="auth-input" type="password" name="directorPassword" id="directorPassword" placeholder="••••••••" required>
                                <button type="button" class="password-toggle" data-toggle-password="#directorPassword" aria-label="Show password">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="captcha-wrap">
                            <?php if ($turnstileReady): ?>
                                <div id="director-login-turnstile" class="cf-turnstile" data-sitekey="<?php echo htmlspecialchars($turnstileConfig['site_key'], ENT_QUOTES, 'UTF-8'); ?>" data-theme="light" data-callback="authCaptchaChanged" data-expired-callback="authCaptchaChanged" data-error-callback="authCaptchaChanged"></div>
                            <?php else: ?>
                                <div class="captcha-note">Captcha will be enabled after keys are configured.</div>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="auth-submit" <?php if ($turnstileReady): ?>disabled<?php endif; ?>>
                            Sign In as Director
                        </button>
                    </form>
                </div>

                <div class="auth-panel <?php echo $activeMode === 'client' ? 'active' : ''; ?>" data-panel="client">
                    <form action="login.php" method="post" class="auth-form" data-requires-captcha="<?php echo $turnstileReady ? '1' : '0'; ?>">
                        <div class="auth-field">
                            <label>Client Name / ID</label>
                            <div class="input-wrapper">
                                <i class="fa fa-user icon-left"></i>
                                <input class="auth-input" type="text" name="clientemail" placeholder="Client identity" required>
                            </div>
                        </div>
                        <div class="auth-field">
                            <label>Client Password</label>
                            <div class="input-wrapper">
                                <i class="fa fa-lock icon-left"></i>
                                <input class="auth-input" type="password" name="clientcid" id="clientPassword" placeholder="••••••••" required>
                                <button type="button" class="password-toggle" data-toggle-password="#clientPassword" aria-label="Show password">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="captcha-wrap">
                            <?php if ($turnstileReady): ?>
                                <div id="client-login-turnstile" class="cf-turnstile" data-sitekey="<?php echo htmlspecialchars($turnstileConfig['site_key'], ENT_QUOTES, 'UTF-8'); ?>" data-theme="light" data-callback="authCaptchaChanged" data-expired-callback="authCaptchaChanged" data-error-callback="authCaptchaChanged"></div>
                            <?php else: ?>
                                <div class="captcha-note">Captcha will be enabled after keys are configured.</div>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="auth-submit" <?php if ($turnstileReady): ?>disabled<?php endif; ?>>
                            Sign In as Client
                        </button>
                    </form>
                </div>

                <div class="auth-panel <?php echo $activeMode === 'auditor' ? 'active' : ''; ?>" data-panel="auditor">
                    <form action="login.php" method="post" class="auth-form" data-requires-captcha="<?php echo $turnstileReady ? '1' : '0'; ?>">
                        <div class="auth-field">
                            <label>Registered Email</label>
                            <div class="input-wrapper">
                                <i class="fa fa-envelope icon-left"></i>
                                <input class="auth-input" type="email" name="auditorEmail" placeholder="auditor@email.com" required>
                            </div>
                        </div>
                        <div class="auth-field">
                            <label>Login Key</label>
                            <div class="input-wrapper">
                                <i class="fa fa-key icon-left"></i>
                                <input class="auth-input" type="text" name="auditorKey" id="auditorKey" placeholder="AITF-AUD-2026-XXXX-XXXX-XXXX" required>
                                <button type="button" class="password-toggle" data-toggle-password="#auditorKey" aria-label="Show key">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="captcha-wrap">
                            <?php if ($turnstileReady): ?>
                                <div id="auditor-login-turnstile" class="cf-turnstile" data-sitekey="<?php echo htmlspecialchars($turnstileConfig['site_key'], ENT_QUOTES, 'UTF-8'); ?>" data-theme="light" data-callback="authCaptchaChanged" data-expired-callback="authCaptchaChanged" data-error-callback="authCaptchaChanged"></div>
                            <?php else: ?>
                                <div class="captcha-note">Captcha will be enabled after keys are configured.</div>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="auth-submit" <?php if ($turnstileReady): ?>disabled<?php endif; ?>>
                            Sign In as Auditor
                        </button>
                    </form>
                    <div class="auth-footer-links">
                        <span class="auth-link" style="color: var(--text-muted); font-size: 13px;">Login key is provided by AITF administrator</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Registration Modal -->
    <div id="registerModal" class="modal-overlay">
        <div class="modal-container">
            <button class="modal-close" onclick="closeModal('registerModal')"><i class="fa fa-times"></i></button>
            <h2 class="modal-title">Create Account</h2>
            <p class="modal-subtitle">Follow the steps to set up your employee access.</p>
            
            <div class="signup-steps">
                <div class="signup-step active">
                    <span class="signup-step-text">1. Email</span>
                </div>
                <div class="signup-step <?php echo $createAccountStep === 'verify' ? 'active' : ''; ?>">
                    <span class="signup-step-text">2. OTP</span>
                </div>
                <div class="signup-step <?php echo $createAccountStep === 'verify' ? 'active' : ''; ?>">
                    <span class="signup-step-text">3. Complete</span>
                </div>
            </div>

            <div class="step-content">
                <?php if ($createAccountStep === 'verify') { ?>
                    <form action="createAccount.php" method="post" class="auth-form">
                        <input type="hidden" name="create_account_action" value="complete_signup">
                        <div class="auth-field">
                            <label>OTP Code</label>
                            <div class="input-wrapper">
                                <i class="fa fa-shield icon-left"></i>
                                <input class="auth-input" type="text" name="signup_otp" placeholder="Enter 6-digit OTP" maxlength="6" inputmode="numeric" required>
                            </div>
                        </div>
                        <div class="auth-field">
                            <label>Employee ID (EID)</label>
                            <div class="input-wrapper">
                                <i class="fa fa-id-card icon-left"></i>
                                <input class="auth-input" type="text" name="signup_eid" placeholder="Enter your EID" required>
                            </div>
                        </div>
                        <button type="submit" class="auth-submit">Validate and Login</button>
                    </form>
                    <div class="captcha-note" style="margin-top: 24px;">OTP is valid for 10 minutes. After validation, you will be logged in automatically.</div>
                <?php } else { ?>
                    <form action="createAccount.php" method="post" class="auth-form">
                        <input type="hidden" name="create_account_action" value="request_otp">
                        <div class="auth-field">
                            <label>Registered Email</label>
                            <div class="input-wrapper">
                                <i class="fa fa-envelope icon-left"></i>
                                <input class="auth-input" type="email" name="signup_email" placeholder="email@company.com" required>
                            </div>
                        </div>
                        <div class="auth-field">
                            <label>Create Password</label>
                            <div class="input-wrapper">
                                <i class="fa fa-lock icon-left"></i>
                                <input class="auth-input" type="password" name="signup_password" id="signupPassword" placeholder="••••••••" required>
                                <button type="button" class="password-toggle" data-toggle-password="#signupPassword" aria-label="Show password">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="auth-field">
                            <label>Confirm Password</label>
                            <div class="input-wrapper">
                                <i class="fa fa-lock icon-left"></i>
                                <input class="auth-input" type="password" name="signup_confirm_password" id="signupConfirmPassword" placeholder="••••••••" required>
                                <button type="button" class="password-toggle" data-toggle-password="#signupConfirmPassword" aria-label="Show password">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="auth-submit">Continue</button>
                    </form>
                    <div class="captcha-note" style="margin-top: 24px;">Use the email registered by your Director. An OTP will be sent to this address.</div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div id="forgotPasswordModal" class="modal-overlay">
        <div class="modal-container">
            <button class="modal-close" onclick="closeModal('forgotPasswordModal')"><i class="fa fa-times"></i></button>
            <h2 class="modal-title">Reset Password</h2>
            <p class="modal-subtitle">Enter your email address and we'll send you a link to reset your password.</p>
            
            <form action="forgotPassword.php" method="post" class="auth-form">
                <div class="auth-field">
                    <label>Email Address</label>
                    <div class="input-wrapper">
                        <i class="fa fa-envelope icon-left"></i>
                        <input class="auth-input" type="email" name="reset_email" placeholder="name@company.com" required>
                    </div>
                </div>
                <button type="submit" class="auth-submit">Send Reset Link</button>
            </form>
        </div>
    </div>

    <script>
        const turnstileEnabled = <?php echo json_encode($turnstileReady); ?>;

        function authCaptchaChanged() {
            document.querySelectorAll('.auth-form[data-requires-captcha="1"]').forEach(function (form) {
                var submit = form.querySelector('.auth-submit');
                var token = form.querySelector('input[name="cf-turnstile-response"]');
                if (submit) {
                    submit.disabled = !(token && token.value.trim() !== '');
                }
            });
        }

        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        document.addEventListener('DOMContentLoaded', function () {
            var overlay = document.getElementById('login-loading-overlay');

            document.querySelectorAll('.auth-mode-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    var mode = button.getAttribute('data-mode');
                    document.querySelectorAll('.auth-mode-btn').forEach(function (item) {
                        item.classList.toggle('active', item === button);
                    });
                    document.querySelectorAll('.auth-panel').forEach(function (panel) {
                        panel.classList.toggle('active', panel.getAttribute('data-panel') === mode);
                    });
                    authCaptchaChanged();
                });
            });

            document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var input = document.querySelector(button.getAttribute('data-toggle-password'));
                    var icon = button.querySelector('i');
                    if (!input) {
                        return;
                    }
                    input.type = input.type === 'password' ? 'text' : 'password';
                    if (icon) {
                        icon.classList.toggle('fa-eye');
                        icon.classList.toggle('fa-eye-slash');
                    }
                });
            });

            document.querySelectorAll('form').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    var requiresCaptcha = form.getAttribute('data-requires-captcha') === '1';
                    if (turnstileEnabled && requiresCaptcha) {
                        var token = form.querySelector('input[name="cf-turnstile-response"]');
                        if (!token || token.value.trim() === '') {
                            event.preventDefault();
                            authCaptchaChanged();
                            alert('Please complete the captcha verification.');
                            return;
                        }
                    }

                    var submit = form.querySelector('.auth-submit');
                    if (submit && submit.disabled) {
                        event.preventDefault();
                        return;
                    }
                    if (submit) {
                        submit.disabled = true;
                        submit.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
                    }
                    if (overlay) {
                        overlay.style.display = 'flex';
                    }
                });
            });
            
            document.querySelectorAll('.modal-overlay').forEach(function(modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.classList.remove('active');
                    }
                });
            });

            authCaptchaChanged();

            <?php if ($createAccountStep === 'verify'): ?>
                openModal('registerModal');
            <?php endif; ?>
        });
    </script>
</body>
</html>
