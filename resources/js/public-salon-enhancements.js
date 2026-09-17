/* NOBAT — Public Salon interaction layer
 * Visual/interaction refinements only. The booking API and page contract remain unchanged.
 */
(() => {
    'use strict';

    const ready = (callback) => {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
        } else {
            callback();
        }
    };

    ready(() => {
        const root = document.getElementById('salonPage');
        if (!root) return;

        const discoverUrl = new URL('/salons/discover', window.location.origin).toString();
        const brandLink = root.querySelector('.topbar .brand');
        if (brandLink) {
            brandLink.href = discoverUrl;
            brandLink.setAttribute('aria-label', 'NOBAT — کشف سالن‌ها');
        }

        /* ------------------------------------------------------------------
           Anchor navigation / active section
           ------------------------------------------------------------------ */
        const anchorLinks = Array.from(
            root.querySelectorAll('.topnav a[href^="#"], .mobile-anchor-bar a[href^="#"]')
        );
        const sections = Array.from(
            new Set(anchorLinks
                .map((link) => document.querySelector(link.getAttribute('href')))
                .filter(Boolean))
        );

        const activateSection = (id) => {
            anchorLinks.forEach((link) => {
                const active = link.getAttribute('href') === `#${id}`;
                link.classList.toggle('is-active', active);
                if (active) link.setAttribute('aria-current', 'location');
                else link.removeAttribute('aria-current');
            });
        };

        anchorLinks.forEach((link) => {
            link.addEventListener('click', (event) => {
                const target = document.querySelector(link.getAttribute('href'));
                if (!target) return;
                event.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                history.replaceState(null, '', link.getAttribute('href'));
                activateSection(target.id);
            });
        });

        if ('IntersectionObserver' in window && sections.length) {
            const sectionObserver = new IntersectionObserver((entries) => {
                entries
                    .filter((entry) => entry.isIntersecting)
                    .sort((a, b) => b.intersectionRatio - a.intersectionRatio)
                    .slice(0, 1)
                    .forEach((entry) => activateSection(entry.target.id));
            }, {
                rootMargin: '-28% 0px -58% 0px',
                threshold: [0.08, 0.2, 0.45],
            });

            sections.forEach((section) => sectionObserver.observe(section));
        }

        /* ------------------------------------------------------------------
           Gallery: mobile swipe in lightbox
           ------------------------------------------------------------------ */
        const lightbox = document.getElementById('mediaLightbox');
        const lightboxMedia = document.getElementById('lightboxMedia');
        let touchStartX = null;
        let touchStartY = null;

        lightboxMedia?.addEventListener('touchstart', (event) => {
            const touch = event.changedTouches[0];
            touchStartX = touch?.clientX ?? null;
            touchStartY = touch?.clientY ?? null;
        }, { passive: true });

        lightboxMedia?.addEventListener('touchend', (event) => {
            if (touchStartX === null || touchStartY === null) return;
            const touch = event.changedTouches[0];
            const dx = (touch?.clientX ?? touchStartX) - touchStartX;
            const dy = (touch?.clientY ?? touchStartY) - touchStartY;
            touchStartX = null;
            touchStartY = null;

            if (!lightbox?.classList.contains('open') || Math.abs(dx) < 55 || Math.abs(dx) < Math.abs(dy)) {
                return;
            }

            const next = document.getElementById('lightboxNext');
            const prev = document.getElementById('lightboxPrev');
            if (dx < 0) next?.click();
            else prev?.click();
        }, { passive: true });

        /* ------------------------------------------------------------------
           Gallery media fallback — one broken asset must not break the page.
           ------------------------------------------------------------------ */
        root.querySelectorAll('.gallery img, .services-grid img, .team-grid img').forEach((image) => {
            image.addEventListener('error', () => {
                image.classList.add('is-media-error');
                image.removeAttribute('src');
            }, { once: true });
        });

        /* ------------------------------------------------------------------
           Rich, dynamic footer
           ------------------------------------------------------------------ */
        const footer = root.querySelector(':scope > footer');
        if (!footer) return;

        const hasGallery = Boolean(root.querySelector('#gallery'));
        const hasServices = Boolean(root.querySelector('#services'));
        const hasTeam = Boolean(root.querySelector('#team'));
        const hasLocation = Boolean(root.querySelector('#location'));
        const bookingUrl = `${window.location.pathname.replace(/\/$/, '')}/booking`;
        const salonName = root.dataset.salonName || 'سالن';

        const sectionLink = (id, label) => {
            if (!root.querySelector(`#${id}`)) return '';
            return `<a href="#${id}">${label}</a>`;
        };

        footer.className = 'salon-footer';
        footer.setAttribute('aria-label', `اطلاعات و دسترسی‌های ${salonName}`);
        footer.innerHTML = `
            <div class="salon-footer-orbit" aria-hidden="true"></div>
            <div class="salon-footer-inner">
                <div class="salon-footer-brand-block">
                    <a href="${discoverUrl}" class="salon-footer-brand" aria-label="NOBAT — کشف سالن‌ها">
                        <span class="salon-footer-mark" aria-hidden="true">N</span>
                        <span>
                            <strong>NOBAT</strong>
                            <small>SALON EXPERIENCE</small>
                        </span>
                    </a>
                    <p>
                        تجربه این سالن را ببین، نمونه‌کارها را بررسی کن و وقتی آماده بودی،
                        زمان مناسب خودت را مستقیم رزرو کن.
                    </p>
                    <span class="salon-footer-signature">RM / CO · NOBAT PLATFORM</span>
                </div>

                <div class="salon-footer-column">
                    <span class="salon-footer-label">کشف</span>
                    ${sectionLink('gallery', 'نمونه‌کارها')}
                    ${sectionLink('services', 'خدمات')}
                    ${sectionLink('team', 'تیم سالن')}
                    ${sectionLink('location', 'موقعیت و اطلاعات')}
                    <a href="${discoverUrl}">سالن‌های دیگر</a>
                </div>

                <div class="salon-footer-column">
                    <span class="salon-footer-label">رزرو</span>
                    <a class="salon-footer-book" href="${bookingUrl}">رزرو نوبت</a>
                    <a href="${bookingUrl}">مشاهده زمان‌های آزاد</a>
                    <a href="${discoverUrl}#results">پیدا کردن سالن</a>
                </div>
            </div>

            <div class="salon-footer-bottom">
                <span>© ${new Date().getFullYear()} NOBAT</span>
                <span>${salonName}</span>
                <a href="${discoverUrl}">بازگشت به کشف <b aria-hidden="true">↑</b></a>
            </div>
        `;
    });
})();
