(() => {
    document.querySelectorAll('[data-hero-carousel]').forEach(carousel => {
        const slides = Array.from(carousel.querySelectorAll('.achilles-hero-carousel__slide'));
        const controls = carousel.querySelector('.achilles-hero-carousel__controls');
        carousel.querySelectorAll('img[data-fallback]').forEach(img => {
            const fallback = () => {
                if (img.dataset.fallback) {
                    const url = img.dataset.fallback;
                    delete img.dataset.fallback;
                    img.src = url;
                }
            };
            img.addEventListener('error', fallback);
            if (img.complete && !img.naturalWidth) fallback();
        });
        if (!controls || slides.length < 2) return;
        controls.hidden = false;
        const indicators = Array.from(controls.querySelectorAll('[data-slide]'));
        const pause = controls.querySelector('[data-pause]');
        const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
        let current = 0;
        let timer;
        let hovered = false;
        let focused = false;
        let paused = false;

        const stop = () => window.clearTimeout(timer);
        const schedule = () => {
            stop();
            if (!hovered && !focused && !paused && !document.hidden && !motion.matches) {
                timer = window.setTimeout(() => show(current + 1), 5000);
            }
        };
        const show = index => {
            current = (index + slides.length) % slides.length;
            slides.forEach((slide, i) => {
                const active = i === current;
                slide.classList.toggle('is-active', active);
                slide.setAttribute('aria-hidden', String(!active));
                slide.tabIndex = active ? 0 : -1;
                slide.inert = !active;
                if (active) slide.querySelector('img').loading = 'eager';
                indicators[i].setAttribute('aria-current', String(active));
            });
            schedule();
        };
        controls.querySelector('[data-previous]').addEventListener('click', () => show(current - 1));
        controls.querySelector('[data-next]').addEventListener('click', () => show(current + 1));
        indicators.forEach((button, index) => button.addEventListener('click', () => show(index)));
        pause.addEventListener('click', () => {
            paused = !paused;
            pause.setAttribute('aria-pressed', String(paused));
            pause.setAttribute('aria-label', paused ? 'Resume slideshow' : 'Pause slideshow');
            pause.textContent = paused ? '\u25b6' : '\u275a\u275a';
            schedule();
        });
        carousel.addEventListener('mouseenter', () => { hovered = true; stop(); });
        carousel.addEventListener('mouseleave', () => { hovered = false; schedule(); });
        carousel.addEventListener('focusin', () => { focused = true; stop(); });
        carousel.addEventListener('focusout', event => {
            focused = carousel.contains(event.relatedTarget);
            schedule();
        });
        document.addEventListener('visibilitychange', schedule);
        motion.addEventListener('change', schedule);
        schedule();
    });
})();
