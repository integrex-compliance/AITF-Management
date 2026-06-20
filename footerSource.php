<!-- Modern Footer -->
<style>
    /* Reset any parent container constraints */
    html,
    body {
        width: 100% !important;
        max-width: 100% !important;
        overflow-x: hidden;
        margin: 0 !important;
        padding: 0 !important;
    }

    :root {
        --primary-color: #0f172a;
        --secondary-color: #1e293b;
        --accent-color: #3b82f6;
        --text-light: #ffffff;
        --text-muted: #94a3b8;
        --text-secondary: #cbd5e1;
        --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        --card-bg: rgba(255, 255, 255, 0.05);
        --transition: all 0.3s ease;
    }

    .footer-box {
        background: var(--bg-gradient);
        color: var(--text-light);
        position: relative;
        overflow: hidden;
        padding: 60px 0 0;
        font-family: 'Inter', sans-serif;
        width: 100vw !important;
        max-width: 100% !important;
        margin: 0 !important;
        left: 50% !important;
        right: 50% !important;
        margin-left: -50vw !important;
        margin-right: -50vw !important;
        box-sizing: border-box;
    }

    /* Ensure footer takes full width and overrides any container constraints */
    .footer-box {
        width: 100vw !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 60px 0 0 !important;
        position: relative;
        left: 50%;
        right: 50%;
        margin-left: -50vw !important;
        margin-right: -50vw !important;
    }

    .footer-box .container {
        max-width: 1200px !important;
        width: 100% !important;
        margin: 0 auto !important;
        padding: 0 15px !important;
        position: relative;
        z-index: 2;
    }

    .footer_blog {
        background: transparent;
        padding: 0;
        height: 100%;
        transition: var(--transition);
        margin-bottom: 30px;
    }

    .footer_blog h3 {
        color: white;
        font-size: 18px;
        font-weight: 600;
        margin: 0 0 25px 0;
        padding: 0 0 10px 0;
        position: relative;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-family: 'Inter', sans-serif;
        border: none !important;
    }

    .footer_blog h3::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 40px;
        height: 2px;
        background: var(--accent-color);
    }

    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links li {
        margin-bottom: 12px;
    }

    .footer-links a {
        color: var(--text-muted);
        text-decoration: none;
        transition: var(--transition);
        display: block;
        padding: 4px 0;
        position: relative;
        font-size: 14px;
    }

    .footer-links a:hover {
        color: var(--text-light);
        transform: translateX(5px);
    }

    .social-links {
        display: flex;
        gap: 15px;
        margin-top: 20px;
    }

    .social-link {
        display: flex;
        align-items: center;
        color: var(--text-secondary) !important;
        text-decoration: none !important;
        transition: var(--transition);
        margin-bottom: 12px;
    }

    .social-link i {
        color: var(--accent-color) !important;
        margin-right: 12px !important;
        font-size: 18px !important;
        transition: var(--transition) !important;
    }

    .social-link:hover {
        background: transparent;
        transform: translateX(5px);
    }

    .social-link:hover i {
        color: var(--accent-hover) !important;
    }

    .footer-bottom {
        background: rgba(0, 0, 0, 0.3);
        padding: 20px 0;
        margin-top: 50px;
        font-size: 14px;
        color: var(--text-muted);
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        text-align: center;
    }

    .crp {
        color: var(--text-muted) !important;
        font-size: 0.9rem;
        margin: 0;
        text-align: center;
        font-weight: 400;
        line-height: 1.7;
        font-family: 'Inter', sans-serif;
    }

    .crp a {
        color: var(--text-secondary) !important;
        text-decoration: none;
        transition: var(--transition);
        margin: 0 8px;
        font-weight: 500;
    }

    .crp a:hover {
        color: var(--accent-color) !important;
    }

    #scroll-to-top {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: var(--accent-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        opacity: 0;
        visibility: hidden;
        transition: var(--transition);
        z-index: 999;
        box-shadow: 0 5px 20px rgba(59, 130, 246, 0.3);
        border: none;
        cursor: pointer;
    }

    #scroll-to-top.show {
        opacity: 1;
        visibility: visible;
    }

    #scroll-to-top:hover {
        background: #2563eb;
        transform: translateY(-5px);
    }

    /* Responsive adjustments */
    @media (max-width: 991px) {
        .footer_blog {
            margin-bottom: 30px;
        }

        .footer_blog h3 {
            font-size: 16px;
        }
    }

    @media (max-width: 767px) {
        .footer-box {
            padding: 50px 0 0;
        }

        .footer_blog {
            margin-bottom: 30px;
        }

        .social-links {
            justify-content: center;
        }

        .footer-box .row {
            row-gap: 24px;
        }

        .footer_blog h3 {
            text-align: left;
        }
    }

    @media (max-width: 575px) {
        .footer-box {
            padding: 40px 0 0;
        }

        .footer-box .row {
            row-gap: 20px;
        }

        .footer_blog h3 {
            text-align: center;
        }

        .footer-links a {
            text-align: center;
        }

        .social-links {
            flex-direction: column;
            align-items: center;
        }

        .footer-bottom .container {
            text-align: center;
        }
    }
</style>

<!-- Full Width Footer Wrapper -->
<div
    style="width: 100vw; position: relative; left: 50%; right: 50%; margin-left: -50vw; margin-right: -50vw; overflow: hidden; background: var(--bg-gradient);">
    <footer class="footer-box">
        <div class="container">
            <div class="row">
                <!-- COMPANY -->
                <div class="col-xs-12 col-sm-6 col-md-3">
                    <div class="footer_blog">
                        <h3>COMPANY</h3>
                        <ul class="footer-links">
                            <li><a href="career.php">Careers</a></li>
                            <li><a href="policy.php">Privacy Policy</a></li>

                            <li><a href="faq.php">FAQs</a></li>
                            <li><a href="contactUS.php">Contact Us</a></li>
                        </ul>
                    </div>
                </div>

                <!-- RESOURCES -->
                <div class="col-xs-12 col-sm-6 col-md-3">
                    <div class="footer_blog">
                        <h3>RESOURCES</h3>
                        <ul class="footer-links">
                            <li><a href="application.php">Application Form</a></li>

                            <li><a href="service.php">Services</a></li>
                            <li><a href="terms-conditions.php">Terms & Conditions</a></li>

                            <li><a href="circuler.php">Circulars</a></li>
                        </ul>
                    </div>
                </div>

                <!-- SOLUTIONS -->
                <div class="col-xs-12 col-sm-6 col-md-3">
                    <div class="footer_blog">
                        <h3>SOLUTIONS</h3>
                        <ul class="footer-links">
                            <li><a href="certificationGuideline.php">Certification Guidelines</a></li>
                            <li><a href="auditorGuideline.php">Auditor Guidelines</a></li>
                            <li><a href="useOfLogo.php">Logo Usage Guidelines</a></li>
                            <li><a href="transitionGuideline.php">Transition Guidelines</a></li>

                        </ul>
                    </div>
                </div>

                <!-- FOLLOW US -->
                <div class="col-xs-12 col-sm-6 col-md-3">
                    <div class="footer_blog">
                        <h3>FOLLOW US</h3>
                        <div class="social-links">
                            <a href="#" class="social-link">
                                <i class="fab fa-linkedin-in"></i>
                                <span>LinkedIn</span>
                            </a>
                            <a href="#" class="social-link">
                                <i class="fab fa-instagram"></i>
                                <span>Instagram</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container">
                <p class="crp">© 2026 AITF. All Rights Reserved.</p>
                <p class="crp">Website developed by AITF Management Certification.</p>
            </div>
        </div>
    </footer>
</div>

<a href="#" id="scroll-to-top">
    <i class="fas fa-arrow-up"></i>
</a>

<script>
    // Back to top button functionality
    window.addEventListener('scroll', function () {
        var backToTop = document.getElementById('scroll-to-top');
        if (window.pageYOffset > 300) {
            backToTop.classList.add('show');
        } else {
            backToTop.classList.remove('show');
        }
    });

    document.getElementById('scroll-to-top').addEventListener('click', function (e) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
</script>

</body>

</html>
