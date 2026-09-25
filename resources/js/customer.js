/**
 * NOBAT — Customer interactions
 *
 * Customer-only behavior lives here.
 * Keep public salon/discover behavior in their own bundles.
 */

(() => {
    const bootCancelConfirm = () => {
        document.querySelectorAll('[data-customer-cancel-form]').forEach((form) => {
            if (form.dataset.cancelBound === '1') {
                return;
            }

            form.dataset.cancelBound = '1';

            form.addEventListener('submit', (event) => {
                event.preventDefault();

                if (document.querySelector('[data-customer-dialog]')) {
                    return;
                }

                const dialog = document.createElement('div');

                dialog.dataset.customerDialog = '1';
                dialog.className = 'customer-dialog-backdrop';

                dialog.innerHTML = `
                    <div
                        class="customer-dialog"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="customer-dialog-title"
                    >
                        <div class="customer-dialog-icon" aria-hidden="true">!</div>
                        <div class="customer-dialog-content">
                            <span class="customer-eyebrow">CANCEL BOOKING</span>
                            <h2 id="customer-dialog-title">از لغو این نوبت مطمئنی؟</h2>
                            <p>
                                با ادامه، نوبتت لغو می‌شود و زمان انتخاب‌شده دوباره آزاد خواهد شد.
                            </p>
                        </div>

                        <div class="customer-dialog-actions">
                            <button type="button" class="customer-btn customer-btn-secondary" data-customer-dialog-close>
                                نه، برگرد
                            </button>

                            <button type="button" class="customer-btn customer-btn-danger" data-customer-dialog-confirm>
                                بله، لغو کن
                            </button>
                        </div>
                    </div>
                `;

                document.body.appendChild(dialog);

                const close = () => {
                    dialog.remove();
                };

                dialog.querySelector('[data-customer-dialog-close]')?.addEventListener('click', close);

                dialog.addEventListener('click', (dialogEvent) => {
                    if (dialogEvent.target === dialog) {
                        close();
                    }
                });

                dialog.querySelector('[data-customer-dialog-confirm]')?.addEventListener('click', () => {
                    form.submit();
                });

                dialog.querySelector('[data-customer-dialog-close]')?.focus();
            });
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootCancelConfirm);
    } else {
        bootCancelConfirm();
    }
})();
