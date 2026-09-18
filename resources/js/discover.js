(() => {
    'use strict';

    const page = document.querySelector('.discover-page');

    if (!page) return;

    const setLoading = (loading) => {
        page.classList.toggle('discover-results-loading', loading);
    };

    const notifyError = (message) => {
        if (typeof window.toast === 'function') {
            window.toast(message, 'error');
        } else {
            window.alert(message);
        }
    };

    const requestDiscover = async (
        url,
        {
            push = true,
            scroll = true,
            showOverlay = false,
        } = {}
    ) => {
        const nextUrl = new URL(url, window.location.origin);
        nextUrl.hash = 'results';

        setLoading(true);

        if (showOverlay) {
            openSearchModal({ loading: true });
        }

        try {
            const response = await fetch(nextUrl.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'text/html',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('DISCOVER_REQUEST_FAILED');
            }

            const html = await response.text();
            const parsed = new DOMParser().parseFromString(html, 'text/html');
            const freshDynamic = parsed.querySelector('#discoverDynamicContent');
            const currentDynamic = page.querySelector('#discoverDynamicContent');

            if (!freshDynamic || !currentDynamic) {
                throw new Error('DISCOVER_DYNAMIC_CONTENT_NOT_FOUND');
            }

            currentDynamic.replaceWith(freshDynamic);

            if (push) {
                window.history.pushState({}, '', nextUrl.toString());
            }

            syncHeroSearchInputs(nextUrl);
            bindResultInteractions();
            observeReveals();

            if (showOverlay) {
                const freshResults = freshDynamic.querySelector('#results');

                if (freshResults) {
                    renderSearchModal(freshResults);
                }
            }

            if (scroll && !showOverlay) {
                requestAnimationFrame(() => {
                    page.querySelector('#results')?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                    });
                });
            }

            return true;
        } catch (error) {
            console.error(error);

            if (showOverlay) {
                closeSearchModal();
            }

            notifyError('نتایج دریافت نشد. اتصال را بررسی کن و دوباره تلاش کن.');
            return false;
        } finally {
            setLoading(false);
        }
    };

    const formToUrl = (form) => {
        const url = new URL(form.action || window.location.href, window.location.origin);
        const data = new FormData(form);

        url.search = '';

        for (const [key, value] of data.entries()) {
            const stringValue = String(value).trim();

            if (stringValue !== '') {
                url.searchParams.append(key, stringValue);
            }
        }

        return url;
    };

    const submitDiscoverForm = (
        form,
        options = {}
    ) => {
        if (!form) return Promise.resolve(false);

        return requestDiscover(
            formToUrl(form),
            options
        );
    };

    /* -----------------------------------------------------------------------
       Hero search result modal
       ----------------------------------------------------------------------- */

    const searchModal = page.querySelector('#discoverSearchModal');
    const searchModalBody = page.querySelector('#discoverSearchResults');
    const searchModalTitle = page.querySelector('#discoverSearchTitle');
    const searchModalMeta = page.querySelector('#discoverSearchMeta');
    const searchModalSeeAll = page.querySelector('#discoverSearchSeeAll');

    const openSearchModal = ({ loading = false } = {}) => {
        if (!searchModal) return;

        searchModal.hidden = false;
        searchModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('discover-search-modal-open');

        if (loading && searchModalBody) {
            searchModalBody.setAttribute('aria-busy', 'true');
            searchModalBody.innerHTML = `
                <div class="discover-search-loading">
                    <span class="discover-search-spinner" aria-hidden="true"></span>
                    <strong>داریم بهترین گزینه‌ها را پیدا می‌کنیم...</strong>
                    <small>نتیجه واقعی از سیستم NOBAT دریافت می‌شود.</small>
                </div>
            `;

            if (searchModalTitle) {
                searchModalTitle.textContent = 'در حال جستجو';
            }

            if (searchModalMeta) {
                searchModalMeta.textContent = 'چند لحظه...';
            }
        }

        requestAnimationFrame(() => {
            searchModal.classList.add('is-open');
            searchModal.querySelector('#discoverSearchClose')?.focus();
        });
    };

    const closeSearchModal = () => {
        if (!searchModal) return;

        searchModal.classList.remove('is-open');
        searchModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('discover-search-modal-open');

        window.setTimeout(() => {
            if (!searchModal.classList.contains('is-open')) {
                searchModal.hidden = true;
            }
        }, 180);
    };

    const renderSearchModal = (freshResults) => {
        if (!searchModal || !searchModalBody) return;

        const title =
            freshResults
                .querySelector('#discover-results-title')
                ?.textContent
                ?.replace(/\\s+/g, ' ')
                ?.trim()
            || 'نتایج جستجو';

        const cards = Array.from(
            freshResults.querySelectorAll('.discover-result-card')
        ).slice(0, 6);

        const totalText =
            freshResults
                .querySelector('.mb-5')
                ?.textContent
                ?.replace(/\\s+/g, ' ')
                ?.trim();

        searchModalTitle && (searchModalTitle.textContent = title);
        searchModalMeta && (
            searchModalMeta.textContent =
                cards.length > 0
                    ? (totalText || `${cards.length} سالن در این صفحه`)
                    : 'برای این جستجو نتیجه‌ای پیدا نشد.'
        );

        searchModalBody.innerHTML = '';
        searchModalBody.setAttribute('aria-busy', 'false');

        if (!cards.length) {
            searchModalBody.innerHTML = `
                <div class="discover-search-empty">
                    <div class="discover-search-empty-icon">⌕</div>
                    <strong>نتیجه‌ای پیدا نشد</strong>
                    <p>عبارت جستجو یا فیلترها را کمی تغییر بده و دوباره امتحان کن.</p>
                </div>
            `;
        } else {
            const grid = document.createElement('div');
            grid.className = 'discover-search-results-grid';

            cards.forEach((card) => {
                grid.appendChild(card.cloneNode(true));
            });
