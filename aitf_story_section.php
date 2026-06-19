<?php
/**
 * AITF Business-Transformation Scroll-Storytelling Section
 * Integrated with PUBLIC Site Layout (headerSource & footerSource)
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AITF - Business Transformation</title>
    
    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-blue: #2563eb;
            --primary-green: #16a34a;
            --primary-orange: #ea580c;
            --bg-dark: #0f172a;
            --text-light: #f8fafc;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        /* RESET & BASE */
        html, body {
            margin: 0;
            padding: 0;
            background-color: var(--bg-dark);
            color: var(--text-light);
            font-family: 'Inter', sans-serif;
            min-height: 100%;
        }

        body {
            overflow-x: hidden;
            position: relative;
        }

        /* Fix for White Space after Footer */
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%);
            z-index: -2;
        }

        .story-mesh-global {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-image: linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);
            background-size: 50px 50px;
            opacity: 0.3;
            z-index: -1;
            pointer-events: none;
        }

        /* Adjust for Fixed Navbar */
        .story-main-container {
            margin-top: 70px;
            position: relative;
            z-index: 1;
        }

        .story-section {
            min-height: 100vh;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 100px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .story-content {
            max-width: 1200px;
            width: 90%;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .story-text h2 {
            font-size: clamp(2.5rem, 5vw, 3.8rem);
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 24px;
            background: linear-gradient(to right, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .story-text p {
            font-size: 1.3rem;
            color: #94a3b8;
            line-height: 1.6;
            font-weight: 400;
        }

        .story-visual {
            height: 500px;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 32px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        /* KPI Cards */
        .kpi-wrapper { width: 80%; }
        .kpi-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .kpi-label { font-size: 0.9rem; color: #60a5fa; text-transform: uppercase; letter-spacing: 2px; font-weight: 600; }
        .kpi-val { font-size: 3.5rem; font-weight: 800; display: block; margin-top: 10px; font-family: 'Montserrat'; color: #fff; }

        /* IMS Merge UI */
        .panel-ims {
            position: absolute;
            width: 150px;
            height: 200px;
            border: 2px solid;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.4rem;
            background: rgba(15, 23, 42, 0.95);
            box-shadow: 0 20px 40px rgba(0,0,0,0.6);
            backdrop-filter: blur(10px);
        }

        .q-panel { border-color: var(--primary-blue); color: var(--primary-blue); }
        .e-panel { border-color: var(--primary-green); color: var(--primary-green); }
        .o-panel { border-color: var(--primary-orange); color: var(--primary-orange); }

        /* Success Reveal */
        .success-badge {
            width: 180px; height: 180px;
            background: linear-gradient(135deg, #ffd700, #b8860b);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 6rem;
            color: #fff;
            box-shadow: 0 0 80px rgba(255, 215, 0, 0.5);
            margin: 0 auto 50px;
        }

        .cta-group {
            margin-top: 50px;
            display: flex;
            gap: 25px;
            justify-content: center;
        }

        .cta-btn {
            padding: 20px 45px;
            border-radius: 14px;
            font-weight: 800;
            text-decoration: none;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-size: 0.95rem;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .btn-blue { background: var(--primary-blue); color: #fff; }
        .btn-outline { border: 2px solid var(--glass-border); color: #fff; }
        .cta-btn:hover { transform: translateY(-8px) scale(1.05); box-shadow: 0 20px 40px rgba(0,0,0,0.5); color: #fff; text-decoration: none; }

        /* Scroll Progress */
        .page-progress {
            position: fixed;
            top: 70px; left: 0;
            width: 0%; height: 6px;
            background: linear-gradient(to right, #3b82f6, #60a5fa);
            z-index: 2000;
            box-shadow: 0 2px 10px rgba(59, 130, 246, 0.4);
        }

        @media (max-width: 968px) {
            .story-content { grid-template-columns: 1fr; text-align: center; }
            .story-visual { height: 400px; }
            .story-text h2 { font-size: 2.8rem; }
        }
    </style>
</head>
<body>

    <!-- PUBLIC HEADER -->
    <?php include 'headerSource.php'; ?>

    <div class="page-progress"></div>
    <div class="story-bg"><div class="story-mesh"></div></div>

    <div class="story-main-container">
        
        <!-- SECTION 1: HERO -->
        <section class="story-section" id="story-hero">
            <div class="story-content" style="grid-template-columns: 1fr;">
                <div class="story-text" style="text-align: center;">
                    <h2>One Organization.<br>Three Systems.<br>One Goal: Excellence.</h2>
                    <p>Improve Quality, Protect the Environment, and Ensure Workplace Safety through internationally recognized management systems.</p>
                </div>
            </div>
        </section>

        <!-- SECTION 2: QMS -->
        <section class="story-section" id="story-qms">
            <div class="story-content">
                <div class="story-text">
                    <h2>Quality Management<br>(ISO 9001)</h2>
                    <p>Drive customer satisfaction, process efficiency, and reduced delays. Build quality into every process through measurable business improvements and data-driven dashboards.</p>
                </div>
                <div class="story-visual">
                    <div class="kpi-wrapper">
                        <div class="kpi-card">
                            <span class="kpi-label">Customer Satisfaction</span>
                            <span class="kpi-val" id="qms-nps">65%</span>
                        </div>
                        <div class="kpi-card" style="margin-bottom: 0;">
                            <span class="kpi-label">Operational Efficiency</span>
                            <span class="kpi-val" id="qms-eff">72%</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 3: EMS -->
        <section class="story-section" id="story-ems">
            <div class="story-content">
                <div class="story-text">
                    <h2>Environmental Excellence<br>(ISO 14001)</h2>
                    <p>Focus on energy optimization, waste reduction, and resource efficiency. Growth through environmental stewardship and measurable performance metrics.</p>
                </div>
                <div class="story-visual">
                    <div class="kpi-wrapper">
                        <div class="kpi-card">
                            <span class="kpi-label">Energy Optimization</span>
                            <span class="kpi-val" id="ems-eng">50%</span>
                        </div>
                        <div class="kpi-card" style="margin-bottom: 0;">
                            <span class="kpi-label">Waste Reduction</span>
                            <span class="kpi-val" id="ems-wst">45%</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 4: OHSMS -->
        <section class="story-section" id="story-ohs">
            <div class="story-content">
                <div class="story-text">
                    <h2>Predictive Safety<br>(ISO 45001)</h2>
                    <p>Focus on risk reduction and workplace safety metrics. Safety is the foundation of sustainable success through effective risk controls and predictive monitoring.</p>
                </div>
                <div class="story-visual">
                    <div class="kpi-wrapper">
                        <div class="kpi-card">
                            <span class="kpi-label">Risk Control Index</span>
                            <span class="kpi-val" id="ohs-risk">Medium</span>
                        </div>
                        <div class="kpi-card" style="margin-bottom: 0;">
                            <span class="kpi-label">Workplace Safety</span>
                            <span class="kpi-val" id="ohs-saf">78%</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 5: IMS -->
        <section class="story-section" id="story-ims">
            <div class="story-content">
                <div class="story-text">
                    <h2>Integrated Management<br>(IMS)</h2>
                    <p>Combine Quality, Environment, and Safety into one unified management system dashboard. Eliminate silos, reduce duplication, and multiply efficiency.</p>
                </div>
                <div class="story-visual">
                    <div class="panel-ims q-panel" id="ims-q">QMS</div>
                    <div class="panel-ims e-panel" id="ims-e">EMS</div>
                    <div class="panel-ims o-panel" id="ims-o">OHSMS</div>
                    <div id="ims-core" style="width: 140px; height: 140px; border-radius: 50%; background: #fff; opacity: 0; filter: blur(40px);"></div>
                </div>
            </div>
        </section>

        <!-- SECTION 6: SUCCESS -->
        <section class="story-section" id="story-success">
            <div class="story-content" style="grid-template-columns: 1fr; text-align: center;">
                <div class="story-text">
                    <div class="success-badge" id="success-seal">✓</div>
                    <h2>Trust Verified. Success Certified.</h2>
                    <p>Join the elite group of organizations that lead with compliance, credibility, and operational excellence through AITF certification.</p>
                    <div class="cta-group">
                        <a href="contactUS.php" class="cta-btn btn-blue">Start Transformation</a>
                        <a href="aboutISO.php" class="cta-btn btn-outline">Explore Standards</a>
                    </div>
                </div>
            </div>
        </section>

    </div>

    <!-- PUBLIC FOOTER -->
    <?php include 'footerSource.php'; ?>

    <!-- GSAP Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>

    <script>
        window.addEventListener('load', function() {
            if (typeof gsap === 'undefined') return;
            gsap.registerPlugin(ScrollTrigger);

            // Page Progress
            gsap.to('.page-progress', {
                width: '100%',
                ease: 'none',
                scrollTrigger: {
                    trigger: '.story-main-container',
                    start: 'top top',
                    end: 'bottom bottom',
                    scrub: 0.3
                }
            });

            // QMS KPI Scrubbing
            gsap.to('#qms-nps', { innerText: '98%', snap: { innerText: 1 }, scrollTrigger: { trigger: '#story-qms', start: 'top 70%', end: 'bottom 30%', scrub: 1 } });
            gsap.to('#qms-eff', { innerText: '95%', snap: { innerText: 1 }, scrollTrigger: { trigger: '#story-qms', start: 'top 70%', end: 'bottom 30%', scrub: 1 } });

            // EMS KPI Scrubbing
            gsap.to('#ems-eng', { innerText: '90%', snap: { innerText: 1 }, scrollTrigger: { trigger: '#story-ems', start: 'top 70%', end: 'bottom 30%', scrub: 1 } });
            gsap.to('#ems-wst', { innerText: '88%', snap: { innerText: 1 }, scrollTrigger: { trigger: '#story-ems', start: 'top 70%', end: 'bottom 30%', scrub: 1 } });

            // OHS KPI Scrubbing
            gsap.to('#ohs-risk', { 
                scrollTrigger: { 
                    trigger: '#story-ohs', start: 'top 70%', end: 'bottom 30%', scrub: 1,
                    onUpdate: (self) => {
                        const val = Math.round(self.progress * 100);
                        document.getElementById('ohs-risk').innerText = val > 80 ? 'Optimal' : (val > 40 ? 'Safe' : 'Medium');
                    }
                } 
            });
            gsap.to('#ohs-saf', { innerText: '100%', snap: { innerText: 1 }, scrollTrigger: { trigger: '#story-ohs', start: 'top 70%', end: 'bottom 30%', scrub: 1 } });

            // IMS Merge Animation
            const imsTl = gsap.timeline({
                scrollTrigger: {
                    trigger: '#story-ims',
                    start: 'top 60%',
                    end: 'bottom 40%',
                    scrub: 1
                }
            });

            imsTl.from('#ims-q', { x: -400, y: -300, opacity: 0, rotation: -90 })
                 .from('#ims-e', { x: 400, y: -300, opacity: 0, rotation: 90 }, 0)
                 .from('#ims-o', { y: 400, opacity: 0, rotation: 180 }, 0)
                 .to('#ims-core', { opacity: 1, filter: 'blur(0px)', scale: 2, duration: 0.5 });

            // Final Reveal
            gsap.from('#success-seal', {
                scale: 0,
                rotation: 1080,
                opacity: 0,
                duration: 1.5,
                ease: 'elastic.out(1, 0.5)',
                scrollTrigger: {
                    trigger: '#story-success',
                    start: 'top 60%',
                    toggleActions: 'play none none reverse'
                }
            });
        });
    </script>
</body>
</html>
