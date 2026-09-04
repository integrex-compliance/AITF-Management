<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php include __DIR__ . '/headsource.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary-color: #f59e0b;
            --accent-color: #3b82f6;
            --success-color: #10b981;
            --light-gray: #f8fafc;
            --medium-gray: #e2e8f0;
            --dark-gray: #1e293b;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.7;
            color: var(--text-primary);
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .validation-header-section {
            padding: 160px 0 80px;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            margin-bottom: -120px;
            position: relative;
            z-index: 1;
        }
        
        .validation-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            padding: 3rem;
            margin: 0 auto 3rem;
            max-width: 700px;
            width: 90%;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.5);
            z-index: 2;
        }
        
        .validation-container::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
        }
        
        .page-title {
            color: var(--dark-gray);
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 700;
            font-size: 2.2rem;
            position: relative;
            padding-bottom: 1rem;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.2;
        }
        
        .page-title::after {
            content: '';
            display: block;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            margin: 1rem auto 0;
            border-radius: 2px;
        }
        
        .form-description {
            text-align: center;
            color: var(--text-secondary);
            margin-bottom: 2.5rem;
            font-size: 1.1em;
            line-height: 1.7;
            max-width: 90%;
            margin-left: auto;
            margin-right: auto;
        }
        
        .form-group {
            margin-bottom: 1.75rem;
            position: relative;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark-gray);
            font-size: 0.95rem;
            transition: var(--transition);
        }
        
        .form-control {
            width: 100%;
            padding: 0.85rem 1.25rem;
            border: 2px solid var(--medium-gray);
            border-radius: 10px;
            font-size: 1rem;
            transition: var(--transition);
            background-color: var(--light-gray);
            color: var(--dark-gray);
            font-family: inherit;
        }
        
        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
            outline: none;
            background-color: white;
        }
        
        .form-control::placeholder {
            color: #94a3b8;
            opacity: 0.8;
        }
        
        /* Error alert for invalid certificate details */
        .validation-error-alert {
            margin-bottom: 1.75rem;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            background: #fee2e2;
            color: #b91c1c;
            font-size: 1rem;
            font-weight: 500;
            border-left: 6px solid #dc2626;
            box-shadow: 0 8px 18px rgba(248, 113, 113, 0.35);
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            animation: shake-alert 0.3s ease-out, fadeIn 0.5s ease-out;
        }

        .validation-error-icon {
            font-size: 1.15rem;
            margin-top: 0.05rem;
        }

        /* Autocomplete dropdown for organization name */
        .suggestions-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 50;
            background-color: #ffffff;
            border: 1px solid var(--medium-gray);
            border-radius: 0 0 10px 10px;
            max-height: 220px;
            overflow-y: auto;
            box-shadow: var(--shadow-md);
        }

        .suggestions-item {
            padding: 0.6rem 1rem;
            cursor: pointer;
            font-size: 0.95rem;
            color: var(--dark-gray);
        }

        .suggestions-item:hover {
            background-color: #eff6ff;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            border: none;
            padding: 1rem 2.5rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: auto;
            min-width: 200px;
            margin: 1.5rem auto 0;
            transition: var(--transition);
            text-transform: none;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2);
        }
        
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
        }
        
        .btn-submit:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(37, 99, 235, 0.3);
        }
        
        .btn-submit i {
            margin-right: 8px;
            font-size: 1.1em;
        }
        
        /* Decorative elements */
        .decoration {
            position: absolute;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0) 70%);
            z-index: -1;
        }
        
        .decoration-1 {
            top: -100px;
            right: -100px;
            width: 300px;
            height: 300px;
        }
        
        .decoration-2 {
            bottom: -50px;
            left: -50px;
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0) 70%);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .validation-container {
                padding: 2rem 1.5rem;
                margin: 1.5rem auto;
                width: 95%;
            }
            
            .page-title {
                font-size: 1.8rem;
                margin-bottom: 1.25rem;
            }
            
            .form-description {
                font-size: 1rem;
                margin-bottom: 2rem;
            }
            
            .btn-submit {
                width: 100%;
                max-width: 100%;
                padding: 0.9rem;
            }
        }
        
        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes shake-alert {
            0% { transform: translateX(0); }
            20% { transform: translateX(-4px); }
            40% { transform: translateX(4px); }
            60% { transform: translateX(-2px); }
            80% { transform: translateX(2px); }
            100% { transform: translateX(0); }
        }
        
        .validation-container {
            animation: fadeIn 0.6s ease-out forwards;
        }
    </style>
</head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <head>
        <?php include './headerSource.php'; ?>
    </head>
    
    <div class="validation-header-section text-center">
        <div class="container">
            <h1 style="color: white; font-weight: 800; font-size: 3rem; text-transform: uppercase; letter-spacing: 2px;">Validation</h1>
            <p style="color: rgba(255,255,255,0.9); font-size: 1.2rem;">Verify your AITF certification status</p>
        </div>
    </div>

    <div class="container" style="position: relative; z-index: 10;">
        <div class="decoration decoration-1"></div>
        <div class="decoration decoration-2"></div>
        
        <div class="validation-container">
            <?php if (isset($_SESSION['validation_error']) && $_SESSION['validation_error'] === 'invalid_client_warning') { unset($_SESSION['validation_error']); ?>
                <div class="validation-error-alert">
                    <span class="validation-error-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </span>
                    <span>
                        <strong>Validation failed.</strong><br>
                        The entered organization name or certificate number could not be found. Please verify the details and try again.
                    </span>
                </div>
            <?php } ?>
            <h1 class="page-title">Certificate Validation</h1>
            
            <p class="form-description">
                Verify the current status of your AITF certification by entering your organization's details and certificate number below.
            </p>
            
            <form action="login.php" method="post" class="needs-validation" novalidate>
                <?php if (!isset($errorC) || $errorC == true) { ?>
                    <div class="form-group">
                        <label for="name" class="form-label">Organization Name <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input name="name" class="form-control pl-12" type="text" id="name" value="" 
                                   placeholder="Enter organization name" autocomplete="organization" required>
                            <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                <i class="fas fa-building"></i>
                            </div>
                            <div id="orgSuggestions" class="suggestions-dropdown" style="display:none;"></div>
                        </div>
                        <div class="text-sm text-gray-500 mt-1">Enter the exact name as it appears on your certificate</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="cno" class="form-label">Certificate Number <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input name="cno" class="form-control pl-12" type="text" id="cno" value="" 
                                   placeholder="Enter certificate number" autocomplete="off" required>
                            <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                <i class="fas fa-certificate"></i>
                            </div>
                        </div>
                        <div class="text-sm text-gray-500 mt-1">Enter the certificate number without any special characters</div>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn-submit" name="Send" id="send">
                            <i class="fas fa-search"></i> Validate Certificate
                        </button>
                    </div>
                    
                    <div class="mt-6 text-center text-sm text-gray-500">
                        <p>Need help? <a href="contact.php" class="text-primary-600 hover:text-primary-800 font-medium">Contact our support team</a></p>
                    </div>
                <?php } ?>
            </form>
        </div>
    </div>
    
    <script>
    // Form validation
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            var forms = document.getElementsByClassName('needs-validation');
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
    
    // Add animation on input focus
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.querySelector('label').style.transform = 'translateY(-5px)';
            this.parentElement.querySelector('label').style.fontSize = '0.85rem';
            this.parentElement.querySelector('label').style.color = 'var(--primary-color)';
        });
        
        input.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.querySelector('label').style.transform = '';
                this.parentElement.querySelector('label').style.fontSize = '';
                this.parentElement.querySelector('label').style.color = '';
            }
        });
    });

    // Autocomplete client search on Organization Name
    (function() {
        var nameInput = document.getElementById('name');
        var suggestions = document.getElementById('orgSuggestions');
        if (!nameInput || !suggestions) {
            return;
        }

        var debounceTimer = null;

        nameInput.addEventListener('input', function() {
            var query = this.value.trim();

            if (debounceTimer) {
                clearTimeout(debounceTimer);
            }

            if (query.length === 0) {
                suggestions.style.display = 'none';
                suggestions.innerHTML = '';
                return;
            }

            debounceTimer = setTimeout(function() {
                fetch('autoSearch.php?customersearch=' + encodeURIComponent(query))
                    .then(function(response) { return response.json(); })
                    .then(function(data) {
                        suggestions.innerHTML = '';

                        if (!Array.isArray(data) || data.length === 0) {
                            suggestions.style.display = 'none';
                            return;
                        }

                        data.forEach(function(item) {
                            if (!item || !item.customer_name) {
                                return;
                            }
                            var div = document.createElement('div');
                            div.className = 'suggestions-item';
                            div.textContent = item.customer_name;
                            div.addEventListener('click', function() {
                                nameInput.value = item.customer_name;
                                suggestions.style.display = 'none';
                                suggestions.innerHTML = '';
                                nameInput.focus();
                            });
                            suggestions.appendChild(div);
                        });

                        suggestions.style.display = 'block';
                    })
                    .catch(function() {
                        suggestions.style.display = 'none';
                        suggestions.innerHTML = '';
                    });
            }, 200);
        });

        // Close suggestions when clicking outside
        document.addEventListener('click', function(event) {
            if (!suggestions.contains(event.target) && event.target !== nameInput) {
                suggestions.style.display = 'none';
            }
        });
    })();
    </script>

    <?php include './footerSource.php'; ?>

</body>
</html>
