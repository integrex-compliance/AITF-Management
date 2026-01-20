<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './headSource.php'; ?>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
        
        /* New Floating Contact Button */
        .floating-contact {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 15px;
            align-items: flex-end;
        }
        
        .floating-contact-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 5px 25px rgba(37, 99, 235, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            position: relative;
            z-index: 10;
        }
        
        .floating-contact-btn:hover {
            transform: scale(1.1) rotate(10deg);
            box-shadow: 0 8px 30px rgba(37, 99, 235, 0.4);
        }
        
        .floating-contact-options {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 15px;
            transform: translateY(20px);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .floating-contact.active .floating-contact-options {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }
        
        .floating-option {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--primary);
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .floating-option.whatsapp {
            background: #25D366;
            color: white;
        }
        
        .floating-option.phone {
            background: #FF5722;
            color: white;
        }
        
        .floating-option.email {
            background: #4CAF50;
            color: white;
        }
        
        .floating-option:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        :root {
            --primary: #2563eb;
            --primary-light: #3b82f6;
            --primary-dark: #1e40af;
            --secondary: #1e40af;
            --accent: #3b82f6;
            --text: #1f2937;
            --text-light: #4b5563;
            --light: #f8fafc;
            --white: #ffffff;
            --border: #e5e7eb;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 15px 30px -5px rgba(37, 99, 235, 0.15);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --radius: 16px;
            --gradient: linear-gradient(135deg, var(--primary), var(--primary-dark));
            --gradient-hover: linear-gradient(135deg, var(--primary-dark), var(--primary));
            --font: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font);
            background-color: var(--light);
            color: var(--text);
            line-height: 1.6;
            font-size: 1rem;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden;
        }
        
        /* Header Section */
        .contact-hero {
            background: var(--gradient);
            color: white;
            padding: 140px 2rem 100px;
            text-align: center;
            position: relative;
            overflow: hidden;
            margin-bottom: 0;
            clip-path: polygon(0 0, 100% 0, 100% 90%, 0 100%);
            animation: gradientBG 15s ease infinite;
            background-size: 200% 200%;
        }
        
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .contact-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29-22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 1;
            z-index: 1;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            line-height: 1.15;
            letter-spacing: -1px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.1);
            animation: fadeInUp 0.8s ease-out;
            position: relative;
            display: inline-block;
        }
        
        .hero-content h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: rgba(255,255,255,0.5);
            border-radius: 2px;
        }
        
        .hero-content p {
            font-size: 1.25rem;
            max-width: 700px;
            margin: 1.5rem auto 0;
            opacity: 0.9;
            font-weight: 400;
            line-height: 1.8;
            animation: fadeInUp 0.8s ease-out 0.2s both;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        /* Utility Classes */
        .text-center {
            text-align: center;
        }
        
        .mt-1 { margin-top: 0.5rem; }
        .mt-2 { margin-top: 1rem; }
        .mt-3 { margin-top: 1.5rem; }
        .mt-4 { margin-top: 2rem; }
        .mt-5 { margin-top: 3rem; }
        
        /* Contact Grid */
        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 1.5rem;
            position: relative;
        }
        
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            margin-top: -40px;
            position: relative;
            z-index: 10;
        }
        
        @media (min-width: 768px) {
            .contact-grid {
                grid-template-columns: 1fr 1fr;
                gap: 2.5rem;
            }
            
            .hero-content h1 {
                font-size: 3.5rem;
            }
        }
        
        /* Contact Card */
        .contact-card {
            background: var(--white);
            border-radius: var(--radius);
            padding: 2.5rem;
            box-shadow: var(--shadow-md);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
            height: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .contact-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--gradient);
            transition: all 0.3s ease;
            z-index: -1;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .contact-card:hover::before {
            width: 100%;
            opacity: 0.05;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .card-header {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .card-header h2 {
            font-size: 1.5rem;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0;
        }
        
        .card-header i {
            font-size: 1.75rem;
            color: var(--primary);
        }
        
        @media (min-width: 1536px) {
            .contact-container {
                max-width: 1440px;
            }
        }
        
        .contact-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgcGF0dGVyblRyYW5zZm9ybT0icm90YXRlKDQ1KSI+PHJlY3Qgd2lkdGg9IjUwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJyZ2JhKDI1NSwyNTUsMjU1LDAuMDMpIi8+PC9wYXR0ZXNuPjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJ1cmwoI3BhdHRlcm4pIi8+PC9zdmc+');
            opacity: 0.3;
        }
        
        /* Map Section */
        .map-section {
            margin: 5rem 0 0;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
            position: relative;
        }
        
        .map-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(37, 99, 235, 0.05) 100%);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 1;
            pointer-events: none;
        }
        
        .map-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(37, 99, 235, 0.15);
        }
        
        .map-section:hover::before {
            opacity: 0.3;
        }
        
        .map-container {
            width: 100%;
            height: 400px;
            border: none;
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive Adjustments */
        @media (max-width: 1023px) {
            .contact-hero {
                padding: 120px 1.5rem 80px;
                clip-path: polygon(0 0, 100% 0, 100% 95%, 0 100%);
            }
            
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .hero-content p {
                font-size: 1.1rem;
            }
            
            .contact-grid {
                margin-top: -40px;
            }
            
            .contact-card,
            .direct-contact {
                padding: 2rem 1.5rem;
            }
            
            .card-header h2 {
                font-size: 1.4rem;
            }
            
            .direct-contact h3 {
                font-size: 1.4rem;
            }
            
            .map-section {
                margin-top: 2.5rem;
                border-radius: 12px;
                overflow: hidden;
            }
            
            .map-container {
                height: 300px;
            }
            
            .email-btn {
                padding: 0.9rem 1.75rem;
                font-size: 1rem;
                max-width: 100%;
            }
        }
        
        @media (max-width: 639px) {
            .contact-hero {
                padding: 100px 1.25rem 60px;
            }
            
            .hero-content h1 {
                font-size: 2rem;
            }
            
            .hero-content p {
                font-size: 1rem;
            }
            
            .contact-item {
                padding: 1rem;
                flex-direction: column;
                text-align: center;
            }
            
            .contact-icon {
                margin: 0 auto 1rem;
            }
            
            .contact-text {
                width: 100%;
            }
            
            .email-btn {
                padding: 0.85rem 1.5rem;
                font-size: 0.95rem;
            }
        }
        
        /* Business Hours */
        .business-hours {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px dashed rgba(0,0,0,0.1);
        }
        
        .business-hours h3 {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }
        
        .hours-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .hours-list li {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-size: 0.95rem;
        }
        
        .hours-list li span:first-child {
            font-weight: 500;
            color: var(--text);
        }
        
        .hours-list li span:last-child {
            color: var(--text-light);
        }
        
        /* Direct Contact Section */
        .direct-contact {
            background: var(--white);
            border-radius: var(--radius);
            padding: 2.5rem;
            box-shadow: var(--shadow-md);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
            height: 100%;
            display: flex;
            flex-direction: column;
            text-align: center;
            border: 1px solid rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }
        
        .direct-contact::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--gradient);
            transition: all 0.3s ease;
        }
        
        .direct-contact:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .direct-contact:hover::before {
            height: 5px;
            background: var(--gradient-hover);
        }
        
        .direct-contact:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        
        .direct-contact-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1) 0%, rgba(37, 99, 235, 0.05) 100%);
            border-radius: 50%;
            border: 2px dashed var(--primary-light);
            animation: pulse 2s infinite, float 3s ease-in-out infinite;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            color: var(--primary);
            font-size: 2.25rem;
            transition: all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.1);
        }
        
        .direct-contact-icon::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient);
            opacity: 0;
            transition: all 0.4s ease;
            z-index: 0;
        }
        
        .direct-contact-icon i {
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }
        
        .direct-contact:hover .direct-contact-icon {
            transform: rotate(5deg) scale(1.1);
            color: white;
            box-shadow: 0 12px 25px rgba(37, 99, 235, 0.2);
        }
        
        .direct-contact:hover .direct-contact-icon::before {
            opacity: 1;
        }
        
        .direct-contact:hover .direct-contact-icon i {
            transform: scale(1.1);
        }
        
        .direct-contact:hover .direct-contact-icon {
            background: var(--primary);
            color: white;
            transform: rotate(5deg) scale(1.05);
        }
        
        @media (min-width: 1024px) {
            .contact-grid {
                grid-template-columns: 1fr 1fr;
                gap: 2.5rem;
            }
        }
        
            background: var(--gradient);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: -1;
        }
        
        .contact-item:hover .contact-icon {
            transform: rotate(5deg) scale(1.1);
            color: white;
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.2);
        }
        
        .contact-item:hover .contact-icon::before {
            opacity: 1;
        }
        
        .contact-item:hover .contact-icon {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
        }
        
        .contact-text h4 {
            font-size: 1rem;
            font-weight: 600;
            margin: 0 0 0.25rem;
            color: var(--text);
        }
        
        .contact-text p, 
        .contact-text a {
            margin: 0;
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.6;
            text-decoration: none;
            transition: var(--transition);
        }
        
        .contact-text a:hover {
            color: var(--primary);
        }
        
        .contact-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--header-gradient);
            opacity: 0.9;
        }
        
        .contact-info:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }
        
        .contact-info:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .contact-info h2 {
            color: var(--primary-color);
            margin: 0.5rem 0 2rem;
            font-size: 1.75rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            position: relative;
            padding-bottom: 1.25rem;
        }
        
        .contact-info h2 i {
            color: var(--primary-color);
            font-size: 1.5rem;
            opacity: 0.9;
        }
        
        .contact-info h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
            border-radius: 3px;
        }
        
        .contact-info h2 i {
            color: var(--accent-color);
        }
        
        .contact-details {
            margin-top: 20px;
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
            padding: 1.5rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }
        
        .contact-item:last-child {
            border-bottom: none;
            padding-bottom: 0.5rem;
        }
        
        .contact-item:last-child {
            border-bottom: none;
        }
        
        .contact-item:hover {
            transform: translateX(5px);
        }
        
        .contact-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.08), rgba(37, 99, 235, 0.12));
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.25rem;
            color: var(--primary-color);
            font-size: 1.25rem;
            flex-shrink: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .contact-icon::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 0;
        }
        
        .contact-icon i {
            position: relative;
            z-index: 1;
        }
        
        .contact-item:hover .contact-icon {
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25);
        }
        
        .contact-item:hover .contact-icon::before {
            opacity: 1;
        }
        
        .contact-text h4 {
            margin: 0 0 0.5rem 0;
            color: var(--text-color);
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .contact-text p, 
        .contact-info h4 {
            margin: 0.25rem 0;
            color: var(--text-light);
            line-height: 1.6;
        }
        
        .contact-text a, 
        .contact-text p {
            margin: 0;
            color: #6b7280;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .contact-text a:hover {
            color: var(--primary-color);
        }
        
        .view-map-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f3f4f6;
            color: #2563eb;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            margin-top: 10px;
            transition: all 0.3s ease;
            border: 1px solid #d1d5db;
        }
        
        .view-map-btn:hover {
            background: #2563eb;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }
        
        .view-map-btn i {
            font-size: 0.9em;
        }
        
        .business-hours {
            margin-top: 2.5rem;
            padding-top: 2.25rem;
            border-top: 1px dashed rgba(0, 0, 0, 0.08);
            position: relative;
        }
        
        .business-hours::before {
            content: '';
            position: absolute;
            top: -1px;
            left: 0;
            width: 80px;
            height: 2px;
            background: var(--primary-color);
            opacity: 0.6;
        }
        
        .business-hours h3 {
            font-size: 1.25rem;
            margin-bottom: 1.25rem;
            color: var(--primary-color);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .business-hours h3::before {
            content: '\f017';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: var(--primary-color);
        }
        
        .hours-list {
            list-style: none;
            padding: 0;
            margin: 1rem 0 0;
        }
        
        .hours-list li {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0.5rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
            transition: all 0.2s ease;
            border-radius: 6px;
        }
        
        .hours-list li:hover {
            background: rgba(37, 99, 235, 0.03);
            transform: translateX(4px);
        }
        
        .hours-list li:last-child {
            border-bottom: none;
        }
        
        .hours-list li span:first-child {
            font-weight: 500;
            color: var(--text-color);
            position: relative;
            padding-left: 1.5rem;
        }
        
        .hours-list li span:first-child::before {
            content: '•';
            position: absolute;
            left: 0;
            color: var(--primary-color);
            font-weight: bold;
        }
        
        .hours-list li span:last-child {
            color: var(--text-light);
        }
        
        .contact-form {
            background: white;
            border-radius: 12px;
            padding: 35px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            position: relative;
        }
        
        .contact-form:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .form-header {
            margin-bottom: 25px;
        }
        
        .form-header h2 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-header h2 i {
            color: var(--accent-color);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-color);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 14px 35px;
            border-radius: 50px;
            height: 100%;
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }
        
        .btn-submit:hover::before {
            opacity: 1;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        }
        
        .btn-submit:active {
            transform: translateY(0);
        }
        
        .btn-submit:hover {
            background: #1e3c72;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .success-message {
            background: #d1fae5;
            color: #155724;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 0.95rem;
            display: none;
            border-left: 4px solid #10b981;
            animation: fadeIn 0.5s ease-out;
            position: relative;
            padding-left: 50px;
        }
        
        .success-message::before {
            content: '✓';
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            width: 24px;
            height: 24px;
            background: #10b981;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .contact-hero {
                padding: 50px 0;
            }
            
            .contact-hero h1 {
                font-size: 2.2rem;
            }
            
            .contact-grid {
                grid-template-columns: 1fr;
            }
            
            .google-map {
                height: 300px;
            }
        }
        /* Animation Keyframes */
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
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .animate-delay-1 { animation-delay: 0.2s; }
        .animate-delay-2 { animation-delay: 0.4s; }
        .animate-delay-3 { animation-delay: 0.6s; }
        
        .animate-fade-in {
            animation: fadeIn 0.8s ease-out forwards;
        }
        
        .animate-slide-in-left {
            animation: slideInLeft 0.8s ease-out forwards;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .contact-hero {
                padding: 80px 0;
            }
            
            .contact-hero h1 {
                font-size: 2.5rem;
            }
            
            .contact-grid {
                grid-template-columns: 1fr;
                gap: 20px;
                margin-top: 0;
            }
            
            .contact-form {
                padding: 30px 20px;
            }
        }
        
        /* Success Message Animation */
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            animation: slideIn 0.5s ease-out;
            border-left: 4px solid #28a745;
        }
        
        .success-message i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #2a5298;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #1e3c72;
        }
        
        .direct-contact-section {
            margin: 0;
            padding: 2.5rem;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .direct-contact-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
            margin: 0 auto;
            max-width: 500px;
        }
        
        .direct-contact-card h3 {
            font-size: 32px;
            margin: 15px 0 10px;
            font-weight: 800;
            color: #1f2937;
            display: block;
            position: relative;
            letter-spacing: -0.5px;
        }
        
        .direct-contact-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 2.5rem;
            box-shadow: 0 15px 35px rgba(37, 99, 235, 0.3);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        
        .direct-contact-icon:hover {
            transform: rotate(5deg) scale(1.05);
            box-shadow: 0 15px 40px rgba(37, 99, 235, 0.4);
        }
        
        .direct-contact-content {
            position: relative;
            z-index: 2;
            text-align: center;
            margin: -0.5rem 0;
            display: flex;
            flex-direction: column;
            height: 100%;
            justify-content: center;
        }
        
        .direct-contact-content h3 {
            font-size: 1.3rem;
            margin: 0.5rem 0 1rem;
            color: var(--primary-color);
            line-height: 1.3;
        }
        
        .direct-contact-content p {
            color: #4b5563;
            font-size: 15px;
            line-height: 1.6;
            margin: 0.5rem 0 1.25rem;
            max-width: 90%;
            margin-left: auto;
            margin-right: auto;
            font-weight: 400;
            position: relative;
            animation: fadeInUp 0.8s ease-out 0.2s both;
            text-align: center;
        }
        
        .email-btn-container {
            margin-top: auto;
            padding-top: 0;
        }
        
        .email-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--gradient);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
            margin: 1rem 0 0.5rem;
            border: none;
            cursor: pointer;
            font-size: 1.05rem;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25);
            width: 100%;
            max-width: 280px;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .email-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0;
            height: 100%;
            background: var(--gradient-hover);
            transition: all 0.4s ease;
            z-index: -1;
        }
        
        .email-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(37, 99, 235, 0.35);
        }
        
        .email-btn:hover::before {
            width: 100%;
        }
        
        .email-btn i {
            margin-right: 0.5rem;
            font-size: 1.1em;
            transition: transform 0.3s ease;
        }
        
        .email-btn:hover i {
            transform: translateX(3px);
        }
        
        .email-note {
            font-size: 0.9rem;
            color: var(--text-light);
            margin: 0.75rem 0 0;
            line-height: 1.6;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .email-note i {
            color: var(--primary);
            font-size: 1.1em;
        }
        
        .direct-contact:hover .email-note {
            color: var(--text);
        }
        
        .btn-icon {
            margin-right: 0.75rem;
            transition: var(--transition);
        }
        
        .btn-hover {
            position: absolute;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            left: 0;
            top: 0;
            opacity: 0;
            transform: translateY(10px);
            transition: var(--transition);
        }
        
        .direct-email-btn:hover .btn-text {
            opacity: 0;
            transform: translateY(-10px);
        }
        
        .direct-email-btn:hover .btn-hover {
            opacity: 1;
            transform: translateY(0);
        }
        
        .direct-email-btn:hover .btn-icon {
            transform: translateX(5px);
        }
        
        .direct-contact-decoration {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: -1;
        }
        
        .floating-shape {
            position: absolute;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(29, 78, 216, 0.15));
            border-radius: 50%;
            animation: float 15s infinite ease-in-out;
            opacity: 0.8;
            filter: blur(20px);
            z-index: 1;
        }
        
        .shape-1 {
            width: 100px;
            height: 100px;
            top: 20%;
            left: 20%;
        }
        
        .shape-2 {
            width: 150px;
            height: 150px;
            top: 40%;
            right: 20%;
        }
        
        .shape-3 {
            width: 120px;
            height: 120px;
            bottom: 20%;
            left: 40%;
        }
        
        .shape-4 {
            width: 180px;
            height: 180px;
            bottom: 40%;
            right: 40%;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            25% {
                transform: translate(10px, -15px) rotate(5deg);
            }
            50% {
                transform: translate(-5px, -25px) rotate(-5deg);
            }
            75% {
                transform: translate(15px, -10px) rotate(3deg);
            }
        }
        
        /* Right Side Content */
        .right-side-content {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        /* Map Container */
        .map-container {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .map-container h3 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .map-container h3 i {
            color: var(--primary-color);
        }

        .map-embed {
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .map-embed iframe {
            width: 100%;
            height: 300px;
            border: none;
        }

        .map-actions {
            text-align: center;
            margin-top: 1rem;
        }

        .map-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            background: rgba(37, 99, 235, 0.1);
        }

        .map-link:hover {
            background: rgba(37, 99, 235, 0.2);
            transform: translateY(-2px);
        }

        /* Quick Contact */
        .quick-contact {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .quick-contact h3 {
            font-size: 1.25rem;
            margin-bottom: 1.25rem;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .quick-contact h3 i {
            color: var(--primary-color);
        }

        .quick-contact-item {
            display: flex;
            align-items: flex-start;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .quick-contact-item:last-child {
            border-bottom: none;
        }

        .quick-icon {
            width: 40px;
            height: 40px;
            background: rgba(37, 99, 235, 0.1);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: var(--primary-color);
            font-size: 1rem;
            flex-shrink: 0;
        }

        .quick-details h4 {
            font-size: 0.9rem;
            color: var(--text-light);
            margin: 0 0 0.25rem 0;
            font-weight: 500;
        }

        .quick-details a {
            color: var(--text-color);
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            display: inline-block;
        }

        .quick-details a:hover {
            color: var(--primary-color);
        }

        /* Right Side Content */
        .right-side-content {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        
        /* Contact Form */
        .contact-form {
            background: #fff;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
        }
        
        .form-header {
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .form-header h2 {
            color: #1e40af;
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
        }
        
        .form-header p {
            color: #6b7280;
            margin: 0;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f9fafb;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: #fff;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #2563eb, #1e40af);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            width: 100%;
            justify-content: center;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }
        
        .btn-submit:active {
            transform: translateY(0);
        }
        
        .error-message {
            color: #ef4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none;
        }
        
        .success-message {
            background: #10b981;
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Quick Contact Bar */
        .quick-contact-bar {
            background: white;
            padding: 15px 0;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            position: relative;
            z-index: 10;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .quick-contact-bar .container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .quick-contact-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .quick-contact-item:hover {
            background: rgba(37, 99, 235, 0.05);
        }
        
        .quick-contact-item i {
            width: 40px;
            height: 40px;
            background: rgba(37, 99, 235, 0.1);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 18px;
        }
        
        .quick-contact-item div {
            display: flex;
            flex-direction: column;
        }
        
        .quick-contact-item span {
            font-size: 12px;
            color: var(--text-light);
        }
        
        .quick-contact-item a {
            color: var(--text);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .quick-contact-item a:hover {
            color: var(--primary);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .contact-header {
                padding: 6rem 1.5rem 5rem;
            }
            
            .contact-header h1 {
                font-size: 2.25rem;
            }
            
            .contact-header p {
                font-size: 1.1rem;
            }
            
            .contact-grid {
                margin-top: -2rem;
            }
            
            .direct-contact-section {
                padding: 1.5rem 1.25rem;
            }
            
            .direct-contact-icon {
                width: 65px;
                height: 65px;
                font-size: 1.75rem;
            }
            
            .direct-email-btn {
                width: 100%;
                padding: 0.7rem 1.25rem;
                font-size: 0.95rem;
                margin-top: 0.5rem;
            }
        }
    </style>
</head>

<body>
    <?php include './headerSource.php'; ?>

    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="hero-content">
            <h1>Let's Start a Conversation</h1>
            <p>We're here to help and answer any questions you might have. Reach out to us and we'll respond as soon as possible.</p>
        </div>
    </section>

    <!-- Quick Contact Bar -->
    <div class="quick-contact-bar">
        <div class="quick-contact-item">
            <i class="fas fa-phone-alt"></i>
            <div>
                <span>Call Us</span>
                <a href="tel:+919881691012">+91 9881691012</a>
            </div>
        </div>
        <div class="quick-contact-item">
            <i class="fas fa-envelope"></i>
            <div>
                <span>Email Us</span>
                <a href="mailto:aitfindia@gmail.com">aitfindia@gmail.com</a>
            </div>
        </div>
        <div class="quick-contact-item">
            <i class="fas fa-map-marker-alt"></i>
            <div>
                <span>Location</span>
                <a href="#map">View on Map</a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="contact-container">
        <div class="contact-grid">
            <!-- Contact Information Card -->
            <div class="contact-card">
                <div class="card-header">
                    <h2><i class="fas fa-address-card"></i> Contact Information</h2>
                </div>
                
                <div class="contact-details">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Our Location</h4>
                            <p>Devi Indrayani Wing B 26,<br>Gat No. 382+3+4/1, Near IT Park,<br>Talwade, Pune - 411 062<br>Maharashtra, India</p>
                            <a href="https://www.google.com/maps?q=Devi+Indrayani+Wing+B+26,+Gat+No.+382%2B3%2B4%2F1,+Near+IT+Park,+Talwade,+Pune,+Maharashtra+411062" target="_blank" class="text-primary">
                                <i class="fas fa-map-marker-alt"></i> View on Google Maps
                            </a>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Phone Number</h4>
                            <p><a href="tel:+919881691012">+91 9881691012</a></p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-text">
                            <h4>Email Address</h4>
                            <p><a href="mailto:aitfindia@gmail.com">aitfindia@gmail.com</a></p>
                            <p><a href="index.html" target="_blank">www.aitf.co.in</a></p>
                        </div>
                    </div>
                    
                    <div class="business-hours">
                        <h3><i class="far fa-clock"></i> Business Hours</h3>
                        <ul class="hours-list">
                            <li><span>Friday - Wednesday</span> <span>9:30 AM - 6:30 PM</span></li>
                            <li><span>Thursday</span> <span>9:30 AM - 2:30 PM</span></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Map Section -->
            <div class="map-section">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d30265.71551502028!2d73.7898802!3d18.5246034!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bc2bff5e0d5fc5d%3A0x4f5a9a6e6c2b0b0b7!2sTalwade%2C%20Pimpri-Chinchwad%2C%20Maharashtra!5e0!3m2!1sen!2sin!4v1620000000000!5m2!1sen!2sin" 
                    width="100%" 
                    height="450" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy"
                    class="map-container">
                </iframe>
            </div>
            
            <!-- Direct Contact Card -->
            <div class="direct-contact">
                <div class="direct-contact-icon">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <h3>Let's Start a Conversation</h3>
                <p class="animate-p">We're here to help and answer any questions you might have. Drop us a message and we'll get back to you as soon as possible!</p>
                <p>Prefer to email us directly? Click the button below to compose an email in your default email client.</p>
                
                <div class="mt-4">
                    <button onclick="openEmailClient()" class="email-btn">
                        <i class="fas fa-paper-plane"></i> Compose Email
                    </button>
                    <p class="email-note">
                        <i class="fas fa-info-circle"></i> We typically respond within 24 hours
                    </p>
                    <p class="email-note">
                        <i class="fas fa-external-link-alt"></i> Opens in your default email client
                    </p>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Floating Contact Button -->
    <div class="floating-contact">
        <div class="floating-contact-options">
            <a href="https://wa.me/919881691012" class="floating-option whatsapp" target="_blank" title="Chat on WhatsApp">
                <i class="fab fa-whatsapp"></i>
            </a>
            <a href="tel:+919881691012" class="floating-option phone" title="Call Us">
                <i class="fas fa-phone"></i>
            </a>
            <a href="mailto:aitfindia@gmail.com" class="floating-option email" title="Email Us">
                <i class="fas fa-envelope"></i>
            </a>
        </div>
        <button class="floating-contact-btn" id="contactToggle">
            <i class="fas fa-comment-dots"></i>
        </button>
    </div>

    <?php include './footerSource.php'; ?>
    
    <script>
        // Toggle floating contact options
        const contactToggle = document.getElementById('contactToggle');
        const floatingContact = document.querySelector('.floating-contact');
        
        contactToggle.addEventListener('click', () => {
            floatingContact.classList.toggle('active');
        });
        
        // Close floating menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!floatingContact.contains(e.target) && !e.target.closest('.floating-contact')) {
                floatingContact.classList.remove('active');
            }
        });
        
        // Add animation to contact items on scroll
        const animateOnScroll = () => {
            const items = document.querySelectorAll('.contact-item, .direct-contact');
            items.forEach(item => {
                const itemTop = item.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;
                
                if (itemTop < windowHeight - 100) {
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }
            });
        };
        
        // Initial animation
        document.addEventListener('DOMContentLoaded', () => {
            const items = document.querySelectorAll('.contact-item, .direct-contact');
            items.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                item.style.transition = `all 0.5s ease ${index * 0.1}s`;
            });
            
            // Trigger animation after a short delay
            setTimeout(animateOnScroll, 300);
        });
        
        // Animate on scroll
        window.addEventListener('scroll', animateOnScroll);
    </script>
    
    <script>
        function openEmailClient() {
            const subject = "Enquiry from AITF Website";
            const email = "aitfindia@gmail.com";
            window.location.href = `mailto:${email}?subject=${encodeURIComponent(subject)}`;
        }
        // Function to open Gmail compose in new tab
        function openEmailClient() {
            const email = 'aitfindia@gmail.com';
            const subject = 'Enquiry from AITF Website';
            const body = 'Dear AITF Team,%0D%0A%0D%0AI would like to inquire about your services.%0D%0A%0D%0ARegards,';
            
            // Open Gmail compose in new tab
            window.open(`https://mail.google.com/mail/?view=cm&fs=1&to=${email}&su=${encodeURIComponent(subject)}&body=${body}`, '_blank');
            return false;
        }
        
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('contactForm');
            
            if (form) {
                // Show success message if form was submitted successfully
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('success') === 'true') {
                    const successMessage = document.createElement('div');
                    successMessage.className = 'success-message';
                    successMessage.textContent = 'Your message has been sent successfully! We\'ll get back to you soon.';
                    form.insertBefore(successMessage, form.firstChild);
                    
                    // Scroll to show the success message
                    setTimeout(() => {
                        successMessage.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 100);
                }
                
                form.addEventListener('submit', function(e) {
                    // Reset error messages
                    document.querySelectorAll('.error-message').forEach(el => {
                        el.style.display = 'none';
                    });
                    
                    let isValid = true;
                    const name = document.getElementById('name');
                    const email = document.getElementById('email');
                    const message = document.getElementById('message');
                    
                    // Validate name
                    if (!name.value.trim()) {
                        showError('name-error', 'Please enter your name');
                        isValid = false;
                    }
                    
                    // Validate email
                    if (!email.value.trim()) {
                        showError('email-error', 'Please enter your email');
                        isValid = false;
                    } else if (!isValidEmail(email.value)) {
                        showError('email-error', 'Please enter a valid email address');
                        isValid = false;
                    }
                    
                    // Validate message
                    if (!message.value.trim()) {
                        showError('message-error', 'Please enter your message');
                        isValid = false;
                    }
                    
                    if (!isValid) {
                        e.preventDefault();
                        
                        // Scroll to first error
                        const firstError = document.querySelector('.error-message[style*="display: block"]');
                        if (firstError) {
                            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                });
                
                // Add input event listeners to clear error messages when typing
                const inputs = form.querySelectorAll('input, textarea');
                inputs.forEach(input => {
                    input.addEventListener('input', function() {
                        const errorId = this.id + '-error';
                        const errorElement = document.getElementById(errorId);
                        if (errorElement) {
                            errorElement.style.display = 'none';
                        }
                    });
                });
            }
            
            function showError(id, message) {
                const errorElement = document.getElementById(id);
                if (errorElement) {
                    errorElement.textContent = message;
                    errorElement.style.display = 'block';
                }
            }
            
            function isValidEmail(email) {
                const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }
            
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    e.preventDefault();
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        });
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Smooth Scroll Behavior -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
        
        // Add animation on scroll
        const animateOnScroll = function() {
            const elements = document.querySelectorAll('.contact-info, .contact-form, .map-container');
            elements.forEach(element => {
                const elementPosition = element.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.3;
                
                if (elementPosition < screenPosition) {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }
            });
        }
        
        // Set initial styles for animation
        document.querySelectorAll('.contact-info, .contact-form, .map-container').forEach(element => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(30px)';
            element.style.transition = 'all 0.6s ease-out';
        });
        
        window.addEventListener('scroll', animateOnScroll);
        animateOnScroll(); // Run once on page load
        
        // Form submission handling
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            // You can add form validation here if needed
            console.log('Form submitted');
            // The form will be submitted normally after this
        });
    });
    </script>
</body>
</html>