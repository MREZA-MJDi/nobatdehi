(() => {
    'use strict';

    const searchInput = document.querySelector(
        '.discover-search input[name="q"]'
    );

    const searchForm = document.querySelector(
        '.discover-search'
    );

    /*
    |--------------------------------------------------------------------------
    | Search loading state
    |--------------------------------------------------------------------------
    */

    if (searchForm) {
        searchForm.addEventListener('submit', () => {
            searchForm.classList.add('is-loading');

            const button = searchForm.querySelector('button');

            if (button) {
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');

                button.dataset.originalText =
                    button.textContent.trim();

                button.textContent = 'در حال جستجو...';
            }
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Keyboard shortcut
    |--------------------------------------------------------------------------
    |
    | Press "/" to focus search.
    |
    */

    document.addEventListener('keydown', (event) => {

        if (
            event.key !== '/' ||
            event.ctrlKey ||
            event.metaKey ||
            event.altKey
        ) {
            return;
        }

        const activeElement = document.activeElement;

        const isTyping =
            activeElement &&
            (
                activeElement.tagName === 'INPUT' ||
                activeElement.tagName === 'TEXTAREA' ||
                activeElement.tagName === 'SELECT' ||
                activeElement.isContentEditable
            );

        if (isTyping) {
            return;
        }

        if (!searchInput) {
            return;
        }

        event.preventDefault();

        searchInput.focus();
        searchInput.select();
    });


    /*
    |--------------------------------------------------------------------------
    | Scroll reveal
    |--------------------------------------------------------------------------
    */

    const revealItems = document.querySelectorAll(
        [
            '.discover-section-head',
            '.discover-service-card',
            '.discover-salon-card',
            '.discover-stylist-card',
            '.discover-featured-content',
            '.discover-final .discover-container'
        ].join(', ')
    );

    if (
        window.matchMedia(
            '(prefers-reduced-motion: reduce)'
        ).matches
    ) {
        revealItems.forEach((element) => {
            element.classList.add('is-visible');
        });

        return;
    }

    if (!('IntersectionObserver' in window)) {
        revealItems.forEach((element) => {
            element.classList.add('is-visible');
        });

        return;
    }

    const observer = new IntersectionObserver(
        (entries, observerInstance) => {

            entries.forEach((entry) => {

                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');

                observerInstance.unobserve(
                    entry.target
                );

            });

        },
        {
            threshold: 0.10,
            rootMargin: '0px 0px -50px 0px'
        }
    );

    revealItems.forEach((element, index) => {

        element.style.setProperty(
            '--reveal-delay',
            `${Math.min(index * 45, 240)}ms`
        );

        element.classList.add('reveal-item');

        observer.observe(element);

    });

})();
