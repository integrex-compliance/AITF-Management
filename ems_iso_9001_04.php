<!DOCTYPE html>
<html lang="en">
<head>
    <?php include './headSource.php'; ?>
    <!-- Add Particle.js Library -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    
    <style>
        /* Animated Gradient Text */
        @keyframes gradientText {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        :root {
            --primary-color: #1b5e20;
            --secondary-color: #2e7d32;
            --accent-color: #ffab00;
            --light-bg: #f1f8e9;
            --text-color: #1b5e20;
            --gradient-1: #1b5e20;
            --gradient-2: #2e7d32;
            --gradient-3: #43a047;
            --transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        /* Modern Card with 3D Tilt Effect */
        .modern-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1), 0 5px 15px rgba(0, 0, 0, 0.07);
            padding: 40px;
            margin-bottom: 40px;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            transform-style: preserve-3d;
            perspective: 1000px;
            will-change: transform, box-shadow;
            transform: translateZ(0) scale(1);
        }
        
        .modern-card:hover {
            transform: translateY(-10px) scale(1.02) rotateX(2deg) rotateY(2deg);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15), 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .modern-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--gradient-1), var(--gradient-2), var(--gradient-3));
            background-size: 400% 400%;
            animation: gradientBG 8s ease infinite;
        }

        /* Hero Section with Particles */
        .hero-section {
            position: relative;
            height: 100vh;
            min-height: 700px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            background: linear-gradient(-45deg, #1b5e20, #2e7d32, #43a047, #66bb6a);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            z-index: 1;
        }
        
        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 1;
            opacity: 0.6;
        }
        
        .hero-content {
            max-width: 900px;
            padding: 0 30px;
            z-index: 2;
            animation: fadeInUp 1s ease-out;
        }

        .hero-content h1 {
            font-size: 5rem;
            margin-bottom: 30px;
            font-weight: 900;
            letter-spacing: -1px;
            text-shadow: 2px 2px 15px rgba(0,0,0,0.2);
            background: linear-gradient(135deg, #ffffff, #e8f5e9, #c8e6c9, #a5d6a7);
            background-size: 300% 300%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
            display: inline-block;
            animation: gradientText 8s ease infinite;
            text-transform: uppercase;
            line-height: 1.1;
        }
        
        .hero-content h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, #ffd600, #ffab00);
            border-radius: 2px;
        }
        
        .hero-content p.lead {
            font-size: 1.8rem;
            margin: 0 auto 30px;
            max-width: 800px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
            opacity: 0.9;
            line-height: 1.6;
        }

        /* Content Sections */
        .section-title {
            position: relative;
            margin-bottom: 50px;
            text-align: center;
        }

        .section-title h2 {
            display: inline-block;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            position: relative;
            padding-bottom: 15px;
        }

        .section-title h2:after {
            content: '';
            position: absolute;
            width: 70px;
            height: 3px;
            background: var(--secondary-color);
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }

        /* Modern List Styles with Icons */
        .modern-list {
            list-style: none;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }

        .modern-list li {
            padding: 18px 25px 18px 60px;
            position: relative;
            margin-bottom: 15px;
            color: var(--text-color);
            background: rgba(255, 255, 255, 0.7);
            border-radius: 12px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-left: 4px solid transparent;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(5px);
            transform: translateZ(0);
        }
        
        .modern-list li:hover {
            transform: translateX(5px);
            background: rgba(46, 125, 50, 0.1);
            border-left: 3px solid var(--accent-color);
        }

        .modern-list li:before {
            content: '\f00c';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent-color);
            background: rgba(255, 171, 0, 0.1);
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: var(--transition);
        }
        
        .modern-list li:hover:before {
            background: var(--accent-color);
            color: white;
            transform: translateY(-50%) scale(1.1);
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .hero-content h1 {
                font-size: 3.5rem;
            }
            
            .section-title h2 {
                font-size: 2.2rem;
            }
            
            .modern-list {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .hero-section {
                height: 70vh;
                min-height: 500px;
            }
            
            .hero-content h1 {
                font-size: 2.8rem;
            }
            
            .hero-content p.lead {
                font-size: 1.3rem;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
            
            .modern-card {
                padding: 25px 20px;
            }
        }
        
        @media (max-width: 576px) {
            .hero-section {
                height: 80vh;
                min-height: 500px;
            }
            
            .hero-content h1 {
                font-size: 2.2rem;
            }
            
            .hero-content p.lead {
                font-size: 1.1rem;
            }
        }
        
        /* Animations */
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
        
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(2deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        
        /* Glowing Effect */
        .glow {
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }
        
        /* Pulse Animation */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .pulse {
            animation: pulse 4s infinite;
        }
        
        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>

    <body id="home" data-spy="scroll" data-target="#navbar-wd" data-offset="98">
    <?php include './headerSource.php'; ?>
    
    <!-- Hero Section with Particles -->
    <section class="hero-section">
        <div id="particles-js"></div>
        <div class="hero-content">
            <h1 class="glow">Environmental<br>Management System</h1>
            <p class="lead text-white pulse">Transform Your Environmental Management with ISO 14001:2015 Training</p>
            <div class="mt-5">
                <button class="btn btn-light btn-lg mr-3" style="background: rgba(255,255,255,0.9); border: none; padding: 12px 35px; border-radius: 50px; font-weight: 600; letter-spacing: 1px; transition: all 0.3s ease; box-shadow: 0 5px 20px rgba(0,0,0,0.2);">
                    Enroll Now
                </button>
                <button class="btn btn-outline-light btn-lg" style="border: 2px solid rgba(255,255,255,0.8); padding: 10px 30px; border-radius: 50px; font-weight: 500; letter-spacing: 1px; transition: all 0.3s ease;">
                    Learn More
                </button>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container py-5">
        <!-- Course Overview -->
        <div class="modern-card mb-5">
            <div class="section-title">
                <h2>Environmental Management System (ISO 14001:2015)</h2>
            </div>
    <!-- end section -->
            <div class="row">
                <div class="col-md-4">
                    <div class="modern-card h-100">
                        <h4><i class="fas fa-chalkboard-teacher text-success mr-2"></i> Training</h4>
                        <p class="lead">Internal Auditor Course: ISO 14001</p>
                        
                        <h4 class="mt-4"><i class="far fa-clock text-success mr-2"></i> Duration</h4>
                        <p class="lead">2 Days Intensive Training</p>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="modern-card h-100">
                        <h4><i class="fas fa-certificate text-success mr-2"></i> Participants Will Receive</h4>
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <ul class="modern-list">
                                    <li>EMS Slides including Exercises</li>
                                    <li>ISO 14001 Standard</li>
                                    <li>Sample EMS Manual</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="modern-list">
                                    <li>Case Studies & Workbook</li>
                                    <li>Practical Audit Templates</li>
                                    <li>Internal Auditor Certificate</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Learning Outcomes -->
            <div class="modern-card mt-4">
                <div class="section-title">
                    <h2>Learning Outcomes</h2>
                </div>
                <p class="lead mb-4">By the end of this course, you will be able to:</p>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="modern-list">
                            <li>Develop an environmental internal audit system</li>
                            <li>Perform environmental audits to ISO 14001 requirements</li>
                            <li>Maximize value from internal audit processes</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="modern-list">
                            <li>Drive continual improvement through audits</li>
                            <li>Understand environmental management principles</li>
                            <li>Implement effective audit reporting</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Who Should Attend -->
            <div class="modern-card mt-4">
                <div class="row">
                    <div class="col-md-6">
                        <h4><i class="fas fa-users text-success mr-2"></i> Who Should Attend?</h4>
                        <p>This comprehensive two-day course is designed for professionals who want to develop or enhance their environmental auditing skills, including:</p>
                        <ul class="modern-list">
                            <li>Environmental Managers & Officers</li>
                            <li>Quality & Compliance Managers</li>
                            <li>Departmental Managers</li>
                            <li>Internal Auditors</li>
                            <li>Management Representatives</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h4><i class="fas fa-chalkboard-teacher text-success mr-2"></i> Course Facilitators</h4>
                        <p>Our expert facilitators are highly qualified and experienced in both training and assessment of Environmental Management Systems. They bring practical industry experience and engaging teaching methods to deliver an effective learning experience.</p>
                        <div class="mt-3 p-3" style="background: rgba(46, 125, 50, 0.1); border-radius: 10px;">
                            <i class="fas fa-quote-left text-success mr-2"></i>
                            <span class="font-italic">Selected for their ability to make environmental management training both practical and enjoyable.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course Structure -->
            <div class="modern-card mt-4">
                <div class="section-title">
                    <h2>Course Structure & Content</h2>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h4><i class="far fa-calendar-alt text-success mr-2"></i> Schedule</h4>
                        <p><strong>Duration:</strong> 2 Days (9:00 AM - 6:00 PM)</p>
                        <p><strong>Class Size:</strong> Limited to 10 delegates</p>
                        <p><strong>Format:</strong> Interactive classroom training with hands-on exercises</p>
                        
                        <h4 class="mt-4"><i class="fas fa-user-check text-success mr-2"></i> Eligibility</h4>
                        <p>Delegates should have basic knowledge of Environmental Management Systems and ISO 14001 standards.</p>
                    </div>
                    <div class="col-md-6">
                        <h4><i class="fas fa-book-open text-success mr-2"></i> Course Elements</h4>
                        <ul class="modern-list">
                            <li>Basic concepts and definitions</li>
                            <li>ISO 14001:2015 requirements</li>
                            <li>Audit planning and preparation</li>
                            <li>Performing environmental audits</li>
                            <li>Audit reporting and follow-up</li>
                            <li>Case studies and practical exercises</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- How to Apply -->
            <div class="modern-card mt-4 text-center">
                <h3 class="mb-4"><i class="fas fa-paper-plane text-success mr-2"></i> How to Apply</h3>
                <p class="lead">Ready to enhance your environmental management skills?</p>
                <p>Download the Participant Nomination Form and email it to <a href="mailto:training1@aitf.co.in" class="text-success font-weight-bold">training1@aitf.co.in</a></p>
                
                <div class="mt-4 p-4" style="background: rgba(46, 125, 50, 0.1); border-radius: 10px; display: inline-block;">
                    <p class="mb-2"><i class="fas fa-info-circle text-success mr-2"></i> <strong>Important Notes:</strong></p>
                    <p class="mb-1">• Advance payment required to reserve your seat</p>
                    <p class="mb-1">• 48 hours cancellation notice required</p>
                    <p class="mb-0">• Nomination transfers are acceptable</p>
                </div>
                
                <div class="mt-4">
                    <button class="btn btn-success btn-lg px-5 py-3" style="border-radius: 50px; font-weight: 600; letter-spacing: 1px; box-shadow: 0 5px 20px rgba(27, 94, 32, 0.3);">
                        <i class="fas fa-download mr-2"></i> Download Application Form
                    </button>
                </div>
            </div>

        </div>
    </div>

    <?php include './footerSource.php'; ?>
    
    <script>
    // Initialize particles.js
    document.addEventListener('DOMContentLoaded', function() {
        particlesJS('particles-js', {
            particles: {
                number: { value: 80, density: { enable: true, value_area: 800 } },
                color: { value: '#ffffff' },
                shape: { type: 'circle' },
                opacity: { value: 0.5, random: true },
                size: { value: 3, random: true },
                line_linked: { enable: true, distance: 150, color: '#ffffff', opacity: 0.2, width: 1 },
                move: { enable: true, speed: 2, direction: 'none', random: true, straight: false, out_mode: 'out' }
            },
            interactivity: {
                detect_on: 'canvas',
                events: {
                    onhover: { enable: true, mode: 'grab' },
                    onclick: { enable: true, mode: 'push' },
                    resize: true
                }
            },
            retina_detect: true
        });
        
        // Add 3D tilt effect to cards
        const cards = document.querySelectorAll('.modern-card');
        cards.forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                const angleX = (y - centerY) / 20;
                const angleY = (centerX - x) / 20;
                
                card.style.transform = `perspective(1000px) rotateX(${angleX}deg) rotateY(${angleY}deg) translateY(-10px) scale(1.02)`;
                card.style.boxShadow = `${-angleY}px ${angleX}px 30px rgba(0,0,0,0.2)`;
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(0) scale(1)';
                card.style.boxShadow = '0 25px 60px rgba(0, 0, 0, 0.15), 0 15px 30px rgba(0, 0, 0, 0.1)';
            });
        });
    });
    </script>
</body>
</html>