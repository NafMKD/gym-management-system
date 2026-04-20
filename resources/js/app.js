import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const revealItems = document.querySelectorAll('[data-reveal]');

if (revealItems.length > 0) {
    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, {
        threshold: 0.18,
    });

    revealItems.forEach((item) => revealObserver.observe(item));
}

const counterItems = document.querySelectorAll('[data-counter]');

if (counterItems.length > 0) {
    const counterObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            const target = entry.target;
            const endValue = Number(target.getAttribute('data-counter')) || 0;
            const duration = 1400;
            const startTime = performance.now();

            const tick = (now) => {
                const progress = Math.min((now - startTime) / duration, 1);
                target.textContent = Math.floor(progress * endValue).toString();

                if (progress < 1) {
                    requestAnimationFrame(tick);
                } else {
                    target.textContent = endValue.toString();
                }
            };

            requestAnimationFrame(tick);
            observer.unobserve(target);
        });
    }, {
        threshold: 0.6,
    });

    counterItems.forEach((item) => counterObserver.observe(item));
}
