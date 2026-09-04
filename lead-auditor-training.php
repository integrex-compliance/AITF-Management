<!DOCTYPE html>
<html lang="en">
    <!-- Basic -->
    <style> 
        /* bluring the image */
        .gfg { 
            /*filter: blur(5px);*/ 
            width: 100%; 
        } 

        /* designing title and tag line */
        .logo { 
            text-align:center; 
            position: absolute; 
            font-size: 18px; 
            top: 50px; 
            left: 27%; 
            color: white; 
        } 
        
        h1:hover { 
            background: -webkit-linear-gradient( green, lime); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
        } 
    </style> 
    <head>
        <?php include __DIR__ . '/headsource.php'; ?>
    </head>
    <body>
        <?php include './headerSource.php'; ?>
        <div class="gfg">
            <img src="images/iso.jpg" alt="Lead Auditor Training">
        </div>
        <div class="logo">
            <h1>Lead Auditor Training</h1>
            <p>Professional Lead Auditor Certification Programs</p>
        </div>
        <div class="container mt-5">
            <div class="row">
                <div class="col-md-12">
                    <h2>Lead Auditor Training Programs</h2>
                    <p>Our comprehensive Lead Auditor Training programs are designed to equip professionals with the knowledge and skills required to conduct effective audits across various management systems.</p>
                    
                    <h3>Available Lead Auditor Courses:</h3>
                    <ul>
                        <li><a href="qms-iso-9001-lead.php">QMS ISO 9001:2015 Lead Auditor</a></li>
                        <li><a href="ems-iso-14001-lead.php">EMS ISO 14001:2015 Lead Auditor</a></li>
                        <li><a href="ohsms-iso-45001-lead.php">OHSMS ISO 45001:2018 Lead Auditor</a></li>
                        <li><a href="isms-iso-27001-2022-lead.php">ISMS ISO 27001:2022 Lead Auditor</a></li>
                        <li><a href="integrated-systems-lead.php">Integrated Systems Lead Auditor</a></li>
                    </ul>
                    
                    <h3>Course Benefits:</h3>
                    <ul>
                        <li>Internationally recognized certification</li>
                        <li>Expert trainers with extensive industry experience</li>
                        <li>Practical audit exercises and case studies</li>
                        <li>Comprehensive course materials</li>
                        <li>Post-training support and guidance</li>
                    </ul>
                </div>
            </div>
        </div>
        <?php include './footerSource.php'; ?>
    </body>
</html>
