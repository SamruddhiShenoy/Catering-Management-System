// Advanced ScrollReveal & GSAP Animations
document.addEventListener('DOMContentLoaded', () => {
    
    // VanillaTilt for 3D Cards
    if (typeof VanillaTilt !== 'undefined') {
        VanillaTilt.init(document.querySelectorAll(".package-card"), {
            max: 10,
            speed: 400,
            glare: true,
            "max-glare": 0.2,
            scale: 1.02
        });
        VanillaTilt.init(document.querySelectorAll(".stat-card"), {
            max: 5,
            speed: 300,
            scale: 1.05
        });
    }

    // ScrollReveal for fade-ins
    if (typeof ScrollReveal !== 'undefined') {
        const sr = ScrollReveal({
            distance: '40px',
            duration: 1000,
            easing: 'cubic-bezier(0.25, 0.1, 0.25, 1)',
            reset: false
        });

        // Add reveal class to elements dynamically if missing, or select them
        sr.reveal('.hero-title', { origin: 'bottom', delay: 200 });
        sr.reveal('.hero-subtitle', { origin: 'bottom', delay: 400 });
        sr.reveal('.hero-section .btn-gold', { origin: 'bottom', delay: 600 });
        
        sr.reveal('.section-title', { origin: 'bottom', delay: 200 });
        sr.reveal('.package-card', { origin: 'bottom', interval: 200, delay: 300 });
        
        // Dashboard cards
        sr.reveal('.stat-card', { origin: 'bottom', interval: 100 });
        
        // Generic reveal class
        sr.reveal('.reveal', { origin: 'bottom', interval: 150 });
    }

    // GSAP Page Load Transition (if needed for smooth SPA feel)
    if (typeof gsap !== 'undefined') {
        gsap.from('body', { opacity: 0, duration: 0.8, ease: "power2.out" });
    }
});
