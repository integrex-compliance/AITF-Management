<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './headSource.php'; ?>
    <style>
        /* Modern Color Scheme */
        :root {
            --primary-color: #1a365d;
            --secondary-color: #2c5282;
            --accent-color: #4299e1;
            --accent-light: #ebf8ff;
            --success-color: #38a169;
            --warning-color: #ecc94b;
            --danger-color: #e53e3e;
            --dark-color: #2d3748;
            --light-color: #f7fafc;
            --text-color: #2d3748;
            --text-muted: #718096;
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --border-radius: 12px;
        }
        
        /* Base Styles */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@600;700;800&display=swap');
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            color: var(--text-color);
            line-height: 1.7;
            overflow-x: hidden;
            background-color: #f8fafc;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--dark-color);
            line-height: 1.3;
        }
        
        /* Hero Section */
        .certification-hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 120px 0 100px;
            text-align: center;
            position: relative;
            overflow: hidden;
            margin-bottom: 3rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .certification-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgcGF0dGVyblRyYW5zZm9ybT0icm90YXRlKDQ1KSI+PHJlY3Qgd2lkdGg9IjUwJSIgaGVpZ2h0PSI1MCUiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wMykiLz48L3BhdHRlcm4+PC9kZWZzPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InVybCgjcGF0dGVybikiLz48L3N2Zz4=');
            opacity: 0.6;
        }
        
        .certification-hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            text-transform: capitalize;
            letter-spacing: -0.5px;
            position: relative;
            display: inline-block;
            padding-bottom: 1rem;
        }
        
        .certification-hero h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--accent-color);
            border-radius: 2px;
        }
        
        .certification-hero h1 span {
            color: #4fc3f7;
            position: relative;
            display: inline-block;
        }
        
        /* Main Content Section */
        .certification-section {
            padding: 5rem 0;
            background-color: #f8fafc;
            position: relative;
            z-index: 1;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        /* Process Cards */
        .process-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 2.5rem;
            margin-bottom: 2.5rem;
            transition: var(--transition);
            border-top: 4px solid var(--accent-color);
            position: relative;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            background-color: #fff;
        }
        
        .process-card::before {
            content: attr(data-step);
            position: absolute;
            right: 1.5rem;
            top: 1.5rem;
            font-size: 5.5rem;
            font-weight: 900;
            color: rgba(66, 153, 225, 0.05);
            line-height: 1;
            z-index: 0;
            font-family: 'Montserrat', sans-serif;
            pointer-events: none;
        }
        
        .process-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-color: var(--accent-color);
        }
        
        .process-card h2 {
            color: var(--primary-color);
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 1rem;
            z-index: 1;
            font-weight: 700;
            display: flex;
            align-items: center;
        }
        
        .process-card h2::before {
            content: '';
            display: inline-block;
            width: 30px;
            height: 30px;
            background-color: var(--accent-light);
            border-radius: 8px;
            margin-right: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-color);
            font-weight: 700;
            font-size: 1rem;
        }
        
        .process-card h2:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background: #2c7be5;
        }
        
        .process-card p {
            color: #4a5568;
            line-height: 1.8;
            margin-bottom: 20px;
            font-size: 1.05rem;
            position: relative;
            z-index: 1;
        }
        
        /* Process Features */
        .process-feature {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.25rem;
            position: relative;
            z-index: 1;
            background: #f8fafc;
            padding: 1rem 1.25rem;
            border-radius: 8px;
            transition: var(--transition);
            border-left: 3px solid var(--accent-color);
        }
        
        .process-feature:hover {
            transform: translateX(5px);
            background: #f1f5f9;
        }
        
        .process-feature .icon {
            color: var(--accent-color);
            font-size: 1.25rem;
            margin-right: 1rem;
            margin-top: 0.25rem;
            min-width: 24px;
            text-align: center;
            background: rgba(66, 153, 225, 0.1);
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .process-feature .content {
            flex: 1;
        }
        
        .process-image {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin: 30px 0;
            transition: transform 0.3s ease;
        }
        
        .process-image img {
            width: 100%;
            display: block;
            transition: transform 0.5s ease;
        }
        
        .process-image:hover img {
            transform: scale(1.05);
        }
        
        /* Certification flow diagram (image replacement) */
        .cert-diagram-container {
            background:
                radial-gradient(circle at 10% 0%, rgba(66, 165, 245, 0.55), transparent 60%),
                radial-gradient(circle at 90% 100%, rgba(236, 64, 122, 0.55), transparent 60%),
                linear-gradient(180deg, #050a30 0%, #12002f 50%, #050a30 100%);
            padding: 26px 16px;
            border-radius: 20px;
            position: relative;
            margin-top: 16px;
        }

        .cert-diagram-container::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 0% 100%, rgba(255, 255, 255, 0.08), transparent 55%),
                radial-gradient(circle at 100% 0%, rgba(255, 255, 255, 0.06), transparent 55%);
            mix-blend-mode: screen;
            opacity: 0.7;
            pointer-events: none;
        }

        .cert-diagram {
            max-width: 980px;
            margin: 0 auto;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            gap: 40px;
            position: relative;
            z-index: 1;
        }

        .cert-column {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            flex: 1 1 0;
            position: relative;
            padding-bottom: 18px;
        }

        .cert-node {
            color: #ffffff;
            text-align: center;
            padding: 10px 14px;
            width: 100%;
            max-width: 430px;
            box-shadow: 0 16px 30px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.35);
        }

        .cert-node-oval {
            border-radius: 999px;
        }

        .cert-node-rect {
            border-radius: 18px;
        }

        .cert-color-blue {
            background: radial-gradient(circle at top, #42a5f5, #0d47a1);
        }

        .cert-color-light-blue {
            background: radial-gradient(circle at top, #29b6f6, #01579b);
        }

        .cert-color-green {
            background: radial-gradient(circle at top, #9ccc65, #2e7d32);
        }

        .cert-color-orange {
            background: radial-gradient(circle at top, #ffb74d, #ef6c00);
        }

        .cert-color-deep-orange {
            background: radial-gradient(circle at top, #ff8a65, #e65100);
        }

        .cert-color-pink {
            background: radial-gradient(circle at top, #f06292, #ad1457);
        }

        .cert-color-deep-blue {
            background: radial-gradient(circle at top, #64b5f6, #1e88e5);
        }

        .cert-node-title-main {
            font-size: 0.9rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .cert-node-sub {
            margin-top: 4px;
            font-size: 0.78rem;
            line-height: 1.5;
            font-weight: 500;
        }

        .cert-arrow-vertical {
            width: 3px;
            height: 26px;
            margin: 4px 0 6px;
            position: relative;
            background-image: linear-gradient(to bottom, #ffeb3b 40%, rgba(255, 235, 59, 0) 40%);
            background-size: 3px 14px;
            background-repeat: repeat-y;
            animation: cert-diagram-vertical 1.7s linear infinite;
        }

        .cert-arrow-vertical::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            border-width: 8px 6px 0 6px;
            border-style: solid;
            border-color: #ffeb3b transparent transparent transparent;
        }

        .cert-stage-wrapper {
            margin-top: 10px;
        }

        .cert-stage-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 6px;
        }

        .cert-stage-box {
            padding: 6px 14px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.7);
            background: rgba(255, 255, 255, 0.12);
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .cert-stage-arrow {
            flex: 0 0 40px;
            height: 2px;
            position: relative;
            background-image: linear-gradient(to right, #ffeb3b 50%, rgba(255, 235, 59, 0.2) 50%);
            background-size: 12px 2px;
            background-repeat: repeat-x;
            animation: cert-diagram-horizontal 1.7s linear infinite;
        }

        .cert-stage-arrow::after {
            content: '';
            position: absolute;
            right: -8px;
            top: 50%;
            transform: translateY(-50%);
            border-width: 5px 0 5px 8px;
            border-style: solid;
            border-color: transparent transparent transparent #ffeb3b;
        }

        .cert-cross-arrow {
            position: absolute;
            bottom: 4px;
            right: -255px;
            width: 470px;
        }

        .cert-node-wide-text {
            max-width: 480px;
        }

        .cert-node-with-side {
            display: flex;
            align-items: stretch;
            gap: 18px;
        }

        /* make planning block an anchor for the bridge */
        .cert-node.cert-color-deep-orange {
            position: relative;
        }

        .cert-node-main {
            flex: 1;
        }

        .cert-arrow-horizontal {
            align-self: center;
            width: 60px;
            height: 2px;
            position: relative;
            background-image: linear-gradient(to right, #ffeb3b 50%, rgba(255, 235, 59, 0.2) 50%);
            background-size: 14px 3px;
            background-repeat: repeat-x;
            animation: cert-diagram-horizontal 1.7s linear infinite;
        }

        .cert-arrow-horizontal::after {
            content: '';
            position: absolute;
            right: -8px;
            top: 50%;
            transform: translateY(-50%);
            border-width: 5px 0 5px 8px;
            border-style: solid;
            border-color: transparent transparent transparent #ffeb3b;
        }

        .cert-side-panel {
            min-width: 190px;
            padding: 16px 14px;
            border-radius: 18px;
            background: rgba(3, 7, 30, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.6);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 8px;
        }

        .cert-side-icon {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            border: 2px solid rgba(255, 255, 255, 0.75);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 4px;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.25);
            font-size: 1.4rem;
        }

        .cert-side-icon::before {
            content: '\23F2';
        }

        .cert-side-text-line {
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .cert-bridge {
            position: absolute;
            top: 24px;
            left: 100%;
            width: 236px;
            height: 485px;
            transform: translate(12px, 0);
            pointer-events: none;
            border-left: 2px dashed #ffeb3b;
            border-top: 2px dashed #ffeb3b;
            border-radius: 14px 14px 0 0;
        }

        .cert-bridge::after {
            content: '';
            position: absolute;
            right: -10px;
            top: -6px;
            border-width: 6px 0 6px 10px;
            border-style: solid;
            border-color: transparent transparent transparent #ffeb3b;
        }

        /* hide old bridge segments */
        .cert-bridge-vertical,
        .cert-bridge-horizontal {
            display: none;
        }

        @media (max-width: 768px) {
            .cert-diagram-container {
                padding: 28px 14px;
            }
            .cert-node {
                min-width: 100%;
                padding: 14px 16px;
            }
            .cert-node-with-side {
                flex-direction: column;
                align-items: center;
            }
            .cert-arrow-horizontal {
                width: 56px;
            }
            .cert-side-panel {
                width: 100%;
                max-width: 260px;
            }
            .cert-diagram {
                max-width: 580px;
                flex-direction: column;
                gap: 10px;
            }
            .cert-column {
                width: 100%;
            }
            .cert-cross-arrow {
                position: relative;
                align-self: center;
                bottom: auto;
                right: auto;
                width: 120px;
                margin-left: 0;
                margin-right: 0;
                margin-top: 6px;
                margin-bottom: 6px;
            }
            .cert-bridge {
                display: none;
            }
        }

        /* Highlight Box */
        .highlight-box {
            background: rgba(66, 153, 225, 0.05);
            border-left: 4px solid var(--accent-color);
            padding: 1.5rem;
            margin: 2rem 0;
            border-radius: 8px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        
        .highlight-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.5) 100%);
            z-index: 0;
            pointer-events: none;
        }
        
        .highlight-box:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .highlight-box p {
            margin: 0;
            font-style: italic;
            color: #2c7be5;
            font-weight: 500;
        }
        
        /* Timeline */
        .timeline {
            position: relative;
            padding-left: 2rem;
            margin: 2.5rem 0;
            border-left: 2px solid #e2e8f0;
            padding-left: 3rem;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: -2px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, var(--accent-color), #90cdf4);
        }
        
        .timeline:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 2rem;
            padding-left: 2rem;
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            border-left: 3px solid var(--accent-color);
            transition: var(--transition);
        }
        
        .timeline-item:hover {
            transform: translateX(5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .timeline-item:before {
            content: '';
            position: absolute;
            left: -2.5rem;
            top: 1.5rem;
            width: 1rem;
            height: 1rem;
            border-radius: 50%;
            background: var(--accent-color);
            border: 3px solid white;
            box-shadow: 0 0 0 2px var(--accent-color);
        }
        
        .timeline-item h4 {
            color: var(--primary-color);
            margin-bottom: 0.75rem;
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
        }
        
        .timeline-item h4::before {
            content: '→';
            color: var(--accent-color);
            margin-right: 0.5rem;
            font-weight: bold;
        }
        
        .timeline-item p {
            margin: 0;
            color: #4a5568;
        }
        
        .timeline-item .timeline-date {
            display: inline-block;
            background: var(--accent-light);
            color: var(--accent-color);
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-weight: 700;
        }
        
        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            background: #2c7be5;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            margin-right: 15px;
            font-weight: bold;
        }
        
        /* Buttons */
        /* Buttons */
        .btn {
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            transition: var(--transition);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.875rem;
            position: relative;
            overflow: hidden;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            z-index: -1;
            transition: var(--transition);
            opacity: 0.9;
        }
        
        .btn-primary {
            color: white;
            background: linear-gradient(135deg, var(--accent-color), var(--secondary-color));
            border: none;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            z-index: -1;
            transition: var(--transition);
            opacity: 0;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(66, 153, 225, 0.3);
        }
        
        .btn-primary:hover::before {
            opacity: 1;
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .container {
                max-width: 100%;
                padding: 0 2rem;
            }
        }
        
        @media (max-width: 992px) {
            .certification-hero {
                padding: 6rem 0 4rem;
            }
            
            .certification-hero h1 {
                font-size: 2.75rem;
            }
            
            .process-card {
                padding: 2rem 1.75rem;
            }
            
            .timeline {
                padding-left: 1.5rem;
                margin-left: 0.5rem;
            }
            
            .timeline-item {
                padding: 1.25rem;
            }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 1.5rem;
            }
            
            .certification-hero {
                padding: 5rem 0 3.5rem;
            }
            
            .certification-hero h1 {
                font-size: 2.25rem;
                padding-bottom: 0.75rem;
            }
            
            .certification-hero h1::after {
                width: 80px;
                height: 3px;
            }
            
            .process-card {
                padding: 1.75rem 1.5rem;
                margin-bottom: 2rem;
            }
            
            .process-card h2 {
                font-size: 1.5rem;
                flex-direction: column;
                align-items: flex-start;
            }
            
            .process-card h2::before {
                margin-bottom: 0.75rem;
                margin-left: -0.5rem;
            }
            
            .timeline {
                padding-left: 1.25rem;
                margin-left: 0.5rem;
            }
            
            .timeline-item {
                padding: 1.25rem;
            }
            
            .highlight-box {
                padding: 1.25rem;
                margin: 1.5rem 0;
            }
            
            .btn {
                padding: 0.65rem 1.5rem;
                font-size: 0.8rem;
            }
        }
        
        @media (max-width: 576px) {
            .container {
                padding: 0 1.25rem;
            }
            
            .certification-hero {
                padding: 4rem 0 3rem;
            }
            
            .certification-hero h1 {
                font-size: 2rem;
                padding-bottom: 0.75rem;
            }
            
            .certification-hero h1::after {
                width: 60px;
                height: 3px;
            }
            
            .process-card {
                padding: 1.5rem 1.25rem;
                margin-bottom: 1.75rem;
            }
            
            .process-card h2 {
                font-size: 1.35rem;
                margin-bottom: 1.25rem;
            }
            
            .process-card p {
                font-size: 0.95rem;
                line-height: 1.7;
            }
            
            .process-feature {
                padding: 0.875rem 1rem;
            }
            
            .timeline {
                padding-left: 1rem;
                margin-left: 0.5rem;
            }
            
            .timeline-item {
                padding: 1.125rem;
            }
            
            .timeline-item::before {
                left: -2rem;
                top: 1.25rem;
                width: 0.875rem;
                height: 0.875rem;
            }
            
            .btn {
                padding: 0.6rem 1.25rem;
                font-size: 0.775rem;
                width: 100%;
                margin-bottom: 0.5rem;
            }
        }
        
        /* Animation */
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

        @keyframes cert-diagram-vertical {
            0% {
                background-position-y: 0;
            }
            100% {
                background-position-y: 16px;
            }
        }

        @keyframes cert-diagram-horizontal {
            0% {
                background-position-x: 0;
            }
            100% {
                background-position-x: 14px;
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        
        /* Utility Classes */
        .text-center {
            text-align: center;
        }
        
        .mt-3 {
            margin-top: 1.5rem;
        }
        
        .mt-5 {
            margin-top: 3rem;
        }
        
        .mb-4 {
            margin-bottom: 2rem;
        }
        
        .lead {
            font-size: 1.25rem;
            font-weight: 400;
            color: var(--text-muted);
            line-height: 1.7;
            margin-bottom: 2rem;
        }
    </style>
</head>

<body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>

    <!-- Hero Section -->
    <section class="certification-hero">
        <div class="container">
            <h1>CERTIFICATION <span>PROCESS</span></h1>
            <p class="lead" style="max-width: 800px; margin: 0 auto; font-size: 1.2rem; opacity: 0.9;">
                A Step-by-Step Guide to Achieving ISO Certification
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="certification-section">
        <div class="container">
            <div class="process-card" data-step="01">
                <h2>Assessment Process Overview</h2>
                <p>AITF-MCL has developed a comprehensive audit program for the full 3-year certification cycle. Our systematic approach ensures that your organization not only achieves certification but also realizes tangible business benefits through enhanced operational efficiency and improved risk management.</p>
                
                <div class="process-feature">
                    <div class="icon"><i class="fa fa-check-circle"></i></div>
                    <div class="content">
                        <p>Two-stage initial audit process for thorough evaluation</p>
                    </div>
                </div>
                
                <div class="process-feature">
                    <div class="icon"><i class="fa fa-check-circle"></i></div>
                    <div class="content">
                        <p>Annual surveillance audits to ensure continued compliance</p>
                    </div>
                </div>
                
                <div class="process-feature">
                    <div class="icon"><i class="fa fa-check-circle"></i></div>
                    <div class="content">
                        <p>Recertification audit in the third year to maintain certification</p>
                    </div>
                </div>
                
                <div class="cert-diagram-container">
                    <div class="cert-diagram">
                        <div class="cert-column">
                            <div class="cert-node cert-node-oval cert-color-blue">
                                <div class="cert-node-title-main">AUDIT APPLICATION BY (AUDITEE) ORGANIZATION</div>
                            </div>

                            <div class="cert-arrow-vertical"></div>

                            <div class="cert-node cert-node-rect cert-color-light-blue">
                                <div class="cert-node-title-main">APPLICATION REVIEW AND ACCEPTANCE BY AITF</div>
                            </div>

                            <div class="cert-arrow-vertical"></div>

                            <div class="cert-node cert-node-rect cert-color-green">
                                <div class="cert-node-title-main">ISSUANCE OF PROPOSAL / CERTIFICATION SERVICE AGREEMENT / CONTRACT</div>
                            </div>

                            <div class="cert-arrow-vertical"></div>

                            <div class="cert-node cert-node-oval cert-color-orange">
                                <div class="cert-node-title-main">ACCEPTANCE BY ORGANIZATION</div>
                            </div>

                            <div class="cert-arrow-vertical"></div>

                            <div class="cert-node cert-node-rect cert-color-deep-orange">
                                <div class="cert-node-title-main">AUDIT PLANNING, NOTIFICATION, AND ASSIGNMENT OF AUDITOR</div>
                            </div>

                            <div class="cert-bridge">
                                <div class="cert-bridge-vertical"></div>
                                <div class="cert-bridge-horizontal"></div>
                            </div>
                        </div>

                        <div class="cert-column">

                            <div class="cert-node cert-node-rect cert-color-pink cert-stage-wrapper">
                                <div class="cert-node-title-main">INITIAL CERTIFICATION AUDIT</div>
                                <div class="cert-stage-row">
                                    <div class="cert-stage-box">STAGE 1 AUDIT</div>
                                    <div class="cert-stage-arrow"></div>
                                    <div class="cert-stage-box">STAGE 2 AUDIT</div>
                                </div>
                            </div>

                            <div class="cert-arrow-vertical"></div>

                            <div class="cert-node cert-node-rect cert-color-green">
                                <div class="cert-node-title-main cert-node-wide-text">REVIEW OF AUDIT PROCESSES, REPORTS, RECOMMENDATIONS AND INITIAL GRANTING OF CERTIFICAATION</div>
                            </div>

                            <div class="cert-arrow-vertical"></div>

                            <div class="cert-node cert-node-rect cert-color-deep-blue">
                                <div class="cert-node-title-main">FIRST SURVEILLANCE</div>
                                <div class="cert-node-sub">Before 12 months from Initial Granting of Certification</div>
                            </div>

                            <div class="cert-arrow-vertical"></div>

                            <div class="cert-node cert-node-rect cert-color-deep-blue">
                                <div class="cert-node-title-main">SECOND SURVEILLANCE</div>
                                <div class="cert-node-sub">Before 24 months from Initial Granting of Certification</div>
                            </div>

                            <div class="cert-arrow-vertical"></div>

                            <div class="cert-node cert-node-rect cert-color-deep-blue cert-node-with-side">
                                <div class="cert-node-main">
                                    <div class="cert-node-title-main">RE-CERTIFICATION AUDIT</div>
                                    <div class="cert-node-sub">Before Completion of 3 years from Initial Granting of Certification</div>
                                </div>
                                <div class="cert-arrow-horizontal"></div>
                                <div class="cert-side-panel">
                                    <div class="cert-side-icon"></div>
                                    <div class="cert-side-text-line">3 Year Cycle</div>
                                    <div class="cert-side-text-line">Continues as Organization</div>
                                    <div class="cert-side-text-line">Remains Certified</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="process-card" data-step="02">
                <h2>1. New Inquiry & Application</h2>
                <p>Begin your certification journey by submitting an inquiry through any of our communication channels. Our team will guide you through the initial steps and provide the necessary application forms.</p>
                
                <div class="highlight-box">
                    <p><i class="fa fa-info-circle"></i> <strong>What you need to do:</strong> Complete the audit application form available on our website and submit it along with the required documents.</p>
                </div>
                
                <div class="process-feature">
                    <div class="icon"><i class="fa fa-arrow-right"></i></div>
                    <div class="content">
                        <p>Submit completed application form via email or in person</p>
                    </div>
                </div>
                
                <div class="process-feature">
                    <div class="icon"><i class="fa fa-arrow-right"></i></div>
                    <div class="content">
                        <p>Our business development team will review your application</p>
                    </div>
                </div>
            </div>
            
            <div class="process-card" data-step="03">
                <h2>2. Audit Application Review</h2>
                <p>Our team carefully reviews your application to understand your organization's specific needs and requirements for certification.</p>
                
                <div class="timeline">
                    <div class="timeline-item">
                        <span class="timeline-date">Step 1</span>
                        <h4>Information Gathering</h4>
                        <p>We collect essential details about your organization, including size, scope, and processes.</p>
                    </div>
                    
                    <div class="timeline-item">
                        <span class="timeline-date">Step 2</span>
                        <h4>Needs Assessment</h4>
                        <p>Our experts analyze your specific certification requirements and objectives.</p>
                    </div>
                    
                    <div class="timeline-item">
                        <span class="timeline-date">Step 3</span>
                        <h4>Documentation Review</h4>
                        <p>We evaluate your existing management system documentation.</p>
                    </div>
                </div>
            </div>
            
            <div class="process-card" data-step="04">
                <h2>3. Quotation & Agreement</h2>
                <p>Based on the information provided, we prepare a detailed quotation and certification service agreement.</p>
                
                <div class="process-feature">
                    <div class="icon"><i class="fa fa-file-text"></i></div>
                    <div class="content">
                        <p>Receive a customized quotation based on your organization's size and complexity</p>
                    </div>
                </div>
                
                <div class="process-feature">
                    <div class="icon"><i class="fa fa-handshake-o"></i></div>
                    <div class="content">
                        <p>Review and sign the certification service agreement</p>
                    </div>
                </div>
                
                <div class="process-feature">
                    <div class="icon"><i class="fa fa-credit-card"></i></div>
                    <div class="content">
                        <p>Submit the required application fee</p>
                    </div>
                </div>
                
                <div class="highlight-box">
                    <p><i class="fa fa-lightbulb-o"></i> <strong>Note:</strong> The quotation includes all costs associated with the certification process, including audit days, travel expenses, and certification fees.</p>
                </div>
            </div>
            
            <div class="process-card" data-step="05">
                <h2>4. Contract & Pre-Assessment</h2>
                <p>After reviewing and accepting the quotation, we proceed with the contract signing and pre-assessment activities to prepare for the certification audit.</p>
                
                <div class="process-feature">
                    <div class="icon"><i class="fa fa-file-contract"></i></div>
                    <div class="content">
                        <p>Sign the certification service agreement to formalize our engagement</p>
                    </div>
                </div>
                
                <div class="process-feature">
                    <div class="icon"><i class="fa fa-search"></i></div>
                    <div class="content">
                        <p>Pre-assessment review to analyze and plan the audit process</p>
                    </div>
                </div>
                
                <div class="process-feature">
                    <div class="icon"><i class="fa fa-calendar-check-o"></i></div>
                    <div class="content">
                        <p>Schedule the stage 1 and stage 2 audit dates</p>
                    </div>
                </div>
                
                <div class="highlight-box">
                    <p><i class="fa fa-info-circle"></i> <strong>Pre-Assessment Focus Areas:</strong> Understanding your organization's scope, processes, and readiness for the certification audit.</p>
                </div>
            </div>
            
            <div class="process-card" data-step="06">
                <h2>5. Stage 1 Audit (Documentation Review)</h2>
                <p>The first stage of the certification process focuses on reviewing your management system documentation and assessing your organization's readiness for the stage 2 audit.</p>
                
                <div class="timeline">
                    <div class="timeline-item">
                        <span class="timeline-date">Objective</span>
                        <h4>Documentation Review</h4>
                        <p>Evaluation of your management system documentation against the requirements of the standard</p>
                    </div>
                    
                    <div class="timeline-item">
                        <span class="timeline-date">Activities</span>
                        <h4>On-site Assessment</h4>
                        <p>Brief visit to your premises to understand your operations and confirm audit planning</p>
                    </div>
                    
                    <div class="timeline-item">
                        <span class="timeline-date">Outcome</span>
                        <h4>Readiness Evaluation</h4>
                        <p>Confirmation of your organization's readiness for the stage 2 certification audit</p>
                    </div>
                </div>
                
                <div class="process-feature">
                    <div class="icon"><i class="fa fa-check-square-o"></i></div>
                    <div class="content">
                        <p>Verification of management system documentation</p>
                    </div>
                </div>
                
                <div class="process-feature">
                    <div class="icon"><i class="fa fa-check-square-o"></i></div>
                    <div class="content">
                        <p>Evaluation of internal audits and management review</p>
                    </div>
                </div>
            </div>
            
            <div class="process-card" data-step="07">
                <h2>6. Stage 2 Audit (Certification Audit)</h2>
                <p>The stage 2 audit evaluates the implementation and effectiveness of your management system in meeting the requirements of the standard.</p>
                
                <div class="process-feature">
                    <div class="icon"><i class="fa fa-search-plus"></i></div>
                    <div class="content">
                        <p>Comprehensive on-site audit of your management system implementation</p>
                    </div>
                </div>
                
                <div class="process-feature">
                    <div class="icon"><i class="fa fa-check-circle-o"></i></div>
                    <div class="content">
                        <p>Verification of compliance with all applicable requirements</p>
                    </div>
                </div>
                
                <div class="process-feature">
                    <div class="icon"><i class="fa fa-file-text-o"></i></div>
                    <div class="content">
                        <p>Documentation of findings and opportunities for improvement</p>
                    </div>
                </div>
                
                <div class="highlight-box">
                    <p><i class="fa fa-info-circle"></i> <strong>Audit Duration:</strong> The length of the stage 2 audit depends on the size and complexity of your organization, typically ranging from 2-5 days.</p>
                </div>
            </div>
            
            <div class="process-card" data-step="08">
                <h2>7. Certification Decision & Issuance</h2>
                <p>After successful completion of the stage 2 audit and resolution of any non-conformities, we make the certification decision.</p>
                
                <div class="timeline">
                    <div class="timeline-item">
                        <span class="timeline-date">Step 1</span>
                        <h4>Review of Audit Findings</h4>
                        <p>Our certification committee reviews the audit report and findings</p>
                    </div>
                    
                    <div class="timeline-item">
                        <span class="timeline-date">Step 2</span>
                        <h4>Certification Decision</h4>
                        <p>Decision to grant, defer, or deny certification</p>
                    </div>
                    
                    <div class="timeline-item">
                        <span class="timeline-date">Step 3</span>
                        <h4>Certificate Issuance</h4>
                        <p>Issuance of the ISO certification with a unique certification number</p>
                    </div>
                </div>
                
                <div class="process-feature">
                    <div class="icon"><i class="fa fa-certificate"></i></div>
                    <div class="content">
                        <p>Receive your official ISO certification</p>
                    </div>
                </div>
                
                <div class="process-feature">
                    <div class="icon"><i class="fa fa-check-circle"></i></div>
                    <div class="content">
                        <p>Your organization is now certified and can use the certification marks</p>
                    </div>
                </div>
            </div>
            
            <div class="process-card" data-step="09">
                <h2>8. Surveillance Audits & Maintenance</h2>
                <p>To maintain your certification, we conduct regular surveillance audits to ensure continued compliance with the standard.</p>
                
                <div class="process-feature">
                    <div class="icon"><i class="fa fa-calendar"></i></div>
                    <div class="content">
                        <p>Annual surveillance audits to verify continued compliance</p>
                    </div>
                </div>
                
                <div class="process-feature">
                    <div class="icon"><i class="fa fa-refresh"></i></div>
                    <div class="content">
                        <p>Continuous improvement of your management system</p>
                    </div>
                </div>
                
                <div class="process-feature">
                    <div class="icon"><i class="fa fa-shield"></i></div>
                    <div class="content">
                        <p>Maintenance of certification through ongoing compliance</p>
                    </div>
                </div>
                
                <div class="highlight-box">
                    <p><i class="fa fa-info-circle"></i> <strong>Recertification:</strong> After three years, a complete recertification audit is required to maintain your certification.</p>
                </div>
            </div>
            
            <div class="process-card" data-step="10">
                <h2>9. Non-Conformities & Corrective Actions</h2>
                <p>If any non-conformities are identified during the audit process, we work with you to ensure they are effectively addressed.</p>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="highlight-box" style="background-color: #fff8f8; border-left-color: #f44336;">
                            <h4><i class="fa fa-exclamation-triangle" style="color: #f44336;"></i> Major Non-Conformities</h4>
                            <p style="color: #f44336;">Require immediate attention and must be addressed before certification can be granted.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="highlight-box" style="background-color: #fff9f0; border-left-color: #ff9800;">
                            <h4><i class="fa fa-exclamation-circle" style="color: #ff9800;"></i> Minor Non-Conformities</h4>
                            <p style="color: #ff9800;">Should be addressed within an agreed timeframe.</p>
                        </div>
                    </div>
                </div>
                
                <div class="process-feature">
                    <div class="icon"><i class="fa fa-check-square"></i></div>
                    <div class="content">
                        <p>Our team will guide you through the corrective action process</p>
                    </div>
                </div>
                
                <div class="process-feature">
                    <div class="icon"><i class="fa fa-support"></i></div>
                    <div class="content">
                        <p>Ongoing support is available to help you address any findings</p>
                    </div>
                </div>
            </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 layout_padding_2">
                                    <div class="process-card" data-step="11">
                <h2>10. Get Started with Your Certification Journey</h2>
                <p>Begin your path to ISO certification with AITF-MCL. Our team of experienced auditors is ready to guide you through every step of the process.</p>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="process-feature">
                            <div class="icon"><i class="fa fa-phone"></i></div>
                            <div class="content">
                                <h4>Contact Us</h4>
                                <p>Reach out to our certification team to discuss your needs</p>
                            </div>
                        </div>
                        
                        <div class="process-feature">
                            <div class="icon"><i class="fa fa-file-text"></i></div>
                            <div class="content">
                                <h4>Submit Application</h4>
                                <p>Complete and submit the certification application form</p>
                            </div>
                        </div>
                        
                        <div class="process-feature">
                            <div class="icon"><i class="fa fa-calendar"></i></div>
                            <div class="content">
                                <h4>Schedule Audit</h4>
                                <p>Plan your certification audit with our team</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="highlight-box" style="background: #f0f7ff;">
                            <h4><i class="fa fa-question-circle"></i> Have Questions?</h4>
                            <p>Our certification experts are here to help. Contact us today to learn more about the certification process and how we can support your organization's quality journey.</p>
                            <a href="contactUS.php" class="btn btn-primary mt-3">Contact Us Now</a>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-5">
                    <h3>Ready to Begin Your Certification Journey?</h3>
                    <p class="lead">Contact us today to schedule a consultation with one of our certification specialists.</p>
                    <a href="contactUS.php" class="btn btn-primary btn-lg mt-3">Request a Quote</a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Add smooth scrolling to all links
        $("a").on('click', function(event) {
            if (this.hash !== "") {
                event.preventDefault();
                var hash = this.hash;
                $('html, body').animate({
                    scrollTop: $(hash).offset().top
                }, 800, function(){
                    window.location.hash = hash;
                });
            }
        });
        
        // Animate process cards on scroll
        $(window).on('scroll', function() {
            $('.process-card').each(function() {
                var cardTop = $(this).offset().top;
                var windowHeight = $(window).height();
                var scrollTop = $(window).scrollTop();
                
                if (cardTop < (windowHeight + scrollTop - 100)) {
                    $(this).addClass('animate__animated animate__fadeInUp');
                }
            });
        });
    </script>
</body>
</html>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 layout_padding_2">
                        <div class="full">
                            <h3><b>AUDIT APPLICATION</b></h3>
                        </div>
                        <div class="full">
                            <p>Required details of organization are gathered by filling of audit application form from the client as specified above with the aim of knowing client's requirements for certification, activities carried out at client's organisation and number of employees etc.</p>
                            <p>On the basis of concern information AITF-MCL send quotation for certification which is mainly based on the required man-days for auditing the organisation. Also this information is needed for analysing competency requirements to appoint the competent auditor.</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 layout_padding_2">
                        <div class="full">
                            <h3><b>QUOTATION ISSUANCE</b></h3>
                        </div>
                        <div class="full">
                            <p>On the basis of information received in the form of completed questionnaire, the manager reviews the scopes available with AITF-MCL and prepares the quotation and registration service agreement, which will be sent to the client organization for acceptance. The Quotations are prepared on the basis of a uniform policy after deciding the efforts required to audit the Organization.</p>
                            <p>The quotation indicates the frequency for surveillance visits as well as terms and conditions for grant and maintenance of certification. The quotation is also accompanied by a copy of our certification criteria. This quotation activity can be merged along with agreement in the most of cases for the feasibility of process completion of quotation and agreement including the acceptance of the terms & conditions by Client and AITF-MCL unanimously.</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 layout_padding_2">
                        <div class="full">
                            <h3><b>CERTIFICATION SERVICE AGREEMENT</b></h3>
                        </div>
                        <div class="full">
                            <p>The client organization is required to submit a copy of the signed agreement along with the application fee. The receipt of agreement enables to register the organization as our client. A unique identification No. is allotted to every organization on receipt of agreement and application fees .Once the Application for registration received & reviewed, which confirms the exact way client company name and site address will appear along with the description of scope (products or services) for which registration is sought. The certification audit contract will be made and sent to the client.</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 layout_padding_2">
                        <div class="full">
                            <h3><b>CONTRACT</b></h3>
                        </div>
                        <div class="full">
                            <p>After receiving signed service agreement AITF-MCL's contract review team review it and if any changed required then revision of contract carried out along with the intimation to client and if accepted then pre-assessment review will be conduct.</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 layout_padding_2">
                        <div class="full">
                            <h3><b>PREE ASSESSMENT REVIEW</b></h3>
                        </div>
                        <div class="full">
                            <p>AITF-MCL carryout pre assessment review for analysing & planning of AITF-MCL with respect of following factors:</p>
                            <p><span class="fa fa-arrow-circle-right"> &nbsp;</span>Auditee organization details like Scope, Name, Address, to clear understanding between client & AITF-MCL Requirements, Audit language requirement etc</p>
                            <p><span class="fa fa-arrow-circle-right"> &nbsp;</span>Details of client Management system if previously certified.</p>
                            <p><span class="fa fa-arrow-circle-right"> &nbsp;</span>Analyses AITF-MCL capability to provide certification service.</p>
                            <p><span class="fa fa-arrow-circle-right"> &nbsp;</span>Implementation status of the Applicant.</p>
                            <p><span class="fa fa-arrow-circle-right"> &nbsp;</span>Tentative Assessment Plan.</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 layout_padding_2">
                        <div class="full">
                            <h3><b>AUDIT NOTIFICATION WITH ASSIGNMENT OF AUDITOR</b></h3>
                        </div>
                        <div class="full">
                            <p>AITF -MCL Send notification with assignment of auditor to Client for acceptance of planed date/s and Auditor/s for the confirmation of no conflict of interest among the auditors and auditee organisation.</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 layout_padding_2">
                        <div class="full">
                            <h3><b>STAGE I</b></h3>
                        </div>
                        <div class="full">
                            <p>Upon confirmation of the audit schedule by the client acceptance of the schedule subject to consideration as above the audit programme is planned for stage I audit by Lead Auditor.</p>
                            <p>Stage I audit: Stage I audit is carried out by a competent auditor for specific number of man day(s) to verify:</p>
                            <p>A. To audit the clients management system documentation.</p>
                            <p>B. To evaluate the client's location and site specific conditions & to undertake discussion with the client's personnel to determine the preparedness for the stage-2 audit</p>
                            <p>C. To review the clients understanding regarding requirements of the standard, in particular with respect to the identification of key performance or significant aspects, process, objectives & operation of the management system</p>
                            <p>D. To collect necessary information regarding the scope of management system, process & location(s) of the client's and related statutory and regulatory aspects and compliance (quality, environmental, legal aspects of the clients operation, associated risks, etc.</p>
                            <p>E. To review allocation of resources for stage-2 audit and agree with the clients on the stage-2audit</p>
                            <p>F. To provide a focus for planning the stage-2 audit by gaining a sufficient understanding of the clients management system and site operation in context of possible significant aspects</p>
                            <p>G. To evaluate if internal audit & management review are being planned & performed,& that the level of implementation of the management system substantiates that the client is ready for the stage-2 audit</p>
                            <p>H. If multisite same management system centrally governs the activities of all sites.</p>
                            <p>I. If multisite organisation's eligibility for sampling method.</p>
                            <p>J. If multisite audit of top Management, Internal audit and Management Review are covered for central activities and all the sites under scope of certification are planned and performed</p>
                            <p>It is necessary that the stage 1 audit be carried out in case of initial certification at the client's premises in order to achieve the objectives stated above. Stage-1 Audit Report - After the stage one audit the auditor shall submit his finding & advise by written report</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 layout_padding_2">
                        <div class="full">
                            <h3><b>STAGE II</b></h3>
                        </div>
                        <div class="full">
                            <p>Stage II audit shall be planned after written or verbal confirmation from client on the closure of stage I audit areas of concern (observation). In case of verbal communication, Certification Manager will acknowledge the same in review report and Lead Auditor shall evaluate the action taken for the same in the stage II assessment audit. Time interval between stage I and Stage II depends upon the nature of observation and time frame required by the client to resolve all the identified Areas or concern during stage I audit</p>
                            <p>Stage II Audit is conducted onsite to evaluate the implementation; including effectiveness, of the client's management system shall cover for the following as minimum requirements:</p>
                            <p>A. Information and evidence of conformity for all the requirements applicable to Quality management system standard and normative document.</p>
                            <p>B. Performance monitoring, measuring, reporting and reviewing against key performance objectives and targets.</p>
                            <p>C. Client's management system and performance as regards to applicable legal compliance.</p>
                            <p>D. Operational control of the clients processes.</p>
                            <p>E. Internal Auditing covering all sites and Management Review.</p>
                            <p>F. Management responsibility for client's policies.</p>
                            <p>G. Links between the normative documents, policy, performance objectives and targets, any applicable legal requirements, responsibilities, competence of personnel, operations, procedures, performance data and internal audit findings and conclusion.</p>
                            <p>H. In case of multisite location Stage II audit cover central office as well as all the sites under certification.</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 layout_padding_2">
                        <div class="full">
                            <h3><b>TYPE OF NON CONFORMANCE</b></h3>
                        </div>
                        <div class="full">
                            <p>Product, Process and system not meeting the planned arrangements are considered as non-conformance. Non-conformance are classified as major or minor based on the following criteria.</p>
                            <p><span class="fa fa-arrow-circle-right"> &nbsp;</span>Major, Minor Non conformance</p>
                            <p><span class="fa fa-arrow-circle-right"> &nbsp;</span>Opportunities for Improvement</p>
                            <p><span class="fa fa-arrow-circle-right"> &nbsp;</span>If Major Finding occurs, Management will be notified to determine whether Audit can continue.</p>
                            <p>A. &nbsp;Major Non-conformance</p>
                            <p>The absence or total breakdown of a system to meet committed quality system requirements that could result in:</p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-arrow-circle-right"> &nbsp;</span>Failure to fulfill one or more requirements of the management system standard</p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-arrow-circle-right"> &nbsp;</span>Non meeting statutory / regulatory requirements</p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-arrow-circle-right"> &nbsp;</span>Not taking corrective actions for the previous NCRs</p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-arrow-circle-right"> &nbsp;</span>Repetitive minor non-conformances of similar nature across the organization/ across the sites sampled.</p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-arrow-circle-right"> &nbsp;</span>Number of defective samples is more than 50% of total samples size.</p>
                            <p>B. &nbsp;Minor Non-conformance</p>
                            <p>A non-compliance, which is not likely to result in the failure of the quality system or reduce its ability to assure controlled processes or products.</p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-arrow-circle-right"> &nbsp;</span>Evidence of implementation observed except isolated cases.</p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-arrow-circle-right"> &nbsp;</span>Defective samples less than 50% of total sample size considered for audit.</p>
                            <p>Response to Findings/Nonconformance</p>
                            <p><span class="fa fa-arrow-circle-right"> &nbsp;</span>A non-compliance, which is not likely to result in the failure of the quality system or reduce its ability to assure controlled processes or products.</p>
                            <p><span class="fa fa-arrow-circle-right"> &nbsp;</span>Minor Nonconformance Rules -</p>
                            <p> &nbsp;&nbsp;&nbsp;&nbsp;Initial audit - Root-cause analysis and action plan 2 weeks, closure before granting registration within 90 days from the date of audit.</p>
                            <p> &nbsp;&nbsp;&nbsp;&nbsp;Surveillance - 3 months to respond (Root-cause analysis and action plan), close next audit.</p>
                            <p> &nbsp;&nbsp;&nbsp;&nbsp;Renewal - Must be closed before expiry date on certificate</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 layout_padding_2">
                        <div class="full">
                            <h3><b>FOLLOW-UP AUDIT (IF REQUIRED):</b></h3>
                        </div>
                        <div class="full">
                            <p>(Follow up audit is recommended when it is considered on site verification is required to verify the corrective actions for the non-conformances recorded during any base audit)Purpose to verify the following:</p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-arrow-circle-right"> &nbsp;</span>Effectiveness of the Corrective action taken for the non-conformances identified during the base assessment.</p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-arrow-circle-right"> &nbsp;</span>Revision to the system documents if any.</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 layout_padding_2">
                        <div class="full">
                            <h3><b>MAINTENANCE OF CERTIFICATION</b></h3>
                        </div>
                        <div class="full">
                            <p>The certification is maintained for a period of 3 years under the following conditions:</p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-arrow-circle-right"> &nbsp;</span>Surveillance audits are conducted as scheduled to ensure continuance of certification and to maintain validity of certificate.</p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-arrow-circle-right"> &nbsp;</span>The quality system is maintained by the certified client and all the certified non-conformances raised during surveillance are closed <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; to the satisfaction of AITF-MCL assessors within the time frame agreed.</p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-arrow-circle-right"> &nbsp;</span>The internal audit and the management reviews are conducted as scheduled and there are no issues pending.</p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-arrow-circle-right"> &nbsp;</span>The client shall maintain suitable records of customer complaints and keep the records of investigation and remedial actions taken <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; with respect to such complaints for verification by the AITF-MCL assessors.</p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-arrow-circle-right"> &nbsp;</span>All outstanding dues to AITF-MCL are paid.</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 layout_padding_2">
                        <div class="full">
                            <h3><b>CRITERIA FOR SUSPENSION, WITHDRAWAL, CANCELLATION</b></h3>
                        </div>
                        <div class="full">
                            <p>The following is the policy and procedure for suspending, withdrawing or reducing the scope of certification and the subsequent actions by AITF-MCL:</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 layout_padding_2">
                        <div class="full">
                            <h3><b>SUSPENSION</b></h3>
                        </div>
                        <div class="full">
                            <p>The certificate may be suspended for the following reasons:</p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-arrow-circle-right"> &nbsp;</span>Requested corrective actions are not performed within agreed time limits;</p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-arrow-circle-right"> &nbsp;</span>Improper use of the certificate of conformity or accreditation mark, e.g. misleading publication or advertisement;</p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-arrow-circle-right"> &nbsp;</span>The holder of the certificate neglects to inform AITF about matters of vital importance for the certified management system.</p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-arrow-circle-right"> &nbsp;</span>Failure to settle the outstanding dues due toAITF. An official suspension of the certificate will be confirmed in writing to the certificate<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; holder. AITF shall indicate under what conditions the suspension will be removed, e.g. corrective actions to be taken.The accreditation<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; authority will be informed.At the end of the suspension period, AITF will determine if the indicated conditions for re -issuing certificate<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; have been fulfilled. On fulfillment, the suspension shall be removed by notifying the certificate holder that the certificate has been <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;reinstated.</p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-arrow-circle-right"> &nbsp;</span>Under suspension, the client's management system certification is temporarily invalid. AITF-MCL has enforceable arrangements <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(F 7.03 Certification Service Agreement Cl.6 Last Para) with its clients to ensure that in case of suspension/Withdrawal the client refrains <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;from further promotion of its certification. AITF-MCL shall make the suspended status of the certification publicly available and shall take <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;any other measures it deems appropriate.</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 layout_padding_2">
                        <div class="full">
                            <h3><b>WITHDRAWAL</b></h3>
                        </div>
                        <div class="full">
                            <p>The certificate may be withdrawn in the following cases:</p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-arrow-circle-right"> &nbsp;</span>If periodical audits reveal nonconformities of serious nature;</p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-arrow-circle-right"> &nbsp;</span>If the certificate holder fails to comply with the due settlement of his financial obligation despite several reminders with no response <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;from the client;</p>
                            <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="fa fa-arrow-circle-right"> &nbsp;</span>If inadequate measures are taken by the certificate holder in case of suspension and Failure to resolve the issues that have resulted in <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;the suspension in a time established by AITF-MCL shall result in withdrawal or reduction of the scope of certification. The suspension <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;would not exceed 6 months & will be resulted in to withdrawal or reducing the scope of certification.</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 layout_padding_2">
                        <div class="full">
                            <h3><b>CANCELLATION</b></h3>
                        </div>
                        <div class="full">
                            <p>The certificate will be cancelled if the certificate holder does not wish to prolong the agreement. AITF will withdraw/ cancel the certificate by informing the certificate holder in writing.</p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 layout_padding_2">
                        <div class="full">
                            <h3><b>REDUCING SCOPE OF CERTIFICATION</b></h3>
                        </div>
                        <div class="full">
                            <p>If client requested to do so or if during assessment it found that client don't having the imparted same (scope) activity in existence in such an event scope will be reduced at any type of assessment.</p>
                            <p>AITF-MCL shall reduce the client's scope of certification to exclude the parts not meeting the requirements, when the client has persistently or seriously failed to meet the certification requirements of those parts of the scope of certification. Any such reduction shall be in line with the requirements of the standard used for certification. AITF-MCL' s decision on reducing the scope of certification shall be communicated to the client, in writing, through letter F 7.38.</p>
                            <p>Above established Criteria for Suspension, withdrawal, cancellation and reducing scope of certification shall be followed. Upon request by any party, AITF-MCL shall correctly state the status of certification of a client's management system as being suspended, withdrawn or reduced.</p>
                            <p>The Certificate will be withdrawn in its entirety if client or in multisite case the central office or any of the sites does not fulfil the necessary provision for the maintenance of the certification.</p>
                        </div>
                    </div>
                </div>
                
            </div>


        </div>
        <!-- end section -->

       
        <?php include './footerSource.php'; ?>
    </body>
</html>