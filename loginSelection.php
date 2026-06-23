<?php
session_start();

$recaptchaHelperPath = __DIR__ . '/include/recaptcha_helper.php';
if (file_exists($recaptchaHelperPath)) {
    require_once $recaptchaHelperPath;
}

$recaptchaConfig = function_exists('aitf_get_recaptcha_config') ? aitf_get_recaptcha_config() : [
    'site_key' => '6LcIHKksAAAAAAtYeQDQaBpCAyHjg4PIa7XKFMer',
    'secret_key' => '6LcIHKksAAAAAFU1KUQ2X601xzZ-BJNbQAwVjYdA',
    'version' => 'v2_checkbox',
    'action' => 'submit',
    'minimum_score' => 0.5,
    'theme' => 'light',
    'enabled' => false
];
$recaptchaReady = function_exists('aitf_is_recaptcha_ready') ? aitf_is_recaptcha_ready() : false;
$recaptchaBypass = function_exists('aitf_should_bypass_recaptcha') ? aitf_should_bypass_recaptcha() : false;
$recaptchaFrontendEnabled = $recaptchaReady && !$recaptchaBypass;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './headSource.php'; ?>
    <?php if ($recaptchaFrontendEnabled): ?>
        <?php if (($recaptchaConfig['version'] ?? 'v2_checkbox') === 'v3'): ?>
            <script
                src="https://www.google.com/recaptcha/api.js?render=<?php echo urlencode($recaptchaConfig['site_key']); ?>"></script>
        <?php else: ?>
            <script src="https://www.google.com/recaptcha/api.js?onload=loginRecaptchaOnload&render=explicit" async
                defer></script>
        <?php endif; ?>
    <?php endif; ?>
    <title>AITF - Login</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-selection-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 80px 16px 60px;
            background:
                radial-gradient(circle at 20% 30%, rgba(37, 99, 235, 0.15), transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(59, 130, 246, 0.12), transparent 45%),
                linear-gradient(135deg, #0f172a, #1e293b);
            position: relative;
            overflow: hidden;
        }

        .login-selection-wrapper::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.1) 0%, transparent 70%);
            top: -300px;
            left: -300px;
            z-index: 0;
        }

        .login-selection-wrapper::after {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.08) 0%, transparent 70%);
            bottom: -300px;
            right: -300px;
            z-index: 0;
        }

        .login-selection-card {
            max-width: 480px;
            width: 100%;
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.4);
            padding: 40px 32px;
            margin: 20px auto 30px;
            border: 1px solid rgba(51, 65, 85, 0.3);
            backdrop-filter: blur(12px);
            position: relative;
            z-index: 1;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .login-selection-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 18px 50px rgba(0, 0, 0, 0.5);
        }

        .login-selection-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #2563eb, #3b82f6);
        }

        .login-captcha-wrap {
            margin: 18px 0 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #f1f5f9;
            min-height: 92px;
        }

        .login-captcha-note {
            margin-top: 0;
            font-size: 0.85rem;
            color: #6b7280;
            text-align: center;
        }

        #admin-login-recaptcha,
        #client-login-recaptcha {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        #admin-login-recaptcha>div,
        #client-login-recaptcha>div {
            margin: 0 auto;
        }

        .login-submit-btn {
            width: 100%;
            border-radius: 999px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            color: #fff;
            padding: 12px 18px;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.35);
            transition: transform 0.18s ease, box-shadow 0.18s ease, opacity 0.18s ease;
        }

        .login-submit-btn:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 14px 30px rgba(37, 99, 235, 0.45);
        }

        .login-submit-btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            box-shadow: 0 6px 16px rgba(148, 163, 184, 0.3);
        }

        .login-selection-title {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 20px 0;
            text-align: center;
            letter-spacing: -0.01em;
            position: relative;
            display: block;
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-fill-color: transparent;
            line-height: 1.3;
        }

        .login-selection-title i {
            margin-right: 10px;
            font-size: 0.9em;
        }

        .login-selection-subtitle {
            font-size: 15px;
            color: #64748b;
            text-align: center;
            margin-bottom: 30px;
            line-height: 1.5;
            max-width: 360px;
            margin-left: auto;
            margin-right: auto;
        }

        .login-tabs {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }

        .login-tab-btn {
            padding: 14px 26px;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            outline: none;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            background: #f8fafc;
            color: #475569;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
            min-width: 160px;
            text-align: center;
        }

        .login-tab-btn i {
            font-size: 16px;
        }

        .login-tab-btn:hover {
            background: #f1f5f9;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(15, 23, 42, 0.08);
            border-color: #cbd5e1;
        }

        .login-tab-btn.active {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            border-color: transparent;
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.3);
            transform: translateY(-2px);
            position: relative;
            z-index: 1;
        }

        .login-tab-btn.active::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            z-index: -1;
            border-radius: 8px;
        }

        .login-tab-btn.active i {
            color: #ffffff;
        }

        .login-panels {
            margin-top: 8px;
        }

        .login-panel {
            display: none;
        }

        .login-panel.active {
            display: block;
        }

        .login-panel form {
            max-width: 360px;
            margin: 0 auto;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .input-group i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
            transition: color 0.2s ease;
        }

        .login-panel input[type="text"],
        .login-panel input[type="password"],
        .input-group {
            position: relative;
        }

        .input-group input[type="password"],
        .input-group input[type="text"] {
            width: 100%;
            padding: 14px 40px 14px 46px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            color: #1e293b;
            background-color: #f8fafc;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-sizing: border-box;
        }

        .password-toggle {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            width: 40px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            border-radius: 0 10px 10px 0;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .password-toggle:hover {
            color: #2563eb;
        }

        .login-panel input[type="text"]:focus,
        .login-panel input[type="password"]:focus,
        .login-panel input[type="email"]:focus {
            outline: none;
            border-color: #93c5fd;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
            background-color: #ffffff;
        }

        .login-panel input[type="text"]::placeholder,
        .login-panel input[type="password"]::placeholder {
            color: #9ca3af;
        }

        .login-panel input[type="text"]:focus,
        .login-panel input[type="password"]:focus {
            outline: none;
            border-color: #93c5fd;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
            background: #ffffff;
        }

        .login-panel input[type="text"]:focus+i,
        .login-panel input[type="password"]:focus+i {
            color: #2563eb;
        }

        .login-panel input[type="submit"] {
            width: 100%;
            border-radius: 30px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            font-weight: 600;
            font-size: 15px;
            padding: 12px 16px 12px 50px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
            transition: all 0.3s ease;
            margin-top: 10px;
            position: relative;
            text-transform: uppercase;
            letter-spacing: 1px;
            overflow: hidden;
        }

        .login-panel input[type="submit"]::before {
            content: '→';
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 30px;
            height: 30px;
            background: #3b82f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .login-panel input[type="submit"]:hover::before {
            left: 100%;
        }

        .login-panel input[type="submit"]:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.6);
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
        }

        .login-panel input[type="submit"]:disabled {
            opacity: 0.7;
            cursor: default;
            box-shadow: none;
        }

        .login-selection-note {
            font-size: 13px;
            color: #94a3b8;
            text-align: center;
            margin-top: 24px;
            line-height: 1.5;
            position: relative;
            padding-top: 18px;
        }

        .login-selection-note::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 2px;
            background: linear-gradient(90deg, #334155, #475569, #334155);
            border-radius: 2px;
        }

        /* Animation for form elements */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-panel {
            animation: fadeInUp 0.5s ease-out forwards;
        }

        .login-tabs {
            animation: fadeInUp 0.5s 0.1s ease-out both;
        }

        .login-selection-title {
            animation: fadeInUp 0.5s 0.15s ease-out both;
        }

        .login-selection-subtitle {
            animation: fadeInUp 0.5s 0.2s ease-out both;
        }

        @media (max-width: 576px) {
            .login-selection-card {
                padding: 24px 18px 30px;
                border-radius: 18px;
            }

            .login-tabs {
                flex-direction: column;
            }

            .login-tab-btn {
                width: 100%;
                text-align: center;
            }

            #admin-login-recaptcha,
            #client-login-recaptcha {
                transform: scale(0.92);
                transform-origin: center top;
            }
        }

        .login-loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.82);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 99999;
        }

        .login-loading-box {
            background: #0b1220;
            border-radius: 16px;
            padding: 22px 26px 18px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45);
            max-width: 320px;
            width: 90%;
            text-align: center;
            border: 1px solid rgba(148, 163, 184, 0.4);
        }

        .login-loading-spinner {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 3px solid rgba(148, 163, 184, 0.5);
            border-top-color: #38bdf8;
            border-right-color: #4f46e5;
            animation: login-spin 0.9s linear infinite;
            margin: 0 auto 14px;
        }

        .login-loading-title {
            color: #e5e7eb;
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .login-loading-subtitle {
            color: #9ca3af;
            font-size: 12px;
        }

        @keyframes login-spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">

    <?php include './headerSource.php'; ?>

    <div id="login-loading-overlay" class="login-loading-overlay">
        <div class="login-loading-box">
            <div class="login-loading-spinner"></div>
            <div class="login-loading-title">Connecting to Server...</div>
            <div class="login-loading-subtitle">Please wait while we verify your credentials.</div>
        </div>
    </div>

    <section class="login-selection-wrapper">
        <div class="login-selection-card">
            <h1 class="login-selection-title"><i class="fas fa-sign-in-alt"></i>Login</h1>
            <p class="login-selection-subtitle">Please select your login type to access your account</p>

            <div class="login-tabs">
                <button type="button" class="login-tab-btn active" data-target="#user-login-panel">
                    <i class="fas fa-user-shield"></i> Admin Login
                </button>
                <button type="button" class="login-tab-btn" data-target="#client-login-panel">
                    <i class="fas fa-user-tie"></i> Client Login
                </button>
            </div>

            <div class="login-panels">
                <div id="user-login-panel" class="login-panel active">
                    <form action="login.php" method="post" id="admin-login-form">
                        <label class="message-view" style="display: none;">Invalid Username or Password</label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="text" name="useremail" placeholder="User Email" required>
                        </div>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" id="password" placeholder="Password" required>
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                        <div class="login-captcha-wrap">
                            <?php if ($recaptchaBypass): ?>
                                <div class="login-captcha-note">Captcha is temporarily disabled on this environment.</div>
                            <?php elseif ($recaptchaReady && (($recaptchaConfig['version'] ?? 'v2_checkbox') === 'v2_checkbox')): ?>
                                <div id="admin-login-recaptcha"></div>
                            <?php elseif ($recaptchaReady): ?>
                                <input type="hidden" name="g-recaptcha-response" id="admin-login-recaptcha-token">
                                <div class="login-captcha-note">Login protection is active. Your request will be verified
                                    automatically.</div>
                            <?php else: ?>
                                <div class="login-captcha-note">Captcha will be enabled after reCAPTCHA keys are configured.
                                </div>
                            <?php endif; ?>
                        </div>
                        <input type="submit" value="Sign In" class="login-submit-btn" id="admin-login-submit">
                    </form>
                </div>

                <div id="client-login-panel" class="login-panel">
                    <form action="login.php" method="post" id="client-login-form">
                        <label class="message-view" style="display: none;">Invalid Username or Password</label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" class="clientemail" id="clientemail" name="clientemail"
                                placeholder="Client Email" required>
                        </div>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" class="clientcid" id="clientcid" name="clientcid"
                                placeholder="Password" required>
                            <button type="button" class="password-toggle" id="toggleClientPassword">
                                <i class="far fa-eye"></i>
                            </button>
                        </div>
                        <div class="login-captcha-wrap">
                            <?php if ($recaptchaBypass): ?>
                                <div class="login-captcha-note">Captcha is temporarily disabled on this environment.</div>
                            <?php elseif ($recaptchaReady && (($recaptchaConfig['version'] ?? 'v2_checkbox') === 'v2_checkbox')): ?>
                                <div id="client-login-recaptcha"></div>
                            <?php elseif ($recaptchaReady): ?>
                                <input type="hidden" name="g-recaptcha-response" id="client-login-recaptcha-token">
                                <div class="login-captcha-note">Login protection is active. Your request will be verified
                                    automatically.</div>
                            <?php else: ?>
                                <div class="login-captcha-note">Captcha will be enabled after reCAPTCHA keys are configured.
                                </div>
                            <?php endif; ?>
                        </div>
                        <input type="submit" name="login" class="login loginmodal-submit login-submit-btn"
                            value="Sign In" id="client-login-submit">
                    </form>
                </div>
            </div>

            <p class="login-selection-note">
                <i class="fas fa-info-circle"></i> You will be redirected to the portal after successful login.
            </p>
        </div>
    </section>

    <?php include './footerSource.php'; ?>

    <script>
        const recaptchaVersion = <?php echo json_encode($recaptchaConfig['version'] ?? 'v2_checkbox'); ?>;
        const recaptchaSiteKey = <?php echo json_encode($recaptchaConfig['site_key'] ?? ''); ?>;
        const recaptchaEnabled = <?php echo json_encode($recaptchaFrontendEnabled); ?>;
        const recaptchaAction = <?php echo json_encode($recaptchaConfig['action'] ?? 'submit'); ?>;

        let adminLoginWidgetId = null;
        let clientLoginWidgetId = null;

        function loginUpdateButtonsState() {
            const adminBtn = document.getElementById('admin-login-submit');
            const clientBtn = document.getElementById('client-login-submit');

            const requiresCheckbox = !!(recaptchaEnabled && recaptchaVersion !== 'v3');

            if (!requiresCheckbox) {
                if (adminBtn) adminBtn.disabled = false;
                if (clientBtn) clientBtn.disabled = false;
                return;
            }

            if (typeof grecaptcha === 'undefined') {
                if (adminBtn) adminBtn.disabled = true;
                if (clientBtn) clientBtn.disabled = true;
                return;
            }

            const adminDone = adminLoginWidgetId !== null && grecaptcha.getResponse(adminLoginWidgetId).trim() !== '';
            const clientDone = clientLoginWidgetId !== null && grecaptcha.getResponse(clientLoginWidgetId).trim() !== '';

            if (adminBtn) adminBtn.disabled = !adminDone;
            if (clientBtn) clientBtn.disabled = !clientDone;
        }

        function loginRecaptchaOnload() {
            if (!recaptchaEnabled || recaptchaVersion === 'v3' || typeof grecaptcha === 'undefined') {
                return;
            }

            const adminContainer = document.getElementById('admin-login-recaptcha');
            const clientContainer = document.getElementById('client-login-recaptcha');

            if (adminContainer && adminLoginWidgetId === null) {
                adminLoginWidgetId = grecaptcha.render(adminContainer, {
                    sitekey: recaptchaSiteKey,
                    callback: function () { loginUpdateButtonsState(); },
                    'expired-callback': function () { loginUpdateButtonsState(); }
                });
            }

            if (clientContainer && clientLoginWidgetId === null) {
                clientLoginWidgetId = grecaptcha.render(clientContainer, {
                    sitekey: recaptchaSiteKey,
                    callback: function () { loginUpdateButtonsState(); },
                    'expired-callback': function () { loginUpdateButtonsState(); }
                });
            }

            loginUpdateButtonsState();
            window.setInterval(loginUpdateButtonsState, 800);
        }

        window.loginRecaptchaOnload = loginRecaptchaOnload;

        document.addEventListener('DOMContentLoaded', function () {
            // Password toggle functionality
            const togglePassword = document.querySelector('#togglePassword');
            const password = document.querySelector('#password');
            const toggleClientPassword = document.querySelector('#toggleClientPassword');
            const clientPassword = document.querySelector('#clientcid');

            if (togglePassword && password) {
                togglePassword.addEventListener('click', function () {
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                    this.querySelector('i').classList.toggle('fa-eye');
                    this.querySelector('i').classList.toggle('fa-eye-slash');
                });
            }

            if (toggleClientPassword && clientPassword) {
                toggleClientPassword.addEventListener('click', function () {
                    const type = clientPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                    clientPassword.setAttribute('type', type);
                    this.querySelector('i').classList.toggle('fa-eye');
                    this.querySelector('i').classList.toggle('fa-eye-slash');
                });
            }

            // Rest of the existing code
            var buttons = document.querySelectorAll('.login-tab-btn');
            var panels = document.querySelectorAll('.login-panel');
            var overlay = document.getElementById('login-loading-overlay');

            buttons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var target = this.getAttribute('data-target');
                    buttons.forEach(function (b) { b.classList.remove('active'); });
                    panels.forEach(function (p) { p.classList.remove('active'); });
                    this.classList.add('active');
                    var panel = document.querySelector(target);
                    if (panel) {
                        panel.classList.add('active');
                    }
                });
            });

            function showLoginLoader() {
                if (overlay) {
                    overlay.style.display = 'flex';
                }
            }

            function hideLoginLoader() {
                if (overlay) {
                    overlay.style.display = 'none';
                }
            }

            var forms = document.querySelectorAll('.login-panel form');
            forms.forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    var submitBtn = form.querySelector('input[type="submit"]');
                    if (recaptchaEnabled && recaptchaVersion === 'v3') {
                        var tokenField = form.querySelector('input[name="g-recaptcha-response"]');
                        if (tokenField) {
                            e.preventDefault();
                            if (submitBtn) {
                                submitBtn.disabled = true;
                            }
                            showLoginLoader();
                            if (!recaptchaSiteKey || typeof grecaptcha === 'undefined') {
                                hideLoginLoader();
                                if (submitBtn) {
                                    submitBtn.disabled = false;
                                }
                                alert('Captcha is not ready yet. Please wait a moment and try again.');
                                return;
                            }
                            grecaptcha.ready(function () {
                                grecaptcha.execute(recaptchaSiteKey, { action: recaptchaAction }).then(function (token) {
                                    tokenField.value = token;
                                    form.submit();
                                }).catch(function () {
                                    hideLoginLoader();
                                    if (submitBtn) {
                                        submitBtn.disabled = false;
                                    }
                                    alert('Captcha verification failed. Please try again.');
                                });
                            });
                            return;
                        }
                    }

                    if (submitBtn && submitBtn.disabled) {
                        e.preventDefault();
                        return;
                    }

                    if (submitBtn) {
                        submitBtn.disabled = true;
                    }
                });
            });

            loginUpdateButtonsState();
        });
    </script>
</body>

</html>
