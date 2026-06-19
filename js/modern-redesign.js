/* Modern Redesign JS for AITF */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Initialize Lenis Smooth Scroll
    const lenis = new Lenis();
    function raf(time) {
        lenis.raf(time);
        requestAnimationFrame(raf);
    }
    requestAnimationFrame(raf);

    // 2. Hero Section Animations
    const heroTl = gsap.timeline({
        scrollTrigger: {
            trigger: ".modern-hero-redesign",
            start: "top top",
            end: "bottom top",
            scrub: 1.5
        }
    });

    // Prepare connection lines
    gsap.set(".connection-lines path", {
        strokeDasharray: 1000,
        strokeDashoffset: 1000
    });

    heroTl.to(".hero-node.qms", { x: 100, y: 100, scale: 1.2 }, 0)
          .to(".hero-node.ems", { x: 50, y: -50, scale: 1.2 }, 0)
          .to(".hero-node.ohsms", { x: -100, y: 50, scale: 1.2 }, 0)
          .to(".connection-lines path", { strokeDashoffset: 0, opacity: 1, duration: 1, stagger: 0.2 }, 0.5);

    // 3. ISO Sticky Storytelling
    const isoTl = gsap.timeline({
        scrollTrigger: {
            trigger: ".iso-sticky-container",
            start: "top top",
            end: "+=300%",
            pin: true,
            scrub: 1
        }
    });

    // Screen transitions
    isoTl.to(".iso-screen.qms", { opacity: 0, visibility: 'hidden', duration: 1 })
         .fromTo(".iso-screen.ems", { opacity: 0, visibility: 'hidden' }, { opacity: 1, visibility: 'visible', duration: 1 })
         .to(".iso-screen.ems", { opacity: 0, visibility: 'hidden', duration: 1, delay: 1 })
         .fromTo(".iso-screen.ohsms", { opacity: 0, visibility: 'hidden' }, { opacity: 1, visibility: 'visible', duration: 1 })
         .to(".iso-screen.ohsms", { opacity: 0, visibility: 'hidden', duration: 1, delay: 1 })
         .fromTo(".iso-screen.ims", { opacity: 0, visibility: 'hidden' }, { opacity: 1, visibility: 'visible', duration: 1 });

    // 4. Milestones Timeline
    gsap.to(".timeline-progress", {
        height: "100%",
        ease: "none",
        scrollTrigger: {
            trigger: ".milestones-timeline",
            start: "top 80%",
            end: "bottom 20%",
            scrub: 1
        }
    });

    gsap.utils.toArray(".milestone-item").forEach(item => {
        gsap.to(item, {
            opacity: 1,
            y: 0,
            duration: 1,
            scrollTrigger: {
                trigger: item,
                start: "top 85%",
                toggleActions: "play none none reverse"
            }
        });
    });

    // 5. Audit Roadmap Journey
    const roadmapTl = gsap.timeline({
        scrollTrigger: {
            trigger: ".audit-roadmap-section",
            start: "top 70%",
            end: "bottom 20%",
            scrub: 1
        }
    });

    roadmapTl.to(".roadmap-progress", { width: "100%", ease: "none" });

    gsap.utils.toArray(".roadmap-step").forEach((step, i) => {
        ScrollTrigger.create({
            trigger: step,
            start: "center 50%",
            onEnter: () => {
                step.classList.add('active', 'completed');
            },
            onLeaveBack: () => {
                step.classList.remove('active', 'completed');
            }
        });
    });

    // 6. KPI Counter
    gsap.utils.toArray(".kpi-value").forEach(val => {
        const target = parseInt(val.getAttribute('data-target'));
        gsap.to(val, {
            innerText: target,
            duration: 2,
            snap: { innerText: 1 },
            scrollTrigger: {
                trigger: val,
                start: "top 90%",
                toggleActions: "play none none reverse"
            }
        });
    });

    // 7. Trust Center Indicators
    gsap.utils.toArray(".trust-item").forEach((item, i) => {
        gsap.to(item, {
            opacity: 1,
            scale: 1,
            scrollTrigger: {
                trigger: item,
                start: "top 90%",
                toggleActions: "play none none reverse"
            }
        });
    });
});
