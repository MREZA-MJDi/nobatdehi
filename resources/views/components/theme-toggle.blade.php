<div x-data>

    <button
        type="button"
        @click="$store.theme.toggle()"
        :aria-label="
            $store.theme.current === 'dark'
                ? 'فعال کردن حالت روشن'
                : 'فعال کردن حالت تاریک'
        "
        class="theme-toggle"
    >

        <span class="theme-toggle-icon">

            <svg
                x-show="$store.theme.current === 'light'"
                x-cloak
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <circle cx="12" cy="12" r="4"/>
                <path d="M12 2v2"/>
                <path d="M12 20v2"/>
                <path d="M2 12h2"/>
                <path d="M20 12h2"/>
                <path d="m4.9 4.9 1.4 1.4"/>
                <path d="m17.7 17.7 1.4 1.4"/>
                <path d="m6.3 17.7-1.4 1.4"/>
                <path d="m19.1 4.9-1.4 1.4"/>
            </svg>


            <svg
                x-show="$store.theme.current === 'dark'"
                x-cloak
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path d="M21 12.8A8.5 8.5 0 1 1 11.2 3 6.7 6.7 0 0 0 21 12.8Z"/>
            </svg>

        </span>


        <span
            class="theme-toggle-label"
            x-text="
                $store.theme.current === 'dark'
                    ? 'روشن'
                    : 'تاریک'
            "
        ></span>

    </button>

</div>
