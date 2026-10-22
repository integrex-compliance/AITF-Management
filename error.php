<?php
/**
 * Minimalist Dark Error Page
 * Design inspired by modern SaaS error pages (Black screen, Top-Left Brand Logo, Center Artwork, Single Return to Home button)
 * Standalone, ultra-fast, zero external dependencies.
 */

// 1. Determine Base Path (Works on localhost /public_html/ and production root /)
$docRoot = realpath((string)($_SERVER['DOCUMENT_ROOT'] ?? ''));
$appRoot = realpath(__DIR__);
$basePath = '';
if ($docRoot !== false && $appRoot !== false && strpos(strtolower($appRoot), strtolower($docRoot)) === 0) {
    $rel = trim(str_replace('\\', '/', substr($appRoot, strlen($docRoot))), '/');
    $basePath = $rel !== '' ? '/' . $rel : '';
}

// 2. HTTP Error Definitions (English Only)
$httpErrors = [
    400 => [
        'title' => 'Bad Request',
        'headline' => 'Invalid Request',
        'description' => 'The server could not understand your request due to invalid syntax. Please check the URL or return to home.'
    ],
    401 => [
        'title' => 'Unauthorized',
        'headline' => 'Authentication Required',
        'description' => 'You need valid credentials to access this protected area. Please return to home or sign in.'
    ],
    403 => [
        'title' => 'Forbidden',
        'headline' => 'Access Restricted',
        'description' => 'You do not have permission to view or access this resource. Please return to home.'
    ],
    404 => [
        'title' => 'Page Not Found',
        'headline' => 'What are you looking for?',
        'description' => "The page you're trying to reach doesn't exist or may have been moved. Please check the URL or return to home."
    ],
    405 => [
        'title' => 'Method Not Allowed',
        'headline' => 'Method Not Allowed',
        'description' => 'The request method used is not supported for this address. Please return to home.'
    ],
    408 => [
        'title' => 'Request Timeout',
        'headline' => 'Request Timeout',
        'description' => 'The server timed out waiting for the request to complete. Please retry or return to home.'
    ],
    410 => [
        'title' => 'Gone',
        'headline' => 'Resource Removed',
        'description' => 'This resource has been permanently retired and is no longer available.'
    ],
    429 => [
        'title' => 'Too Many Requests',
        'headline' => 'Too Many Requests',
        'description' => 'You have sent too many requests in a short duration. Please pause and return to home.'
    ],
    500 => [
        'title' => 'Internal Server Error',
        'headline' => 'Something Went Wrong',
        'description' => 'Our servers encountered an unexpected condition while processing this request. Please return to home.'
    ],
    502 => [
        'title' => 'Bad Gateway',
        'headline' => 'Bad Gateway',
        'description' => 'The server received an invalid response from an upstream server. Please return to home.'
    ],
    503 => [
        'title' => 'Service Unavailable',
        'headline' => 'Service Temporarily Paused',
        'description' => 'We are performing routine maintenance and optimizations. Please check back shortly or return to home.'
    ],
    504 => [
        'title' => 'Gateway Timeout',
        'headline' => 'Gateway Timeout',
        'description' => 'The upstream server took too long to respond. Please try again later or return to home.'
    ],
];

// 3. Resolve HTTP Status Code
$code = 404;
if (isset($_SERVER['REDIRECT_STATUS']) && (int)$_SERVER['REDIRECT_STATUS'] >= 400 && (int)$_SERVER['REDIRECT_STATUS'] < 600) {
    $code = (int)$_SERVER['REDIRECT_STATUS'];
} elseif (isset($_GET['code']) && (int)$_GET['code'] >= 400 && (int)$_GET['code'] < 600) {
    $code = (int)$_GET['code'];
} elseif (isset($_GET['status']) && (int)$_GET['status'] >= 400 && (int)$_GET['status'] < 600) {
    $code = (int)$_GET['status'];
} elseif (isset($errorCode) && (int)$errorCode >= 400 && (int)$errorCode < 600) {
    $code = (int)$errorCode;
}

$errorInfo = $httpErrors[$code] ?? [
    'title' => 'Error',
    'headline' => 'An Unexpected Error Occurred',
    'description' => 'The server encountered an error while processing your request. Please return to home.'
];

// Custom message if passed
$customMessage = '';
if (isset($errorMessage) && trim($errorMessage) !== '') {
    $customMessage = trim($errorMessage);
} elseif (isset($_GET['msg']) && trim($_GET['msg']) !== '') {
    $customMessage = trim($_GET['msg']);
} elseif (isset($_SESSION['error_message']) && trim($_SESSION['error_message']) !== '') {
    $customMessage = trim($_SESSION['error_message']);
    unset($_SESSION['error_message']);
}

// 4. Send Proper HTTP Response Header
if (!headers_sent()) {
    http_response_code($code);
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("Cache-Control: no-cache, no-store, must-revalidate");
}

// 5. Detect JSON / AJAX Requests
$acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
$isJsonRequest = (
    strpos($acceptHeader, 'application/json') !== false ||
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
    (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false)
);

if ($isJsonRequest && !isset($_GET['html'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => $code,
            'status' => $errorInfo['title'],
            'message' => $customMessage !== '' ? $customMessage : $errorInfo['description'],
            'path' => htmlspecialchars($_SERVER['REQUEST_URI'] ?? '', ENT_QUOTES, 'UTF-8'),
            'timestamp' => gmdate('c')
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

$homeUrl = $basePath !== '' ? $basePath . '/' : '/';
$illustrationUrl = $basePath . '/aitfImages/error-image.webp';
$hasIllustration = file_exists(__DIR__ . '/aitfImages/error-image.webp');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $code; ?> - <?php echo htmlspecialchars($errorInfo['title']); ?> | AITF</title>
    <link rel="shortcut icon" href="<?php echo $basePath; ?>/aitfImages/favico/favicon.ico" type="image/x-icon">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $basePath; ?>/aitfImages/favico/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            min-height: 100vh;
            background-color: #000000;
            color: #ffffff;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Top Left Header Bar (Logo Only) */
        .top-brand-bar {
            width: 100%;
            padding: 32px 48px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            z-index: 10;
        }

        .brand-link {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .brand-link:hover {
            opacity: 0.9;
            transform: scale(1.02);
        }

        .brand-logo-img {
            height: 54px;
            width: auto;
            max-width: 220px;
            display: block;
            object-fit: contain;
        }

        /* Center Error Section */
        .error-main-area {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px 24px 60px 24px;
            text-align: center;
            z-index: 10;
        }

        .error-center-card {
            max-width: 720px;
            width: 100%;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            animation: fadeInCenter 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes fadeInCenter {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Illustration Artwork */
        .error-artwork-frame {
            max-width: 440px;
            width: 100%;
            background: #fff4ef;
            border-radius: 26px;
            padding: 14px;
            margin-bottom: 34px;
            box-shadow: 0 25px 60px -10px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            animation: floatArt 6s ease-in-out infinite;
        }

        @keyframes floatArt {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }

        .error-artwork-img {
            width: 100%;
            height: auto;
            max-height: 340px;
            object-fit: contain;
            border-radius: 18px;
            display: block;
        }

        /* Fallback icon if image missing */
        .error-fallback-icon {
            font-size: 80px;
            color: #f59e0b;
            padding: 40px;
        }

        /* Headline */
        .error-headline {
            font-size: clamp(2.2rem, 5.2vw, 3.4rem);
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.035em;
            line-height: 1.15;
            margin-bottom: 16px;
        }

        /* Description */
        .error-description {
            font-size: clamp(1.02rem, 1.8vw, 1.18rem);
            line-height: 1.65;
            color: #94a3b8;
            max-width: 580px;
            margin: 0 auto 32px auto;
            font-weight: 400;
        }

        /* Custom Alert message if available */
        .custom-alert {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.35);
            border-radius: 12px;
            padding: 12px 20px;
            color: #fca5a5;
            font-size: 0.95rem;
            margin-bottom: 24px;
            max-width: 500px;
        }

        /* Return to Homepage Button */
        .return-home-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            font-size: 15.5px;
            font-weight: 600;
            padding: 14px 34px;
            border-radius: 9999px;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 8px 24px -4px rgba(37, 99, 235, 0.5);
            letter-spacing: 0.2px;
        }

        .return-home-btn:hover {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            transform: translateY(-2px);
            box-shadow: 0 12px 28px -4px rgba(37, 99, 235, 0.7);
            color: #ffffff;
        }

        .return-home-btn i {
            font-size: 16px;
        }

        @media (max-width: 768px) {
            .top-brand-bar {
                padding: 24px 24px 10px 24px;
            }
            .brand-logo-img {
                height: 44px;
            }
            .error-artwork-frame {
                max-width: 340px;
                margin-bottom: 24px;
            }
            .return-home-btn {
                width: 100%;
                max-width: 320px;
            }
        }
    </style>
</head>
<body>

    <!-- Top Left Brand Logo (No Navbar, No Menu) -->
    <header class="top-brand-bar">
        <a href="<?php echo htmlspecialchars($homeUrl); ?>" class="brand-link" title="Return to AITF Homepage">
            <img src="<?php echo htmlspecialchars($basePath); ?>/aitfImages/bg_change_logo.png" 
                 onerror="this.onerror=null;this.src='<?php echo htmlspecialchars($basePath); ?>/aitfImages/newlogo.png';" 
                 alt="AITF Logo" 
                 class="brand-logo-img">
        </a>
    </header>

    <!-- Center Error Content Area -->
    <main class="error-main-area">
        <div class="error-center-card">
            
            <!-- Hero Illustration (error-image.webp) -->
            <?php if ($hasIllustration): ?>
                <div class="error-artwork-frame">
                    <img src="<?php echo htmlspecialchars($illustrationUrl); ?>" alt="<?php echo $code; ?> Error Illustration" class="error-artwork-img" loading="eager">
                </div>
            <?php else: ?>
                <div class="error-artwork-frame">
                    <i class="fas fa-exclamation-triangle error-fallback-icon"></i>
                </div>
            <?php endif; ?>

            <!-- Headline (What are you looking for?) -->
            <h1 class="error-headline"><?php echo htmlspecialchars($errorInfo['headline']); ?></h1>

            <!-- Description -->
            <p class="error-description"><?php echo htmlspecialchars($errorInfo['description']); ?></p>

            <?php if ($customMessage !== ''): ?>
                <div class="custom-alert">
                    <span><?php echo htmlspecialchars($customMessage); ?></span>
                </div>
            <?php endif; ?>

            <!-- Single Return to Homepage Button -->
            <a href="<?php echo htmlspecialchars($homeUrl); ?>" class="return-home-btn">
                <i class="fas fa-home"></i>
                <span>Return to Homepage</span>
            </a>

        </div>
    </main>

</body>
</html>
