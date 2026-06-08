<?php
$pageIdentifier = "Inbox";
$addInboxClass = "active";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Redirecting to GoDaddy...</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            background-color: #f5f7f9;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .loader-container {
            text-align: center;
            background: #ffffff;
            padding: 40px 60px;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            animation: fadeIn 0.5s ease-out;
        }

        .godaddy-logo {
            font-size: 32px;
            font-weight: 700;
            color: #111;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .godaddy-logo span {
            color: #000;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(0, 164, 166, 0.2);
            border-top: 4px solid #00a4a6; /* GoDaddy teal-ish color or just standard dark */
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px auto;
        }

        .message {
            color: #333;
            font-size: 18px;
            font-weight: 500;
            margin: 0;
        }

        .sub-message {
            color: #777;
            font-size: 14px;
            margin-top: 8px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="loader-container">
        <div class="godaddy-logo">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Generic modern mail/cloud icon to represent workspace -->
                <path d="M4 7.00005L10.2 11.65C11.2667 12.45 12.7333 12.45 13.8 11.65L20 7" stroke="#111" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <rect x="3" y="5" width="18" height="14" rx="2" stroke="#111" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span>GoDaddy</span>
        </div>
        <div class="spinner"></div>
        <p class="message">Redirecting you to GoDaddy</p>
        <p class="sub-message">Workspace Webmail is loading...</p>
    </div>

    <script>
        setTimeout(function() {
            window.location.href = 'godaddy_mail.php';
        }, 3500);
    </script>
</body>
</html>
