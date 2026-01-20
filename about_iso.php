<!-- About ISO Section -->
<style>
    .about-iso-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 100px 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .section-title {
        text-align: center;
        margin-bottom: 60px;
        position: relative;
    }
    
    .section-title h2 {
        font-size: 2.8rem;
        font-weight: 700;
        color: #1a3a6a;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .section-title h2 span {
        color: #2c7be5;
        position: relative;
    }
    
    .section-title h2:after {
        content: '';
        display: block;
        width: 80px;
        height: 4px;
        background: #2c7be5;
        margin: 15px auto 0;
        border-radius: 2px;
    }
    
    .about-iso-content {
        color: #4a5568;
        line-height: 1.8;
        font-size: 1.1rem;
        max-width: 1000px;
        margin: 0 auto;
    }
    
    .about-iso-content p {
        margin-bottom: 25px;
        text-align: justify;
    }
    
    .section-subtitle {
        color: #1a3a6a;
        font-size: 1.8rem;
        font-weight: 600;
        margin: 40px 0 20px;
        position: relative;
        padding-bottom: 10px;
    }
    
    .section-subtitle:after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 60px;
        height: 3px;
        background: #2c7be5;
        border-radius: 2px;
    }
    
    .benefits-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin: 50px 0;
    }
    
    .benefit-card {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-top: 4px solid #2c7be5;
    }
    
    .benefit-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
    }
    
    .benefit-header {
        background: #f8fafd;
        padding: 20px 25px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .benefit-header h4 {
        color: #1a3a6a;
        margin: 0;
        font-size: 1.4rem;
        font-weight: 600;
    }
    
    .benefit-header p {
        color: #6c757d;
        margin: 5px 0 0;
        font-size: 0.95rem;
    }
    
    .benefit-body {
        padding: 25px;
    }
    
    .benefit-body ul {
        padding-left: 20px;
        margin: 0;
    }
    
    .benefit-body li {
        margin-bottom: 10px;
        position: relative;
        padding-left: 25px;
    }
    
    .benefit-body li:before {
        content: '✓';
        color: #2c7be5;
        font-weight: bold;
        position: absolute;
        left: 0;
    }
    
    .cta-section {
        background: linear-gradient(135deg, #1a3a6a 0%, #2c7be5 100%);
        border-radius: 12px;
        padding: 50px 30px;
        text-align: center;
        margin-top: 70px;
        color: white;
        position: relative;
        overflow: hidden;
    }
    
    .cta-section:before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        transform: translate(30%, -30%);
    }
    
    .cta-section h3 {
        font-size: 2rem;
        margin: 0 0 15px;
        color: white;
        position: relative;
        z-index: 1;
    }
    
    .cta-section p {
        font-size: 1.1rem;
        max-width: 700px;
        margin: 0 auto 25px;
        color: rgba(255, 255, 255, 0.9);
        position: relative;
        z-index: 1;
    }
    
    .btn-cta {
        display: inline-block;
        background: white;
        color: #1a3a6a !important;
        padding: 12px 35px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        position: relative;
        z-index: 1;
        overflow: hidden;
    }
    
    .btn-cta:before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 0;
        height: 100%;
        background: #f8f9fa;
        transition: all 0.3s ease;
        z-index: -1;
    }
    
    .btn-cta:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        color: #1a3a6a;
    }
    
    .btn-cta:hover:before {
        width: 100%;
    }
    
    @media (max-width: 768px) {
        .about-iso-section {
            padding: 60px 0;
        }
        
        .section-title h2 {
            font-size: 2.2rem;
        }
        
        .benefits-grid {
            grid-template-columns: 1fr;
        }
        
        .cta-section {
            padding: 40px 20px;
        }
        
        .cta-section h3 {
            font-size: 1.6rem;
        }
    }
</style>

<div class="about-iso-section">
    <div class="container">
        <div class="section-title">
            <h2>About <span>ISO</span></h2>
        </div>
        
        <div class="about-iso-content">
            <p>
                The International Organization for Standardization (ISO) is an independent, non-governmental international organization that develops and publishes international standards. Founded in 1947 and headquartered in Geneva, Switzerland, ISO brings together experts to share knowledge and develop voluntary, consensus-based, market-relevant International Standards that support innovation and provide solutions to global challenges across various industries and sectors.
            </p>
            
            <p>
                With members from 167 national standards bodies, ISO has published over 24,000 International Standards covering almost every industry, from technology and food safety to agriculture and healthcare. These standards help businesses increase productivity while minimizing errors and waste, enable products to enter new markets, and facilitate free and fair global trade.
            </p>
            
            <h3 class="section-subtitle">Our Approach to ISO Certification</h3>
            <p>
                We provide comprehensive ISO certification services designed to help organizations implement, maintain, and continuously improve their management systems in line with international standards. Our systematic approach ensures that your organization not only achieves certification but also realizes tangible business benefits through enhanced operational efficiency, improved risk management, and increased stakeholder confidence.
            </p>
            
            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-header">
                        <h4>ISO 9001:2015</h4>
                        <p>Quality Management Systems</p>
                    </div>
                    <div class="benefit-body">
                        <ul>
                            <li>Enhance customer satisfaction and loyalty</li>
                            <li>Improve process efficiency and reduce costs</li>
                            <li>Drive continual improvement and innovation</li>
                            <li>Increase operational consistency and performance</li>
                        </ul>
                    </div>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-header">
                        <h4>ISO 14001:2015</h4>
                        <p>Environmental Management Systems</p>
                    </div>
                    <div class="benefit-body">
                        <ul>
                            <li>Reduce environmental impact and waste</li>
                            <li>Ensure compliance with environmental regulations</li>
                            <li>Improve resource efficiency and reduce costs</li>
                            <li>Enhance corporate reputation and stakeholder trust</li>
                        </ul>
                    </div>
                </div>
                
                <div class="benefit-card">
                    <div class="benefit-header">
                        <h4>ISO 45001:2018</h4>
                        <p>Occupational Health & Safety</p>
                    </div>
                    <div class="benefit-body">
                        <ul>
                            <li>Reduce workplace risks and create safer working conditions</li>
                            <li>Prevent work-related injuries and ill-health</li>
                            <li>Improve compliance with legal requirements</li>
                            <li>Enhance overall business performance</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="cta-section">
                <h3>Ready to Achieve ISO Certification?</h3>
                <p>Take the first step towards operational excellence and global recognition. Our experts are here to guide you through every stage of the certification process.</p>
                <a href="contactUS.php" class="btn-cta">Get Started Today</a>
            </div>
        </div>
    </div>
</div>
