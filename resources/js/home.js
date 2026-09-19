document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-home-slider]');
    if (!root) return;

    const slides = [...root.querySelectorAll('.slide')];
    const dots = [...root.querySelectorAll('#sideDots button')];

    const current = root.querySelector('#slideCurrent');
    const name = root.querySelector('#slideName');
    const location = root.querySelector('#slideLocation');
    const rating = root.querySelector('#slideRating');
    const services = root.querySelector('#slideServices');
    const link = root.querySelector('#slideLink');

    if (slides.length <= 1) {
        dots.forEach((dot) => dot.hidden = true);
        return;
    }

    let activeIndex = 0;
    let timer = null;

    const activate = (index) => {
        activeIndex = (index + slides.length) % slides.length;

        slides.forEach((slide, i) => slide.classList.toggle('is-active', i === activeIndex));
        dots.forEach((dot, i) => dot.classList.toggle('active', i === activeIndex));

        const active = slides[activeIndex];
        if (current) current.textContent = String(activeIndex + 1).padStart(2, '0');
        if (name) name.textContent = active.dataset.name || 'NOBAT';
        if (location) location.textContent = active.dataset.location || 'NOBAT';
        if (rating) rating.textContent = active.dataset.rating || '—';
        if (services) services.textContent = active.dataset.services || '0';
        if (link && active.dataset.url) link.href = active.dataset.url;
    };

    const start = () => {
        window.clearInterval(timer);
        timer = window.setInterval(() => activate(activeIndex + 1), 5600);
    };

    dots.forEach((dot) => {
        dot.addEventListener('click', () => {
            activate(Number(dot.dataset.index || 0));
            start();
        });
    });

    let touchStartX = 0;
    let touchDeltaX = 0;

    root.addEventListener('touchstart', (event) => {
        touchStartX = event.changedTouches[0]?.clientX || 0;
        touchDeltaX = 0;
    }, { passive: true });

    root.addEventListener('touchmove', (event) => {
        const x = event.changedTouches[0]?.clientX || 0;
        touchDeltaX = x - touchStartX;
    }, { passive: true });

    root.addEventListener('touchend', () => {
        if (Math.abs(touchDeltaX) < 50) return;
        activate(activeIndex + (touchDeltaX > 0 ? -1 : 1));
        start();
    }, { passive: true });

    root.addEventListener('mouseenter', () => window.clearInterval(timer));
    root.addEventListener('mouseleave', start);

    activate(0);
    start();
});
