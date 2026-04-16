document.addEventListener('DOMContentLoaded', () => {
    /* ----------------------------------------------------
       1. Magnetic Spotlight Glow on Cards
    ----------------------------------------------------- */
    const updateMousePosition = (e, card) => {
        const rect = card.getBoundingClientRect();
        
        // Glow position
        const x = e.clientX - rect.left; 
        const y = e.clientY - rect.top;  
        card.style.setProperty('--mouse-x', `${x}px`);
        card.style.setProperty('--mouse-y', `${y}px`);

        // 3D Parallax Tilt calculation
        // Calculate distance from center (-0.5 to 0.5)
        const xPos = (x / rect.width) - 0.5;
        const yPos = (y / rect.height) - 0.5;
        
        // Multiplier controls how extreme the tilt is (smaller = more subtle)
        const tiltMultiplier = 15;
        const rotateY = xPos * tiltMultiplier;
        const rotateX = -(yPos * tiltMultiplier); // Negative so it tilts TOWARD the mouse

        card.style.setProperty('--rotate-x', `${rotateX}deg`);
        card.style.setProperty('--rotate-y', `${rotateY}deg`);
    };

    const resetParallax = (card) => {
        card.style.setProperty('--rotate-x', `0deg`);
        card.style.setProperty('--rotate-y', `0deg`);
    };

    // We attach listener to cards directly when they are hovered.
    const bindMagneticGlow = () => {
        const cards = document.querySelectorAll('.sit-ui-card:not(.glow-bound)');
        cards.forEach(card => {
            card.classList.add('glow-bound');
            card.addEventListener('mousemove', (e) => updateMousePosition(e, card));
            card.addEventListener('mouseleave', () => resetParallax(card));
        });
    };

    // Initial binding
    bindMagneticGlow();

    // Re-bind on click or ajax complete just in case grid updates
    const observerObj = new MutationObserver(() => bindMagneticGlow());
    const container = document.getElementById('programsContainer') || document.querySelector('.university-grid-container');
    if (container) {
        observerObj.observe(container, { childList: true, subtree: true });
    }

    /* ----------------------------------------------------
       2. Staggered Reveal on Scroll 
    ----------------------------------------------------- */
    const revealElements = document.querySelectorAll('.sit-reveal');

    if ('IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            root: null,
            threshold: 0.1, // Trigger when 10% visible
            rootMargin: "0px 0px -50px 0px" // Slightly before it fully enters bottom
        });

        revealElements.forEach(el => revealObserver.observe(el));
    } else {
        // Fallback for older browsers
        revealElements.forEach(el => el.classList.add('is-visible'));
    }
});
