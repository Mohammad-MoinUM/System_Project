import './bootstrap';

// ── Scroll-reveal animation observer ────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const animatedElements = document.querySelectorAll(
        '.scroll-fade-up, .scroll-fade-left, .scroll-fade-right, .scroll-zoom-in'
    );

    if (!animatedElements.length) return;

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.1, rootMargin: '0px 0px -40px 0px' }
    );

    animatedElements.forEach((el) => observer.observe(el));

    // ── Counter animation ──────────────────────────────
    document.querySelectorAll('[data-count-to]').forEach((el) => {
        const countObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        animateCounter(entry.target);
                        countObserver.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.3 }
        );
        countObserver.observe(el);
    });

    function animateCounter(el) {
        const target = parseFloat(el.dataset.countTo);
        const suffix = el.dataset.countSuffix || '';
        const prefix = el.dataset.countPrefix || '';
        const decimals = el.dataset.countDecimals ? parseInt(el.dataset.countDecimals) : 0;
        const duration = 1200;
        const start = performance.now();

        function update(now) {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            // ease-out cubic
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = eased * target;
            el.textContent = prefix + current.toFixed(decimals) + suffix;
            if (progress < 1) requestAnimationFrame(update);
        }
        requestAnimationFrame(update);
    }

    // ── Progress bar animation ─────────────────────────
    document.querySelectorAll('progress.animate-progress').forEach((el) => {
        const targetValue = el.getAttribute('value') || 0;
        el.setAttribute('value', 0);
        const progObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => el.setAttribute('value', targetValue), 100);
                        progObserver.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.3 }
        );
        progObserver.observe(el);
    });
});
