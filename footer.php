<?php
// Reusable Footer Component for Varkly Website
?>
<!-- Footer Section -->
<footer class="footer-section">
    <div class="footer-container">
        <div class="footer-column">
            <h3 class="column-title">Varkly</h3>
            <p class="company-description">Leading the way in quality audits and certification assessments, ensuring excellence and compliance for businesses worldwide.</p>
            <div class="footer-logo">
                <img src="aitfImages/FINAL LOGO.png" alt="Varkly Logo" style="height: 70px !important; width: 180px !important; max-width: 180px !important; min-width: 180px !important; max-height: 70px !important;">
            </div>
        </div>
        <div class="footer-column">
            <h3 class="column-title">Services</h3>
            <ul>
                <li><a href="aboutISO.php">Quality Audits</a></li>
                <li><a href="aboutISO.php">Certification Assessments</a></li>
                <li><a href="soft-skills-training.php">Training & Workshops</a></li>
                <li><a href="aboutus.php">Compliance Consulting</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h3 class="column-title">Quick Links</h3>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="aboutus.php">About Us</a></li>
                <li><a href="aboutISO.php">ISO Standards</a></li>
                <li><a href="login.php">Client Portal</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h3 class="column-title">Contact Info</h3>
            <div class="contact-info">
                <p><strong>Email:</strong> info@varkly.com</p>
                <p><strong>Phone:</strong> +91 1234567890</p>
                <p><strong>Address:</strong> Pune, Maharashtra, India</p>
            </div>
            <div class="social-links">
                <a href="#" class="social-link">LinkedIn</a>
                <a href="#" class="social-link">Twitter</a>
                <a href="#" class="social-link">Instagram</a>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="footer-bottom-content">
            <p>&copy; 2026 Varkly Global Private Limited. All Rights Reserved.</p>
            <div class="footer-bottom-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms & Conditions</a>
            </div>
        </div>
    </div>
</footer>

<!-- Footer Styles -->
<style>
    .footer-section {
        background: #000000;
        color: #ffffff;
        padding: 80px 0 30px;
        margin-top: 80px;
        position: relative;
    }
    
    .footer-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, #333333, transparent);
    }
    
    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1.5fr;
        gap: 50px;
        align-items: start;
    }
    
    .footer-column h3 {
        color: #ffffff;
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 25px;
        font-family: 'Poppins', sans-serif;
        position: relative;
        padding-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .footer-column h3::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, #3b82f6, #60a5fa);
        border-radius: 2px;
    }
    
    .footer-column p {
        color: #b0b0b0;
        line-height: 1.8;
        margin-bottom: 20px;
        font-family: 'Inter', sans-serif;
        font-size: 15px;
    }
    
    .footer-logo {
        margin-top: 25px;
        padding: 15px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 8px;
        display: inline-block;
        transition: transform 0.3s ease, background 0.3s ease;
    }
    
    .footer-logo:hover {
        transform: translateY(-2px);
        background: rgba(255, 255, 255, 0.1);
    }
    
    .footer-column ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .footer-column ul li {
        margin-bottom: 15px;
        position: relative;
        padding-left: 20px;
    }
    
    .footer-column ul li::before {
        content: '→';
        position: absolute;
        left: 0;
        top: 0;
        color: #3b82f6;
        font-weight: bold;
        transition: transform 0.3s ease;
    }
    
    .footer-column ul li:hover::before {
        transform: translateX(3px);
    }
    
    .footer-column ul li a {
        color: #b0b0b0;
        text-decoration: none;
        transition: color 0.3s ease;
        font-family: 'Inter', sans-serif;
        font-size: 15px;
        display: block;
    }
    
    .footer-column ul li a:hover {
        color: #3b82f6;
        transform: translateX(5px);
        transition: all 0.3s ease;
    }
    
    .contact-info {
        background: rgba(255, 255, 255, 0.03);
        padding: 25px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 25px;
    }
    
    .contact-info p {
        margin-bottom: 15px;
        color: #b0b0b0;
        display: flex;
        align-items: center;
        font-size: 14px;
    }
    
    .contact-info strong {
        color: #ffffff;
        font-weight: 600;
        margin-right: 8px;
        min-width: 60px;
    }
    
    .social-links {
        display: flex;
        gap: 12px;
        margin-top: 25px;
    }
    
    .social-link {
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        color: #b0b0b0;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 18px;
        font-weight: 500;
    }
    
    .social-link:hover {
        color: #ffffff;
        background: #3b82f6;
        border-color: #3b82f6;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
    }
    
    .footer-bottom {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        margin-top: 60px;
        padding-top: 30px;
    }
    
    .footer-bottom-content {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .footer-bottom p {
        color: #808080;
        margin: 0;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
    }
    
    .footer-bottom-links {
        display: flex;
        gap: 30px;
    }
    
    .footer-bottom-links a {
        color: #808080;
        text-decoration: none;
        transition: color 0.3s ease;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        position: relative;
    }
    
    .footer-bottom-links a::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 1px;
        background: #3b82f6;
        transition: width 0.3s ease;
    }
    
    .footer-bottom-links a:hover {
        color: #3b82f6;
    }
    
    .footer-bottom-links a:hover::after {
        width: 100%;
    }
    
    @media (max-width: 992px) {
        .footer-container {
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
    }
    
    @media (max-width: 768px) {
        .footer-section {
            padding: 60px 0 20px;
        }
        
        .footer-container {
            grid-template-columns: 1fr;
            gap: 35px;
        }
        
        .footer-bottom-content {
            flex-direction: column;
            text-align: center;
        }
        
        .footer-bottom-links {
            justify-content: center;
        }
    }
</style>
