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

$turnstileConfig = function_exists('aitf_get_turnstile_config') ? aitf_get_turnstile_config() : [
    'site_key' => '',
    'secret_key' => ''
];
$turnstileReady = function_exists('aitf_is_turnstile_ready') ? aitf_is_turnstile_ready() : false;

$activeMode = $_SESSION['last_login_type'] ?? 'employee';
$validModes = ['employee', 'director', 'client'];
if (!in_array($activeMode, $validModes, true)) {
    $activeMode = 'employee';
}

$createAccountStep = $_SESSION['create_account_step'] ?? 'email';
?>
<!DOCTYPE html>
<html lang="en">
<?php $pageTitle = "AITF - Portal Login"; ?>
<head>
    <?php include './headSource.php'; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --bg-color: #0f172a;
            --card-bg: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --radius-md: 20px;
            --radius-sm: 12px;
            --shadow: 0 1px 2px rgba(0, 0, 0, 0.05), 0 8px 32px rgba(0, 0, 0, 0.03);
            --shadow-hover: 0 20px 60px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { box-sizing: border-box; }
        
        body.aitf-login-page {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(-45deg, #0f2027, #203a43, #2c5364);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            color: #ffffff;
            display: flex;
            flex-direction: column;
        }
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .main-content-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 24px;
            margin-top: calc(var(--announcement-height, 48px) + var(--nav-height, 85px));
        }

        /* Loading Overlay */
        .login-loading-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(8px);
            display: none; align-items: center; justify-content: center; z-index: 9999;
        }
        .login-loading-box { text-align: center; }
        .login-loading-spinner {
            width: 50px; height: 50px; border: 4px solid rgba(59, 130, 246, 0.2);
            border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 20px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .login-loading-title { color: white; font-size: 1.25rem; font-weight: 600; margin-bottom: 8px; }
        .login-loading-subtitle { color: #94a3b8; font-size: 0.95rem; }

        .auth-wrapper {
            width: 100%; max-width: 460px; z-index: 10;
        }

        .brand-header { text-align: center; margin-bottom: 40px; }
        .brand-logo {
            width: 72px; height: 72px; background: linear-gradient(135deg, #3b82f6, #2563eb);
            border-radius: 20px; display: flex; align-items: center; justify-content: center;
            font-size: 32px; font-weight: 800; color: white; margin: 0 auto 20px;
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
        }
        .brand-header h1 { font-size: 28px; font-weight: 700; margin: 0 0 8px; letter-spacing: -0.5px; }
        .brand-header p { font-size: 15px; color: rgba(255, 255, 255, 0.8); margin: 0; font-weight: 500; }

        .auth-card {
            background: rgba(255, 255, 255, 0.08); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 28px; padding: 40px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4), inset 0 1px 1px rgba(255,255,255,0.2);
        }

        .auth-mode-tabs {
            display: flex; background: rgba(0, 0, 0, 0.2); border-radius: 16px; padding: 6px; margin-bottom: 32px;
        }
        .auth-mode-btn {
            flex: 1; border: none; background: transparent; color: rgba(255, 255, 255, 0.6);
            padding: 12px; font-size: 14px; font-weight: 600; border-radius: 12px; cursor: pointer;
            transition: var(--transition); display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .auth-mode-btn:hover { color: #ffffff; }
        .auth-mode-btn.active { background: #ffffff; color: #0f172a; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }

        .auth-panel { display: none; animation: fadeIn 0.4s ease; }
        .auth-panel.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .auth-field { margin-bottom: 24px; position: relative; }
        .auth-field label { display: block; font-size: 13px; font-weight: 600; color: rgba(255, 255, 255, 0.9); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .input-wrapper { position: relative; display: flex; align-items: center; }
        .input-wrapper i.icon-left { position: absolute; left: 18px; color: rgba(255, 255, 255, 0.5); font-size: 16px; pointer-events: none; transition: var(--transition); }
        .auth-input {
            width: 100%; height: 54px; background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px; padding: 0 16px 0 48px; font-size: 16px; font-weight: 500; color: #ffffff; transition: var(--transition);
        }
        .auth-input::placeholder { color: rgba(255, 255, 255, 0.4); font-weight: 400; }
        .auth-input:focus {
            background: rgba(255, 255, 255, 0.1); border-color: rgba(255, 255, 255, 0.3); outline: none;
            box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.05);
        }
        .auth-input:focus + i.icon-left { color: #ffffff; }
        
        .password-toggle {
            position: absolute; right: 12px; background: transparent; border: none; color: rgba(255, 255, 255, 0.5);
            padding: 8px; cursor: pointer; transition: var(--transition);
        }
        .password-toggle:hover { color: #ffffff; }

        .auth-submit {
            width: 100%; height: 54px; background: #ffffff; color: #0f172a; border: none; border-radius: 14px;
            font-size: 16px; font-weight: 700; cursor: pointer; transition: var(--transition); margin-top: 8px;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .auth-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(255, 255, 255, 0.2); }
        .auth-submit:disabled { opacity: 0.7; cursor: not-allowed; transform: none; box-shadow: none; }

        .auth-footer-links {
            display: flex; justify-content: center; align-items: center; gap: 16px; margin-top: 32px; font-size: 14px;
        }
        .auth-link { color: rgba(255, 255, 255, 0.7); text-decoration: none; font-weight: 500; transition: var(--transition); }
        .auth-link:hover { color: #ffffff; }
        .auth-link.primary-link { color: #60a5fa; font-weight: 600; }
        .auth-link.primary-link:hover { color: #93c5fd; }
        .divider { color: rgba(255, 255, 255, 0.3); }

        .login-notice {
            background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5;
            padding: 16px; border-radius: 12px; font-size: 14px; margin-bottom: 24px; font-weight: 500;
            display: flex; align-items: center; gap: 12px;
        }

        .captcha-wrap { margin-bottom: 24px; min-height: 65px; display: flex; justify-content: center; }
        .captcha-note { font-size: 12px; color: rgba(255, 255, 255, 0.6); text-align: center; }

        /* Modal Styles */
        .modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; z-index: 1000;
            opacity: 0; visibility: hidden; transition: all 0.3s ease; padding: 20px;
        }
        .modal-overlay.active { opacity: 1; visibility: visible; }
        .modal-container {
            background: rgba(255, 255, 255, 0.08); width: 100%; max-width: 440px; border-radius: 28px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4), inset 0 1px 1px rgba(255,255,255,0.2); padding: 32px 36px;
            transform: translateY(40px) scale(0.95); transition: all 0.5s; position: relative; border: 1px solid rgba(255, 255, 255, 0.15); color: #ffffff;
        }
        .modal-overlay.active .modal-container { transform: translateY(0) scale(1); }
        .modal-close {
            position: absolute; top: 20px; right: 20px; background: rgba(255, 255, 255, 0.1); border: none;
            width: 36px; height: 36px; border-radius: 50%; color: white; cursor: pointer;
            display: flex; align-items: center; justify-content: center; transition: var(--transition);
        }
        .modal-close:hover { background: rgba(255, 255, 255, 0.2); transform: rotate(90deg); }
        .modal-title { font-size: 28px; font-weight: 700; margin: 0 0 12px; }
        .modal-subtitle { font-size: 15px; color: rgba(255, 255, 255, 0.7); margin: 0 0 32px; }
        
        .signup-progress {
            display: flex; justify-content: space-between; position: relative; margin-bottom: 32px;
        }
        .signup-progress::before {
            content: ''; position: absolute; top: 20px; left: 0; width: 100%; height: 2px;
            background: rgba(255, 255, 255, 0.1); z-index: 1;
        }
        .signup-step { position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; gap: 8px; }
        .signup-step-circle {
            width: 40px; height: 40px; border-radius: 50%; background: #0f172a; border: 2px solid rgba(255, 255, 255, 0.2);
            display: flex; align-items: center; justify-content: center; font-weight: 600; color: rgba(255, 255, 255, 0.5);
            transition: var(--transition);
        }
        .signup-step.active .signup-step-circle { background: #3b82f6; border-color: #3b82f6; color: white; box-shadow: 0 0 15px rgba(59, 130, 246, 0.4); }
        .signup-step-text { font-size: 12px; font-weight: 600; color: rgba(255, 255, 255, 0.5); text-transform: uppercase; }
        .signup-step.active .signup-step-text { color: white; }
        
        .form-row { display: flex; gap: 16px; margin-bottom: 16px; }
        .form-row .auth-field { flex: 1; margin-bottom: 0; }
        
        .pw-req { font-size: 12px; color: rgba(255,255,255,0.7); margin-bottom: 16px; background: rgba(255,255,255,0.05); padding: 12px; border-radius: 12px; }
        .pw-req-item { margin-bottom: 4px; display: flex; align-items: center; gap: 6px; }
        .pw-req-item.met { color: #10b981; }
        .pw-req-item.unmet { color: #ef4444; }

        @media (max-width: 500px) {
            .auth-card, .modal-container { padding: 24px 20px; border-radius: 20px; }
            .auth-footer-links { flex-direction: column; gap: 14px; }
            .divider { display: none; }
            .form-row { flex-direction: column; gap: 12px; }
            .modal-title { font-size: 24px; }
            .signup-step-circle { width: 32px; height: 32px; font-size: 13px; }
        }

    </style>
</head>
<body class="aitf-login-page" id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>
    <div class="main-content-wrapper">
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

                
            </div>
        </div>
    </div>

    <!-- Registration Modal -->
    <div id="registerModal" class="modal-overlay">
        <div class="modal-container">
            <button class="modal-close" onclick="closeModal('registerModal')"><i class="fa fa-times"></i></button>
            <h2 class="modal-title">Create Account</h2>
            <p class="modal-subtitle">Join us to manage your certifications and audits.</p>
            
            <div class="signup-progress">
                <div class="signup-step <?php echo $createAccountStep === 'email' ? 'active' : ''; ?>">
                    <div class="signup-step-circle">1</div>
                    <span class="signup-step-text">Email</span>
                </div>
                <div class="signup-step <?php echo $createAccountStep === 'verify' ? 'active' : ''; ?>">
                    <div class="signup-step-circle">2</div>
                    <span class="signup-step-text">OTP</span>
                </div>
                <div class="signup-step <?php echo $createAccountStep === 'verify' ? 'active' : ''; ?>">
                    <div class="signup-step-circle">3</div>
                    <span class="signup-step-text">Complete</span>
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
                        
                        <div class="pw-req">
                            <div class="pw-req-item unmet" id="req-len"><i class="fa fa-circle-xmark"></i> At least 8 characters</div>
                            <div class="pw-req-item unmet" id="req-cap"><i class="fa fa-circle-xmark"></i> One uppercase letter</div>
                            <div class="pw-req-item unmet" id="req-spc"><i class="fa fa-circle-xmark"></i> One special character</div>
                        </div>
                        
                        <div class="form-row">
                            <div class="auth-field">
                                <label>Create Password</label>
                                <div class="input-wrapper">
                                    <i class="fa fa-lock icon-left"></i>
                                    <input class="auth-input" type="password" name="signup_password" id="signupPassword" placeholder="••••••••" pattern="(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}" title="Must contain at least 8 characters, one capital letter, and one special character" required>
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
                        </div>
                        <button type="submit" class="auth-submit" id="signupSubmitBtn" disabled>Continue</button>
                    </form>
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

            
            var signupPw = document.getElementById('signupPassword');
            if (signupPw) {
                signupPw.addEventListener('input', function() {
                    var val = this.value;
                    var len = val.length >= 8;
                    var cap = /[A-Z]/.test(val);
                    var spc = /[^a-zA-Z0-9]/.test(val);
                    
                    var rLen = document.getElementById('req-len');
                    var rCap = document.getElementById('req-cap');
                    var rSpc = document.getElementById('req-spc');
                    
                    if (len) { rLen.className = 'pw-req-item met'; rLen.innerHTML = '<i class="fa fa-check-circle"></i> At least 8 characters'; }
                    else { rLen.className = 'pw-req-item unmet'; rLen.innerHTML = '<i class="fa fa-circle-xmark"></i> At least 8 characters'; }
                    
                    if (cap) { rCap.className = 'pw-req-item met'; rCap.innerHTML = '<i class="fa fa-check-circle"></i> One uppercase letter'; }
                    else { rCap.className = 'pw-req-item unmet'; rCap.innerHTML = '<i class="fa fa-circle-xmark"></i> One uppercase letter'; }
                    
                    if (spc) { rSpc.className = 'pw-req-item met'; rSpc.innerHTML = '<i class="fa fa-check-circle"></i> One special character'; }
                    else { rSpc.className = 'pw-req-item unmet'; rSpc.innerHTML = '<i class="fa fa-circle-xmark"></i> One special character'; }
                    
                    var submitBtn = document.getElementById('signupSubmitBtn');
                    if (len && cap && spc) {
                        this.style.borderColor = '#10b981';
                        this.style.background = 'rgba(16, 185, 129, 0.1)';
                        submitBtn.disabled = false;
                    } else {
                        if (val.length > 0) {
                            this.style.borderColor = '#ef4444';
                            this.style.background = 'rgba(239, 68, 68, 0.1)';
                        } else {
                            this.style.borderColor = 'rgba(255, 255, 255, 0.12)';
                            this.style.background = 'rgba(255, 255, 255, 0.06)';
                        }
                        submitBtn.disabled = true;
                    }
                });
            }

            <?php if ($createAccountStep === 'verify'): ?>
                openModal('registerModal');
            <?php endif; ?>
        });
    </script>
    </div>
    
    <?php include 'footerSource.php'; ?>
    
</body>
</html>
