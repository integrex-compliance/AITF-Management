<meta name="viewport" content="width=device-width, initial-scale=1">
<!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>-->
<!-- Required JS: jQuery → Popper → Bootstrap -->
<script src="js/outer/jquery.min.js"></script>
<script src="js/outer/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" type="text/javascript"></script>
<style type="text/css">
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&family=Montserrat:wght@700;800&family=Playfair+Display:wght@700;800;900&display=swap');

    :root {
        --announcement-height: 48px;
        --nav-height: 85px;
        --nav-bg: #ffffff;
        --nav-text: #1e293b;
        --nav-hover-text: #2563eb;
        --nav-hover-bg: rgba(37, 99, 235, 0.06);
        --nav-accent: #3b82f6;
        --nav-accent-light: #60a5fa;
        --nav-accent-glow: rgba(59, 130, 246, 0.15);
        --nav-glass: rgba(255, 255, 255, 0.05);
    }

    .aitf-public-announcement-banner {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1040;
        height: var(--announcement-height);
        overflow: hidden;
        display: flex;
        align-items: center;
        background: linear-gradient(90deg, #053c6d 0%, #0b5b94 48%, #f5821f 100%);
        color: #ffffff;
        border-bottom: 1px solid rgba(255, 255, 255, 0.18);
        box-shadow: 0 8px 24px rgba(5, 60, 109, 0.18);
        font-family: 'Inter', 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .aitf-public-announcement-banner::before,
    .aitf-public-announcement-banner::after {
        content: "";
        position: absolute;
        top: 0;
        bottom: 0;
        width: 30px;
        z-index: 2;
        pointer-events: none;
    }

    .aitf-public-announcement-banner::before {
        left: 0;
        background: linear-gradient(90deg, #053c6d 0%, rgba(5, 60, 109, 0) 100%);
    }

    .aitf-public-announcement-banner::after {
        right: 0;
        background: linear-gradient(270deg, #f5821f 0%, rgba(245, 130, 31, 0) 100%);
    }

    .aitf-public-announcement-track {
        display: flex;
        width: max-content;
        animation: aitfPublicAnnouncementScroll 28s linear infinite;
        will-change: transform;
    }

    .aitf-public-announcement-group {
        display: flex;
        align-items: center;
        flex-shrink: 0;
        padding: 0 26px 0 70px;
    }

    .aitf-public-announcement-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0 24px;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.35px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .aitf-public-announcement-item i {
        color: #ffd59b;
        font-size: 13px;
    }

    .aitf-public-announcement-divider {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.8);
        box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.14);
    }

    .aitf-public-announcement-banner:hover .aitf-public-announcement-track {
        animation-play-state: paused;
    }

    @keyframes aitfPublicAnnouncementScroll {
        from {
            transform: translateX(0);
        }

        to {
            transform: translateX(-50%);
        }
    }

    /* Fixed Navbar */
    .top-header {
        position: fixed;
        top: var(--announcement-height);
        left: 0;
        right: 0;
        width: 100%;
        z-index: 1030;
        background: var(--nav-bg);
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        box-sizing: border-box;
        font-family: 'Inter', 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        height: var(--nav-height);
        transition: background 0.4s ease, box-shadow 0.4s ease, border-color 0.4s ease;
    }

    .top-header + * {
        margin-top: calc(var(--announcement-height) + var(--nav-height)) !important;
    }

    #home .top-header:not(.scrolled) {
        --nav-bg: transparent;
        --nav-text: #f1f5f9;
        --nav-hover-text: #ffffff;
        --nav-hover-bg: rgba(255, 255, 255, 0.12);

        border-bottom: 1px solid rgba(255, 255, 255, 0.15) !important;
        box-shadow: none;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }

    .top-header.scrolled {
        border-bottom: 1px solid rgba(0, 0, 0, 0.08) !important;
    }

    .top-header.scrolled .navbar a:not(.login-btn),
    .top-header.scrolled .navbar .dropdown>a,
    .top-header.scrolled .nav-primary .nav-link,
    .top-header.scrolled .header-nav .nav-link,
    .top-header.scrolled .header-nav .nav-link:not(.login-btn) {
        color: #1e293b !important;
    }

    .top-header.scrolled .header-nav .nav-link:not(.login-btn):hover,
    .top-header.scrolled .header-nav .dropdown:hover>.nav-link:not(.login-btn),
    .top-header.scrolled .nav-primary .nav-link:hover {
        color: #2563eb !important;
        background: rgba(37, 99, 235, 0.08) !important;
    }

    .top-header.scrolled .mobile-toggle-bar {
        background-color: #1e293b !important;
    }

    /* Toggler styling */
    .navbar-toggler {
        border: none !important;
        padding: 10px !important;
        outline: none !important;
        background: transparent !important;
    }

    .mobile-toggle-bar {
        display: block;
        width: 25px;
        height: 3px;
        background-color: var(--nav-text);
        margin: 5px 0;
        transition: all 0.3s ease;
        border-radius: 2px;
    }

    /* Navbar full width styling */
    .navbar {
        background: transparent !important;
        border: none !important;
        border-radius: 0 !important;
        padding: 0 1rem !important;
        margin: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        box-shadow: none !important;
        height: var(--nav-height) !important;
        min-height: var(--nav-height) !important;
        max-height: var(--nav-height) !important;
        overflow: visible !important;
        display: flex !important;
        align-items: center !important;
    }

    .navbar .container-fluid {
        padding: 0 10px 0 5px !important;
        margin: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: nowrap;
        overflow: visible !important;
        height: 100% !important;
    }

    .nav-primary {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-grow: 1;
        white-space: nowrap;
        padding: 0;
        margin: 0 10px 0 0;
        width: auto;
        height: 100% !important;
        overflow: visible !important;
        scrollbar-width: none;
        gap: 2px;
    }

    .nav-primary::-webkit-scrollbar {
        display: none;
    }

    .nav-primary>div {
        margin: 0 1px;
        flex-shrink: 0;
        min-width: auto;
    }

    .nav-primary>.dropdown {
        position: static;
    }

    .nav-primary .nav-link {
        padding: 0.4rem 0.55rem !important;
        font-size: 0.8rem !important;
        white-space: nowrap;
        border-radius: 6px;
        font-weight: 500;
        letter-spacing: 0.02em;
        line-height: 1.4 !important;
        text-transform: capitalize !important;
    }

    /* AITF Logo Styling */
    .navbar-brand {
        padding: 0;
        height: var(--nav-height);
        max-height: var(--nav-height);
        display: flex;
        align-items: center;
        margin-right: 20px;
        margin-left: 0;
        transition: transform 0.3s ease;
    }

    .navbar-brand:hover {
        transform: scale(1.02);
    }

    .navbar-brand img {
        max-height: 100px;
        height: 100px;
        width: auto;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
    }

    .header-nav .nav-link {
        position: relative;
        font-weight: 500;
        letter-spacing: 0.02em;
        text-transform: capitalize;
        color: var(--nav-text);
        padding: 0.5rem 0.9rem;
        opacity: 0.95;
        text-shadow: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-family: 'Inter', sans-serif;
        font-size: 0.85rem;
    }

    /* Desktop nav items (excluding Login) - modern style */
    .header-nav .nav-link:not(.login-btn) {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background-color: transparent;
        border: none !important;
        text-decoration: none;
        overflow: hidden;
        box-sizing: border-box;
        position: relative;
    }

    /* Underline animation effect */
    .header-nav .nav-link:not(.login-btn)::after {
        content: '';
        position: absolute;
        bottom: 6px;
        left: 50%;
        width: 0;
        height: 2px;
        background: linear-gradient(90deg, var(--nav-accent), var(--nav-accent-light));
        border-radius: 2px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        transform: translateX(-50%);
    }

    /* Ensure all navbar links match the text variable */
    .navbar a:not(.login-btn),
    .navbar .dropdown>a,
    .nav-primary .nav-link {
        color: var(--nav-text) !important;
        text-shadow: none;
    }

    .header-nav .nav-link:not(.login-btn)::after {
        content: '';
    }

    .header-nav .nav-link:hover,
    .header-nav .nav-link:focus-visible {
        color: var(--nav-accent-light);
    }

    .header-nav .nav-link:not(.login-btn):hover,
    .header-nav .nav-link:not(.login-btn):focus-visible,
    .header-nav .dropdown:hover>.nav-link:not(.login-btn) {
        background: var(--nav-hover-bg) !important;
        color: var(--nav-hover-text) !important;
        border: none !important;
        box-shadow: 0 0 20px var(--nav-accent-glow) !important;
        transform: translateY(-2px);
        text-shadow: none;
        border-radius: 8px !important;
        overflow: hidden;
    }

    .header-nav .nav-link:not(.login-btn):hover::after,
    .header-nav .dropdown:hover>.nav-link:not(.login-btn)::after {
        width: 60%;
    }

    /* Force all navigation links to match the variable (except hover which uses hover text variable) */
    .header-nav .nav-link,
    .header-nav .nav-link:not(.login-btn),
    .header-nav .nav-link:not(.login-btn):focus,
    .header-nav .nav-link:not(.login-btn):active,
    .header-nav .dropdown>a,
    .header-nav .dropdown>a:focus,
    .header-nav .dropdown>a:active,
    .nav-primary .nav-link,
    .nav-primary .nav-link:focus,
    .nav-primary .nav-link:active {
        color: var(--nav-text) !important;
    }

    .header-nav .nav-link:not(.login-btn):hover,
    .header-nav .dropdown>a:hover,
    .nav-primary .nav-link:hover {
        color: var(--nav-hover-text) !important;
    }

    .header-nav .nav-link.login-btn {
        padding: 8px 16px 8px 32px !important;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%) !important;
        color: #fff !important;
        border: none !important;
        border-radius: 25px !important;
        font-weight: 600 !important;
        font-size: 0.8rem !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        margin: 0 5px !important;
        white-space: nowrap;
        text-transform: capitalize !important;
        letter-spacing: 0.3px !important;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.35) !important;
        position: relative;
        overflow: hidden;
        z-index: 1;
        line-height: 1.3;
        font-family: 'Inter', sans-serif;
        flex-shrink: 0;
    }

    .header-nav .nav-link.login-btn::before {
        content: '→';
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        background: rgba(255, 255, 255, 0.25);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: bold;
        backdrop-filter: blur(4px);
        transition: all 0.3s ease;
    }

    .header-nav .nav-link.login-btn:hover {
        background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 50%, #2563eb 100%) !important;
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.2) inset !important;
        color: #fff !important;
        text-decoration: none;
    }

    .header-nav .nav-link.login-btn:hover::before {
        background: rgba(255, 255, 255, 0.35);
        transform: translateY(-50%) translateX(3px);
    }

    .header-nav .nav-link.login-btn:active {
        transform: translateY(-1px) scale(1) !important;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4) !important;
    }

    .header-nav b {
        font-weight: 500;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }

    @media (max-width: 1200px) {
        .nav-primary {
            gap: 1.5rem;
        }
    }

    @media (max-width: 992px) {
        .nav-primary {
            gap: 1rem;
        }
    }

    @media all and (min-width: 992px) {

        /*.navbar{ padding-top: 0; padding-bottom: 0; }*/
        .navbar .has-megamenu {
            position: static !important;
        }

        .navbar .megamenu {
            left: 0;
            right: 0;
            width: 100%;
            padding: 00px;
        }

        /*.navbar .nav-link{ padding-top:1rem; padding-bottom:1rem;  }*/
    }

    /* Dropdown section styling */
    .dropdown-section {
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .dropdown-section:last-child {
        border-bottom: none;
    }

    .dropdown-section a {
        display: block;
        padding: 8px 16px;
        text-decoration: none;
        color: #333;
        transition: background-color 0.2s;
    }

    .dropdown-section a:hover {
        background-color: #f5f5f5;
    }

    /* Training Dropdown - Clean White Style */
    .dropdown-content.dropdown-modern-grid {
        display: none;
        position: fixed !important;
        top: var(--nav-height) !important;
        left: 50% !important;
        transform: translateX(-50%) !important;
        background: #ffffff !important;
        width: 600px !important;
        max-width: 90vw !important;
        padding: 30px 40px !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 50px rgba(0, 0, 0, 0.15) !important;
        z-index: 9999 !important;
        opacity: 0;
        visibility: hidden;
    }

    .dropdown:hover .dropdown-content.dropdown-modern-grid,
    .dropdown.open .dropdown-content.dropdown-modern-grid {
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .dropdown-grid-row {
        display: flex !important;
        gap: 0 !important;
        margin-bottom: 0 !important;
    }

    .dropdown-grid-col.training-section {
        flex: 1 !important;
        padding: 22px 28px !important;
        cursor: pointer !important;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important;
        border-radius: 12px !important;
        position: relative !important;
        overflow: hidden !important;
    }

    .dropdown-grid-col.training-section::before {
        content: '' !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(59, 130, 246, 0.02) 100%) !important;
        opacity: 0 !important;
        transition: opacity 0.35s ease !important;
        z-index: 0 !important;
    }

    .dropdown-grid-col.training-section:hover::before {
        opacity: 1 !important;
    }

    .dropdown-grid-col.training-section:hover {
        background: transparent !important;
        transform: translateY(-3px) !important;
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15) !important;
    }

    .dropdown-grid-col.training-section:hover .dropdown-section-icon {
        transform: scale(1.1) rotate(5deg) !important;
        background: linear-gradient(135deg, #3b82f6, #60a5fa) !important;
        color: #ffffff !important;
        border-color: transparent !important;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4) !important;
    }

    .dropdown-grid-col.training-section:hover .dropdown-section-title {
        color: #3b82f6 !important;
    }

    .dropdown-grid-col.training-section.active {
        background: #eff6ff !important;
    }

    .dropdown-grid-col.training-section.disabled-training-section {
        cursor: not-allowed !important;
        opacity: 0.85 !important;
    }

    .dropdown-grid-col.training-section.disabled-training-section:hover {
        transform: none !important;
        box-shadow: none !important;
        background: transparent !important;
    }

    .dropdown-grid-col.training-section.disabled-training-section:hover::before {
        opacity: 0 !important;
    }

    .dropdown-grid-col.training-section.disabled-training-section:hover .dropdown-section-icon {
        transform: none !important;
        background: linear-gradient(135deg, #eff6ff, #dbeafe) !important;
        color: #3b82f6 !important;
        border-color: #dbeafe !important;
        box-shadow: none !important;
    }

    .dropdown-grid-col.training-section.disabled-training-section:hover .dropdown-section-title {
        color: #1e293b !important;
    }

    /* Vertical Divider */
    .dropdown-divider-vertical {
        width: 1px !important;
        min-height: 80px !important;
        align-self: stretch !important;
        background: linear-gradient(180deg, transparent 0%, #cbd5e1 20%, #cbd5e1 80%, transparent 100%) !important;
        margin: 15px 0 !important;
        position: relative !important;
        overflow: hidden !important;
        transition: all 0.4s ease !important;
        opacity: 0.3 !important;
    }

    /* Animated vertical divider effect */
    .dropdown-divider-vertical::before {
        content: '' !important;
        position: absolute !important;
        top: -100% !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background: linear-gradient(180deg, transparent 0%, rgba(59, 130, 246, 0.6) 50%, transparent 100%) !important;
        opacity: 0 !important;
        transition: all 0.3s ease !important;
    }

    .dropdown-content:hover .dropdown-divider-vertical {
        background: linear-gradient(180deg, transparent 0%, #3b82f6 20%, #3b82f6 80%, transparent 100%) !important;
        box-shadow: 0 0 8px rgba(59, 130, 246, 0.4) !important;
        opacity: 1 !important;
    }

    .dropdown-content:hover .dropdown-divider-vertical::before {
        opacity: 1 !important;
        animation: verticalDividerGlow 3s ease-in-out infinite !important;
        background: linear-gradient(180deg, transparent 0%, rgba(255, 255, 255, 0.8) 50%, transparent 100%) !important;
    }

    /* Horizontal Divider with Plus */
    .dropdown-divider-horizontal {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 8px 0 !important;
        position: relative !important;
        overflow: visible !important;
    }

    .dropdown-divider-horizontal .divider-line {
        flex: 1 !important;
        height: 1px !important;
        background: linear-gradient(90deg, transparent 0%, #cbd5e1 10%, #cbd5e1 90%, transparent 100%) !important;
        position: relative !important;
        overflow: hidden !important;
        transition: all 0.4s ease !important;
        opacity: 0.3 !important;
    }

    /* Animated horizontal divider effect */
    .dropdown-divider-horizontal .divider-line::before {
        content: '' !important;
        position: absolute !important;
        left: -100% !important;
        top: 0 !important;
        width: 100% !important;
        height: 2px !important;
        background: linear-gradient(90deg, transparent 0%, rgba(59, 130, 246, 0.8) 50%, transparent 100%) !important;
        opacity: 0 !important;
        transition: all 0.3s ease !important;
    }

    .dropdown-content:hover .dropdown-divider-horizontal .divider-line {
        background: linear-gradient(90deg, transparent 0%, #3b82f6 10%, #3b82f6 90%, transparent 100%) !important;
        height: 2px !important;
        box-shadow: 0 0 6px rgba(59, 130, 246, 0.3) !important;
        opacity: 1 !important;
    }

    .dropdown-content:hover .dropdown-divider-horizontal .divider-line::before {
        opacity: 1 !important;
        animation: horizontalDividerSlide 4s ease-in-out infinite !important;
        height: 3px !important;
        background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.9) 50%, transparent 100%) !important;
    }

    .dropdown-divider-horizontal .divider-plus {
        width: 28px !important;
        height: 28px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #3b82f6 !important;
        font-size: 18px !important;
        font-weight: 400 !important;
        background: #eff6ff !important;
        border-radius: 50% !important;
        border: 1px solid #dbeafe !important;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
        position: relative !important;
        overflow: hidden !important;
        z-index: 10 !important;
    }

    /* Pulsing effect for plus icon */
    .dropdown-divider-horizontal .divider-plus::before {
        content: '' !important;
        position: absolute !important;
        top: 50% !important;
        left: 50% !important;
        width: 100% !important;
        height: 100% !important;
        background: rgba(59, 130, 246, 0.2) !important;
        border-radius: 50% !important;
        transform: translate(-50%, -50%) scale(0) !important;
        opacity: 0 !important;
        transition: all 0.3s ease !important;
    }

    .dropdown-content:hover .dropdown-divider-horizontal .divider-plus {
        transform: rotate(90deg) scale(1.1) !important;
        background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4), 0 0 20px rgba(59, 130, 246, 0.2) !important;
        border-color: #3b82f6 !important;
    }

    .dropdown-content:hover .dropdown-divider-horizontal .divider-plus::before {
        opacity: 1 !important;
        animation: plusIconPulse 3s ease-in-out infinite !important;
        background: rgba(255, 255, 255, 0.3) !important;
        transform: translate(-50%, -50%) scale(1.5) !important;
    }

    /* Add active state for click interaction */
    .dropdown-divider-horizontal .divider-plus:active {
        transform: rotate(90deg) scale(0.95) !important;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.6) !important;
    }

    .dropdown-section-icon {
        width: 48px !important;
        height: 48px !important;
        border-radius: 12px !important;
        background: linear-gradient(135deg, #eff6ff, #dbeafe) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        color: #3b82f6 !important;
        margin-bottom: 14px !important;
        border: 1px solid #dbeafe !important;
        transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1) !important;
        position: relative !important;
        z-index: 1 !important;
    }

    .dropdown-section-icon svg {
        width: 24px !important;
        height: 24px !important;
        transition: all 0.3s ease !important;
    }

    .dropdown-section-content {
        margin-bottom: 0 !important;
        position: relative !important;
        z-index: 1 !important;
    }

    .dropdown-section-title {
        font-size: 15px !important;
        font-weight: 700 !important;
        color: #1e293b !important;
        margin: 0 !important;
        text-transform: none !important;
        letter-spacing: 0 !important;
        text-shadow: none !important;
        transition: color 0.3s ease !important;
    }

    .dropdown-section-desc {
        display: none !important;
    }

    .dropdown-coming-soon-inline {
        display: inline-block;
        margin-top: 8px;
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }

    /* Links List - Hidden by default with dynamic lines */
    .dropdown-links-list {
        display: none !important;
        flex-direction: column !important;
        gap: 0 !important;
        margin-top: 15px !important;
        padding-top: 0 !important;
        border-top: none !important;
        padding-left: 10px !important;
        border-left: 2px solid #3b82f6 !important;
    }

    .dropdown-links-list.show {
        display: flex !important;
    }

    .dropdown-links-list a {
        font-family: 'Poppins', 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        color: #334155 !important;
        text-decoration: none !important;
        padding: 12px 16px !important;
        border-radius: 0 !important;
        transition: all 0.25s ease !important;
        display: flex !important;
        align-items: center !important;
        background: #f8fafc !important;
        position: relative !important;
        margin-left: 0 !important;
        margin-bottom: 8px !important;
        border-radius: 8px !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06) !important;
        letter-spacing: 0.2px !important;
    }

    .dropdown-links-list .coming-soon-item {
        display: block !important;
        font-family: 'Poppins', 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        color: #64748b !important;
        text-decoration: none !important;
        padding: 12px 16px !important;
        border-radius: 8px !important;
        background: #f8fafc !important;
        border: 1px dashed #cbd5e1 !important;
        cursor: not-allowed !important;
        pointer-events: none !important;
        text-transform: none !important;
        letter-spacing: 0.2px !important;
        line-height: 1.35 !important;
        white-space: normal !important;
        word-break: break-word !important;
    }

    .dropdown-links-list .coming-soon-item .coming-soon-title {
        display: block;
        font-weight: 700;
        color: #475569;
    }

    .dropdown-links-list .coming-soon-item .coming-soon-note {
        display: block;
        margin-top: 4px;
        font-size: 11px;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }

    /* Dynamic Highlighted Line */
    .dropdown-links-list a .link-line {
        position: absolute !important;
        left: 0 !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        width: 0 !important;
        height: 2px !important;
        background: linear-gradient(90deg, #3b82f6, #60a5fa) !important;
        transition: width 0.3s ease !important;
        border-radius: 2px !important;
    }

    .dropdown-links-list a:hover .link-line {
        width: 20px !important;
    }

    .dropdown-links-list a::before {
        content: '' !important;
        display: none !important;
    }

    .dropdown-links-list a:hover {
        background: #eff6ff !important;
        color: #3b82f6 !important;
        padding-left: 30px !important;
        box-shadow: 0 2px 6px rgba(59, 130, 246, 0.2) !important;
    }

    /* Online Form Link Special Styling */
    .dropdown-links-list a[href="info_updation_form.php"] {
        animation: pulse-glow 2s infinite;
    }

    .dropdown-links-list a[href="info_updation_form.php"]:hover {
        background: linear-gradient(135deg, #15803d, #166534) !important;
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(22, 163, 74, 0.5) !important;
    }

    @keyframes pulse-glow {
        0%, 100% {
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
        }
        50% {
            box-shadow: 0 6px 20px rgba(22, 163, 74, 0.6);
        }
    }

    /* Animation for list items appearing */
    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-10px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Divider Animations */
    @keyframes verticalDividerGlow {
        0% {
            top: -100%;
            opacity: 0;
        }

        50% {
            opacity: 1;
        }

        100% {
            top: 100%;
            opacity: 0;
        }
    }

    @keyframes horizontalDividerSlide {
        0% {
            left: -100%;
            opacity: 0;
        }

        50% {
            opacity: 1;
        }

        100% {
            left: 100%;
            opacity: 0;
        }
    }

    @keyframes plusIconPulse {
        0% {
            transform: translate(-50%, -50%) scale(0);
            opacity: 1;
        }

        50% {
            transform: translate(-50%, -50%) scale(1.2);
            opacity: 0.6;
        }

        100% {
            transform: translate(-50%, -50%) scale(1.5);
            opacity: 0;
        }
    }

    .dropdown-links-list.show a {
        animation: slideInLeft 0.3s ease forwards !important;
    }

    .dropdown-links-list.show a:nth-child(1) {
        animation-delay: 0.05s !important;
    }

    .dropdown-links-list.show a:nth-child(2) {
        animation-delay: 0.1s !important;
    }

    .dropdown-links-list.show a:nth-child(3) {
        animation-delay: 0.15s !important;
    }

    .dropdown-links-list.show a:nth-child(4) {
        animation-delay: 0.2s !important;
    }

    .dropdown-links-list.show a:nth-child(5) {
        animation-delay: 0.25s !important;
    }

    /* Highlighted Section Style */
    .training-section.highlighted-section {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(59, 130, 246, 0.02)) !important;
        border-radius: 10px !important;
        border: 1px solid rgba(59, 130, 246, 0.2) !important;
    }

    .training-section.highlighted-section:hover {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(59, 130, 246, 0.05)) !important;
    }

    .sub-label-app {
        display: block;
        font-size: 10px !important;
        font-weight: 600 !important;
        color: #64748b !important;
        margin-top: 5px !important;
        line-height: 1.4 !important;
        letter-spacing: 0.1px;
        font-family: 'Inter', sans-serif;
    }

    .sub-label-surveillance {
        display: inline-flex !important;
        align-items: center;
        font-size: 9px !important;
        font-weight: 800 !important;
        color: #ffffff !important;
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
        padding: 3px 10px !important;
        border-radius: 50px !important;
        margin-top: 8px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.8px !important;
        box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3) !important;
        white-space: nowrap;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }
</style>
<script type="text/javascript">
    /// some script
    // jquery ready start
    $(document).ready(function () {
        // jQuery code

        //////////////////////// Prevent closing from click inside dropdown
        $(document).on('hover', '.dropdown-menu', function (e) {
            e.stopPropagation();
        });

        // Training Section Click to Expand
        $('.training-section').on('click', function (e) {
            e.stopPropagation();
            var $this = $(this);

            if ($this.hasClass('disabled-training-section')) {
                return;
            }

            var $linksList = $this.find('.dropdown-links-list');

            // If clicking on a link inside, don't toggle
            if ($(e.target).is('a') || $(e.target).closest('a').length) {
                return;
            }

            // Close other sections
            $('.training-section').not($this).removeClass('active');
            $('.training-section').not($this).find('.dropdown-links-list').removeClass('show').hide();

            // Toggle current section
            $this.toggleClass('active');
            if ($this.hasClass('active')) {
                $linksList.addClass('show').slideDown(200);
            } else {
                $linksList.removeClass('show').slideUp(200);
            }
        });

        // Reset when dropdown closes
        $('.dropdown').on('mouseleave', function () {
            var $dropdown = $(this);
            setTimeout(function () {
                if (!$dropdown.is(':hover')) {
                    $dropdown.find('.training-section').removeClass('active');
                    $dropdown.find('.dropdown-links-list').removeClass('show').hide();
                }
            }, 300);
        });

    }); // jquery end
</script>
<!-- Start header -->

<head>

</head>
<div class="aitf-public-announcement-banner" aria-label="AITF announcements">
    <div class="aitf-public-announcement-track">
        <?php for ($announcementSet = 0; $announcementSet < 2; $announcementSet++) { ?>
            <div class="aitf-public-announcement-group" aria-hidden="<?php echo $announcementSet === 0 ? 'false' : 'true'; ?>">
                <span class="aitf-public-announcement-item"><i class="fa fa-certificate" aria-hidden="true"></i>NABCB Accredited Certification Body</span>
                <span class="aitf-public-announcement-divider" aria-hidden="true"></span>
                <span class="aitf-public-announcement-item"><i class="fa fa-balance-scale" aria-hidden="true"></i> IMPARTIAL ASSESSMENT</span>
                <span class="aitf-public-announcement-divider" aria-hidden="true"></span>
                <span class="aitf-public-announcement-item"><i class="fa fa-shield" aria-hidden="true"></i>Trusted. Accredited. Independent.</span>
                <span class="aitf-public-announcement-divider" aria-hidden="true"></span>
                <span class="aitf-public-announcement-item"><i class="fa fa-handshake-o" aria-hidden="true"></i>AUTHENTICATE CERTIFICATE</span>
                <span class="aitf-public-announcement-divider" aria-hidden="true"></span>
            </div>
        <?php } ?>
    </div>
</div>

<header class="top-header">
    <nav class="navbar header-nav navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php"><img src="aitfImages/newlogo.png" alt="AITF Logo" class="img-fluid"
                    style="height: 100px !important; width: auto !important; max-height: 100px !important;"></a>
            <button class="navbar-toggler" type="button" aria-controls="navbar-wd" aria-expanded="false"
                aria-label="Toggle navigation" data-mobile-nav-toggle>
                <span class="mobile-toggle-bar"></span>
                <span class="mobile-toggle-bar"></span>
                <span class="mobile-toggle-bar"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbar-wd">

                <div class="navbar-nav nav-primary d-none d-lg-flex">
                    <div><a class="nav-link" href="index.php">Home</a></div>
                    <div class="dropdown">
                        <a class="nav-link" href="#">Certification</a>
                        <div class="dropdown-content dropdown-modern-grid">
                            <!-- First Row -->
                            <div class="dropdown-grid-row">
                                <!-- ABOUT ISO -->
                                <div class="dropdown-grid-col training-section" data-section="aboutiso">
                                    <div class="dropdown-section-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM13 17H11V11H13V17ZM13 9H11V7H13V9Z"
                                                fill="currentColor" />
                                        </svg>
                                    </div>
                                    <div class="dropdown-section-content">
                                        <h3 class="dropdown-section-title">About ISO</h3>
                                    </div>
                                    <div class="dropdown-links-list" style="display:none;">
                                        <a href="aboutISO.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>What is ISO</a>
                                        <a href="policy.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>Policy</a>
                                    </div>
                                </div>

                                <!-- Vertical Divider -->
                                <div class="dropdown-divider-vertical"></div>

                                <!-- CERTIFICATION PROCESS -->
                                <div class="dropdown-grid-col training-section" data-section="certprocess">
                                    <div class="dropdown-section-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M14 2H6C4.9 2 4 2.9 4 4V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V8L14 2ZM16 18H8V16H16V18ZM16 14H8V12H16V14ZM13 9V3.5L18.5 9H13Z"
                                                fill="currentColor" />
                                        </svg>
                                    </div>
                                    <div class="dropdown-section-content">
                                        <h3 class="dropdown-section-title">Certification Process</h3>
                                    </div>
                                    <div class="dropdown-links-list" style="display:none;">
                                        <a href="certificationProcess.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>Certification Process</a>
                                        <a href="criteria.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>Criteria</a>
                                        <a href="application.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>Application Form</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Horizontal Divider with Plus -->
                            <div class="dropdown-divider-horizontal">
                                <div class="divider-line"></div>
                                <div class="divider-plus">+</div>
                                <div class="divider-line"></div>
                            </div>

                            <!-- Second Row -->
                            <div class="dropdown-grid-row">
                                <!-- EXPECTED OUTCOME -->
                                <div class="dropdown-grid-col training-section" data-section="outcome">
                                    <div class="dropdown-section-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"
                                                fill="currentColor" />
                                        </svg>
                                    </div>
                                    <div class="dropdown-section-content">
                                        <h3 class="dropdown-section-title">Expected Outcome</h3>
                                    </div>
                                    <div class="dropdown-links-list" style="display:none;">
                                        <a href="expectedOutcome.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>Benefits of Certification</a>
                                    </div>
                                </div>

                                <!-- Vertical Divider -->
                                <div class="dropdown-divider-vertical"></div>

                                <!-- COMPLAINTS & IMPARTIALITY -->
                                <div class="dropdown-grid-col training-section" data-section="complaints">
                                    <div class="dropdown-section-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3ZM12 17.5C11.17 17.5 10.5 16.83 10.5 16C10.5 15.17 11.17 14.5 12 14.5C12.83 14.5 13.5 15.17 13.5 16C13.5 16.83 12.83 17.5 12 17.5ZM14.5 10C14.5 11.5 13.5 12 13 12.5C12.5 13 12.5 13.5 12.5 14H11.5C11.5 13 11.5 12.5 12 12C12.5 11.5 13.5 11 13.5 10C13.5 9.17 12.83 8.5 12 8.5C11.17 8.5 10.5 9.17 10.5 10H9.5C9.5 8.62 10.62 7.5 12 7.5C13.38 7.5 14.5 8.62 14.5 10Z"
                                                fill="currentColor" />
                                        </svg>
                                    </div>
                                    <div class="dropdown-section-content">
                                        <h3 class="dropdown-section-title">Complaints & Appeals</h3>
                                    </div>
                                    <div class="dropdown-links-list" style="display:none;">
                                        <a href="complaintsAppeals.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>Complaints & Appeals</a>
                                        <a href="impartiality.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>Impartiality</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="dropdown">
                        <a class="nav-link" href="#">Training</a>
                        <div class="dropdown-content dropdown-modern-grid">
                            <!-- First Row -->
                            <div class="dropdown-grid-row">
                                <!-- EXEMPLAR GLOBAL CERTIFIED -->
                                <div class="dropdown-grid-col training-section disabled-training-section" data-section="exemplar">
                                    <div class="dropdown-section-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"
                                                fill="currentColor" />
                                        </svg>
                                    </div>
                                    <div class="dropdown-section-content">
                                        <h3 class="dropdown-section-title">Exemplar Global Certified</h3>
                                        <span class="dropdown-coming-soon-inline">Comming soon</span>
                                    </div>
                                    <div class="dropdown-links-list" style="display:none;">
                                        <span class="coming-soon-item">Comming soon</span>
                                    </div>
                                </div>

                                <!-- Vertical Divider -->
                                <div class="dropdown-divider-vertical"></div>

                                <!-- AWARENESS TRAINING -->
                                <div class="dropdown-grid-col training-section" data-section="awareness">
                                    <div class="dropdown-section-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M12 6.5C13.38 6.5 14.5 7.62 14.5 9C14.5 10.38 13.38 11.5 12 11.5C10.62 11.5 9.5 10.38 9.5 9C9.5 7.62 10.62 6.5 12 6.5ZM12 14.5C15.5 14.5 18.5 16.08 18.5 17V18.5H5.5V17C5.5 16.08 8.5 14.5 12 14.5Z"
                                                fill="currentColor" />
                                        </svg>
                                    </div>
                                    <div class="dropdown-section-content">
                                        <h3 class="dropdown-section-title">Awareness Training</h3>
                                    </div>
                                    <div class="dropdown-links-list" style="display:none;">
                                        <a href="qms_iso_9001_03.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>QMS ISO 9001:2015</a>
                                        <a href="ems_iso_9001_03.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>EMS ISO 14001:2015</a>
                                        <a href="ohsms_iso_45001_03.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>OHSMS ISO 45001:2018</a>
                                        <a href="bcms_iso_22301_03.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>BCMS ISO 22301:2012</a>
                                        <a href="isms_iso_27001_03.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>ISMS ISO 27001:2013</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Horizontal Divider with Plus -->
                            <div class="dropdown-divider-horizontal">
                                <div class="divider-line"></div>
                                <div class="divider-plus">+</div>
                                <div class="divider-line"></div>
                            </div>

                            <!-- Second Row -->
                            <div class="dropdown-grid-row">
                                <!-- INTERNAL AUDITOR TRAINING -->
                                <div class="dropdown-grid-col training-section" data-section="internal">
                                    <div class="dropdown-section-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M9 11H7V13H9V11ZM13 11H11V13H13V11ZM17 11H15V13H17V11ZM19 4H18V2H16V4H8V2H6V4H5C3.89 4 3.01 4.9 3.01 6L3 20C3 21.1 3.89 22 5 22H19C20.1 22 21 21.1 21 20V6C21 4.9 20.1 4 19 4ZM19 20H5V9H19V20Z"
                                                fill="currentColor" />
                                        </svg>
                                    </div>
                                    <div class="dropdown-section-content">
                                        <h3 class="dropdown-section-title">Internal Auditor Training</h3>
                                    </div>
                                    <div class="dropdown-links-list" style="display:none;">
                                        <a href="qms_iso_9001_04.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>QMS ISO 9001:2015</a>
                                        <a href="ems_iso_9001_04.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>EMS ISO 14001:2015</a>
                                        <a href="ohsms_iso_45001_04.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>OHSMS ISO 45001:2018</a>
                                        <a href="bcms_iso_22301_04.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>BCMS ISO 22301:2012</a>
                                        <a href="isms_iso_27001_04.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>ISMS ISO 27001:2013</a>
                                    </div>
                                </div>

                                <!-- Vertical Divider -->
                                <div class="dropdown-divider-vertical"></div>

                                <!-- LEAD AUDITOR TRAINING -->
                                <div class="dropdown-grid-col training-section" data-section="lead">
                                    <div class="dropdown-section-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M20 6H12L10 4H4C2.9 4 2.01 4.9 2.01 6L2 18C2 19.1 2.9 20 4 20H20C21.1 20 22 19.1 22 18V8C22 6.9 21.1 6 20 6ZM18 12H13V17H11V12H6L12 6L18 12Z"
                                                fill="currentColor" />
                                        </svg>
                                    </div>
                                    <div class="dropdown-section-content">
                                        <h3 class="dropdown-section-title">Lead Auditor Training</h3>
                                    </div>
                                    <div class="dropdown-links-list" style="display:none;">
                                        <a href="qms_iso_9001_04.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>QMS ISO 9001:2015 Lead</a>
                                        <a href="ems_iso_9001_04.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>EMS ISO 14001:2015 Lead</a>
                                        <a href="ohsms_iso_45001_04.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>OHSMS ISO 45001:2018 Lead</a>
                                        <a href="isms_iso_27001_04.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>ISMS ISO 27001:2022 Lead</a>
                                        <a href="integrated_mgmgt_system.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>Integrated Systems Lead</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div><a class="nav-link" href="validation.php">Certificate validation</a></div>
                    <div><a class="nav-link" href="application.php#online-application-form">Application Form</a></div>
                    <div class="dropdown">
                        <a class="nav-link" href="#">Download</a>
                        <div class="dropdown-content dropdown-modern-grid">
                            <!-- First Row -->
                            <div class="dropdown-grid-row">
                                <!-- APPLICATION FORM - Highlighted -->
                                <div class="dropdown-grid-col training-section highlighted-section"
                                    data-section="appform">
                                    <div class="dropdown-section-icon"
                                        style="background: linear-gradient(135deg, #3b82f6, #1d4ed8) !important; color: #ffffff !important;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3ZM17 13H13V17H11V13H7V11H11V7H13V11H17V13Z"
                                                fill="currentColor" />
                                        </svg>
                                    </div>
                                    <div class="dropdown-section-content">
                                        <h3 class="dropdown-section-title"
                                            style="color: #3b82f6 !important; line-height: 1.2;">
                                            Application Form
                                            <span class="sub-label-app">(for New Certification & Recertification)</span>
                                        </h3>
                                    </div>
                                    <div class="dropdown-links-list" style="display:none;">
                                        <a href="text/audit_application_form.pdf" download="Audit_Application_Form.pdf"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>Audit Application Form</a>
                                        <a href="application.php#online-application-form"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>Application Form (Online)</a>
                                    </div>
                                </div>

                                <!-- Vertical Divider -->
                                <div class="dropdown-divider-vertical"></div>

                                <!-- APPLICATION & CERTIFICATION -->
                                <div class="dropdown-grid-col training-section" data-section="certification">
                                    <div class="dropdown-section-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M14 2H6C4.9 2 4 2.9 4 4V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V8L14 2ZM16 18H8V16H16V18ZM16 14H8V12H16V14ZM13 9V3.5L18.5 9H13Z"
                                                fill="currentColor" />
                                        </svg>
                                    </div>
                                    <div class="dropdown-section-content">
                                        <h3 class="dropdown-section-title">Certification</h3>
                                    </div>
                                    <div class="dropdown-links-list" style="display:none;">
                                        <a href="certificationGuideline.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>Certification Guideline</a>
                                        <span class="coming-soon-item"><span class="coming-soon-title">Auditor Guideline</span><span class="coming-soon-note">Comming soon</span></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Horizontal Divider with Plus -->
                            <div class="dropdown-divider-horizontal">
                                <div class="divider-line"></div>
                                <div class="divider-plus">+</div>
                                <div class="divider-line"></div>
                            </div>

                            <!-- Second Row -->
                            <div class="dropdown-grid-row">
                                <!-- GUIDELINES -->
                                <div class="dropdown-grid-col training-section" data-section="guidelines">
                                    <div class="dropdown-section-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM13 17H11V11H13V17ZM13 9H11V7H13V9Z"
                                                fill="currentColor" />
                                        </svg>
                                    </div>
                                    <div class="dropdown-section-content">
                                        <h3 class="dropdown-section-title">Guidelines</h3>
                                    </div>
                                    <div class="dropdown-links-list" style="display:none;">
                                        <a href="useOfLogo.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>Logo Usage Guidelines</a>
                                        <a href="transitionGuideline.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>Transition Guidelines</a>
                                    </div>
                                </div>

                                <!-- Vertical Divider -->
                                <div class="dropdown-divider-vertical"></div>

                                <!-- REPORTS & UPDATES -->
                                <div class="dropdown-grid-col training-section" data-section="reports">
                                    <div class="dropdown-section-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M19 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3ZM9 17H7V10H9V17ZM13 17H11V7H13V17ZM17 17H15V13H17V17Z"
                                                fill="currentColor" />
                                        </svg>
                                    </div>
                                    <div class="dropdown-section-content">
                                        <h3 class="dropdown-section-title" style="line-height: 1.2;">
                                            Reports & Updates
                                            <span class="sub-label-app">(Surveillance Audit)</span>
                                        </h3>
                                    </div>
                                    <div class="dropdown-links-list" style="display:none;">
                                        <a href="ncClosure.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>NC Report & Closure</a>
                                        <a href="infoUpdationForm.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>Information Update Form (PDF)</a>
                                        <a href="info_updation_form.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>Information Update Form (Fill Online)</a>
                                        <a href="circuler.php"
                                            style="color:#1e293b !important; background:#f1f5f9 !important; font-weight:600 !important;"><span
                                                class="link-line"></span>Circulars</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div><a class="nav-link" href="aboutus.php">About Us</a></div>
                    <div><a class="nav-link" href="contactUS.php">Contact Us</a></div>
                    <div><a class="nav-link login-btn" href="loginSelection.php"> Login</a></div>
                </div>
            </div>
    </nav>

    <div class="mobile-nav-overlay" id="mobile-nav-overlay">
        <div class="mobile-nav-drawer">
            <button class="mobile-nav-close mobile-nav-close-fab" type="button" data-mobile-nav-close
                aria-label="Close menu">&times;</button>
            <div class="mobile-nav-panels">
                <section class="mobile-nav-panel" data-panel="root">
                    <div class="mobile-nav-panel-header">
                        <div class="mobile-nav-title">Menu</div>
                    </div>
                    <ul class="mobile-nav-list">
                        <li>
                            <a class="mobile-nav-item" href="index.php">
                                <span>Home</span>
                            </a>
                        </li>
                        <li>
                            <button class="mobile-nav-item has-children" type="button"
                                data-mobile-nav-open="certification">
                                <span>Certification</span>
                                <span class="mobile-nav-chevron">&#8250;</span>
                            </button>
                        </li>
                        <li>
                            <button class="mobile-nav-item has-children" type="button" data-mobile-nav-open="training">
                                <span>Training</span>
                                <span class="mobile-nav-chevron">&#8250;</span>
                            </button>
                        </li>
                        <li>
                            <a class="mobile-nav-item" href="validation.php">
                                <span>Certificate Validation</span>
                            </a>
                        </li>
                        <li>
                            <a class="mobile-nav-item" href="application.php#online-application-form">
                                <div style="display: flex; flex-direction: column;">
                                    <span>Application Form</span>
                                    <span class="sub-label-app" style="margin-top: 2px !important;">(new certification &
                                        recertification)</span>
                                </div>
                            </a>
                        </li>
                        <li>
                            <button class="mobile-nav-item has-children" type="button" data-mobile-nav-open="download">
                                <span>Download</span>
                                <span class="mobile-nav-chevron">&#8250;</span>
                            </button>
                        </li>
                        <li>
                            <a class="mobile-nav-item" href="aboutus.php">
                                <span>About Us</span>
                            </a>
                        </li>
                        <li>
                            <a class="mobile-nav-item" href="contactUS.php">
                                <span>Contact Us</span>
                            </a>
                        </li>
                        <li>
                            <a class="mobile-nav-item mobile-login-btn" href="loginSelection.php">
                                <span>Login</span>
                            </a>
                        </li>
                    </ul>
                </section>

                <section class="mobile-nav-panel" data-panel="certification">
                    <div class="mobile-nav-panel-header">
                        <button class="mobile-nav-back" type="button" data-mobile-nav-back="root">
                            &#8249;
                        </button>
                        <div class="mobile-nav-title">Certification</div>
                    </div>
                    <ul class="mobile-nav-list">
                        <li><a class="mobile-nav-item" href="certificationProcess.php">Certification Process</a></li>
                        <li><a class="mobile-nav-item" href="aboutISO.php">About ISO</a></li>
                        <li><a class="mobile-nav-item" href="expectedOutcome.php">Expected Outcome</a></li>
                        <li><a class="mobile-nav-item" href="criteria.php">Criteria</a></li>
                        <li><a class="mobile-nav-item" href="complaintsAppeals.php">Complaints &amp; Appeals</a></li>
                        <li><a class="mobile-nav-item" href="impartiality.php">Impartiality</a></li>
                        <li><a class="mobile-nav-item" href="application.php">Application Form</a></li>
                    </ul>
                </section>

                <section class="mobile-nav-panel" data-panel="training">
                    <div class="mobile-nav-panel-header">
                        <button class="mobile-nav-back" type="button" data-mobile-nav-back="root">
                            &#8249;
                        </button>
                        <div class="mobile-nav-title">Training</div>
                    </div>
                    <ul class="mobile-nav-list">
                        <li class="mobile-nav-section-label">Exemplar Global Certified</li>
                        <li><span class="mobile-nav-item" style="display:block; opacity:0.7; cursor:not-allowed; pointer-events:none;">Comming soon</span></li>
                        <li class="mobile-nav-section-label">Awareness Training</li>
                        <li><a class="mobile-nav-item" href="qms_iso_9001_03.php">QMS ISO 9001:2015</a></li>
                        <li><a class="mobile-nav-item" href="ems_iso_9001_03.php">EMS ISO 14001:2015</a></li>
                        <li><a class="mobile-nav-item" href="ohsms_iso_45001_03.php">OHSMS ISO 45001:2018</a></li>
                        <li><a class="mobile-nav-item" href="bcms_iso_22301_03.php">BCMS ISO 22301:2012</a></li>
                        <li><a class="mobile-nav-item" href="isms_iso_27001_03.php">ISMS ISO 27001:2013</a></li>
                        <li class="mobile-nav-section-label">Internal &amp; Lead Auditor</li>
                        <li><a class="mobile-nav-item" href="qms_iso_9001_04.php">QMS ISO 9001:2015</a></li>
                        <li><a class="mobile-nav-item" href="ems_iso_9001_04.php">EMS ISO 14001:2015</a></li>
                        <li><a class="mobile-nav-item" href="ohsms_iso_45001_04.php">OHSMS ISO 45001:2018</a></li>
                        <li><a class="mobile-nav-item" href="bcms_iso_22301_04.php">BCMS ISO 22301:2012</a></li>
                        <li><a class="mobile-nav-item" href="isms_iso_27001_04.php">ISMS ISO 27001:2013</a></li>
                        <li><a class="mobile-nav-item" href="integrated_mgmgt_system.php">Integrated Management
                                Systems</a></li>
                        <li><a class="mobile-nav-item" href="md_qms_iso.php">MD-QMS ISO 13485:2016</a></li>
                    </ul>
                </section>

                <section class="mobile-nav-panel" data-panel="download">
                    <div class="mobile-nav-panel-header">
                        <button class="mobile-nav-back" type="button" data-mobile-nav-back="root">
                            &#8249;
                        </button>
                        <div class="mobile-nav-title">Download</div>
                    </div>
                    <ul class="mobile-nav-list">
                        <li class="mobile-nav-section-label">Application &amp; Certification</li>
                        <li>
                            <a class="mobile-nav-item" href="application.php">
                                <div style="display: flex; flex-direction: column;">
                                    <span>Application Form</span>
                                    <span class="sub-label-app" style="margin-top: 2px !important;">(new certification &
                                        recertification)</span>
                                </div>
                            </a>
                        </li>
                        <li><a class="mobile-nav-item" href="certificationGuideline.php">Certification Guideline</a>
                        </li>
                        <li><span class="mobile-nav-item" style="display:block; opacity:0.7; cursor:not-allowed; pointer-events:none;">Auditor Guideline - Comming soon</span></li>
                        <li class="mobile-nav-section-label">Guidelines</li>
                        <li><a class="mobile-nav-item" href="useOfLogo.php">Logo Usage Guidelines</a></li>
                        <li><a class="mobile-nav-item" href="transitionGuideline.php">Transition Guidelines</a></li>
                        <li class="mobile-nav-section-label">
                            <div style="display: flex; flex-direction: column;">
                                <span>Reports &amp; Updates</span>
                                <span class="sub-label-app"
                                    style="margin-top: 2px !important; text-transform: none !important; letter-spacing: normal !important;">(Surveillance
                                    Audit)</span>
                            </div>
                        </li>
                        <li><a class="mobile-nav-item" href="ncClosure.php">NC Report &amp; Closure</a></li>
                        <li><a class="mobile-nav-item" href="infoUpdationForm.php">Information Update Form (PDF)</a></li>
                        <li><a class="mobile-nav-item" href="info_updation_form.php">Information Update Form (Fill Online)</a></li>
                        <li><a class="mobile-nav-item" href="circuler.php">Circulars</a></li>
                    </ul>
                </section>
            </div>
        </div>
    </div>
</header>
<!-- End header -->

<script type="text/javascript">
    $(document).ready(function () {
        // Smooth navbar background based on scroll position
        var header = document.querySelector('.top-header');
        var scrollThreshold = 80;
        var ticking = false;

        function updateNavBg() {
            if (!header) return;
            var scrollY = window.pageYOffset || document.documentElement.scrollTop;

            // Toggle .scrolled class for text color changes
            if (scrollY > 30) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }

            // Dynamic background opacity based on scroll position
            if (document.body.id === 'home') {
                var opacity = Math.min(scrollY / scrollThreshold, 1);
                var r = Math.round(255);
                var g = Math.round(255);
                var b = Math.round(255);
                header.style.background = 'rgba(' + r + ',' + g + ',' + b + ',' + opacity + ')';

                // Dynamic box-shadow
                if (opacity > 0.1) {
                    header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, ' + (0.08 * opacity) + ')';
                } else {
                    header.style.boxShadow = 'none';
                }

                // Dynamic border
                if (opacity > 0.5) {
                    header.style.borderBottom = '1px solid rgba(0, 0, 0, ' + (0.08 * opacity) + ')';
                } else {
                    header.style.borderBottom = '1px solid rgba(255, 255, 255, ' + (0.15 * (1 - opacity)) + ')';
                }
            }

            ticking = false;
        }

        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(updateNavBg);
                ticking = true;
            }
        });
        // Run immediately to prevent delays
        updateNavBg();
        document.addEventListener('DOMContentLoaded', updateNavBg);
        window.addEventListener('load', updateNavBg);

        // Enhanced dropdown menu functionality - No transitions, instant show/hide
        var activeDropdown = null;

        function closeActiveDropdown() {
            if (activeDropdown) {
                var $activeMenu = activeDropdown.find('.dropdown-content');
                activeDropdown.removeClass('open');
                activeDropdown.find('> a').removeClass('active');
                $activeMenu.css({
                    'opacity': '0',
                    'visibility': 'hidden',
                    'pointer-events': 'none',
                    'display': 'none'
                });
                activeDropdown = null;
            }
        }

        $('.navbar .dropdown').each(function () {
            var $dropdown = $(this);
            var $toggle = $dropdown.find('> a');
            var $menu = $dropdown.find('.dropdown-content');
            var isHovered = false;

            // Function to open dropdown
            function openDropdown() {
                // Close other dropdowns first
                if (activeDropdown && activeDropdown[0] !== $dropdown[0]) {
                    closeActiveDropdown();
                }

                $dropdown.addClass('open');
                $menu.css({
                    'opacity': '1',
                    'visibility': 'visible',
                    'pointer-events': 'auto',
                    'display': 'block'
                });

                activeDropdown = $dropdown;
                $toggle.addClass('active');
            }

            // Function to close dropdown
            function closeDropdown() {
                $dropdown.removeClass('open');
                $menu.css({
                    'opacity': '0',
                    'visibility': 'hidden',
                    'pointer-events': 'none',
                    'display': 'none'
                });
                $toggle.removeClass('active');

                if (activeDropdown && activeDropdown[0] === $dropdown[0]) {
                    activeDropdown = null;
                }
            }

            // Toggle on click
            $toggle.on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                if ($dropdown.hasClass('open')) {
                    closeDropdown();
                } else {
                    openDropdown();
                }
            });

            var closeTimer;

            // Hover behavior for the dropdown toggle
            $toggle.hover(
                function () {
                    clearTimeout(closeTimer);
                    isHovered = true;
                    openDropdown();
                },
                function () {
                    isHovered = false;
                    if (activeDropdown && activeDropdown[0] === $dropdown[0]) {
                        closeTimer = setTimeout(function () {
                            closeDropdown();
                        }, 300); // 300ms delay before closing
                    }
                }
            );

            // Hover behavior for the dropdown menu
            $menu.hover(
                function () {
                    clearTimeout(closeTimer);
                    isHovered = true;
                },
                function () {
                    isHovered = false;
                    closeTimer = setTimeout(function () {
                        closeDropdown();
                    }, 300); // 300ms delay before closing
                }
            );

            // Clear timeout when entering the dropdown again
            $dropdown.add($menu).on('mouseenter', function () {
                clearTimeout(closeTimer);
                isHovered = true;
            });
        });

        // Close when clicking outside
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.dropdown').length) {
                closeActiveDropdown();
            }
        });

        // Close dropdown when clicking on a link
        $('.dropdown-content a').on('click', function (e) {
            e.stopPropagation();
            closeActiveDropdown();
        });

        // Close dropdown when pressing Escape key
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && activeDropdown) {
                closeActiveDropdown();
            }
        });

        // Ensure mobile nav overlay is attached directly to <body>
        // so it can truly span the full viewport on all pages.
        var $overlay = $('#mobile-nav-overlay');
        if ($overlay.length && !$overlay.parent().is('body')) {
            $overlay.appendTo('body');
        }
        var $panelsContainer = $overlay.find('.mobile-nav-panels');
        var isNavOpen = false;
        var firstToggle = true; // ensure first click always opens menu
        var panelIndex = {
            'root': 0,
            'certification': 1,
            'training': 2,
            'download': 3
        };

        function setPanel(name) {
            var index = panelIndex[name] || 0;
            $panelsContainer.css('transform', 'translateX(-' + (index * 100) + '%)');
        }

        function openMobileNav() {
            $overlay.addClass('is-open');
            $('body').addClass('mobile-nav-open').css('overflow', 'hidden');
            setPanel('root');
            isNavOpen = true;
        }

        function closeMobileNav() {
            $overlay.removeClass('is-open');
            $('body').removeClass('mobile-nav-open').css('overflow', '');
            // Always reset panels back to the root menu when closing
            setPanel('root');
            isNavOpen = false;
        }

        // Force a clean initial state on first load / back navigation
        $overlay.removeClass('is-open');
        $('body').removeClass('mobile-nav-open').css('overflow', '');
        isNavOpen = false;

        $('[data-mobile-nav-toggle]').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            // On the very first tap, always force the menu to open
            if (firstToggle) {
                openMobileNav();
                firstToggle = false;
                return false;
            }

            if (isNavOpen) {
                closeMobileNav();
            } else {
                openMobileNav();
            }

            return false;
        });

        $overlay.on('click', function (e) {
            if ($(e.target).is('#mobile-nav-overlay')) {
                e.preventDefault();
                e.stopPropagation();
                closeMobileNav();
                return false;
            }
        });

        $overlay.find('[data-mobile-nav-close]').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            closeMobileNav();
            return false;
        });

        $overlay.find('[data-mobile-nav-open]').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var target = $(this).data('mobileNavOpen');
            setPanel(target);
            return false;
        });

        $overlay.find('[data-mobile-nav-back]').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var target = $(this).data('mobileNavBack') || 'root';
            setPanel(target);
            return false;
        });
    });
</script>
