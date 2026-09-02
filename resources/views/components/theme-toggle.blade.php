<div
    x-data
    class="relative"
>

    <button
        type="button"
        @click="$store.theme.toggle()"
        class="pressable flex h-10 items-center gap-2 rounded-xl border border-border bg-surface px-3 text-content-soft shadow-soft hover:text-content"
        :aria-label="$store.theme.current === 'dark' ? 'فعال کردن حالت روشن' : 'فعال کردن حالت تاریک'"
    >

        {{-- Sun --}}

        <svg
            x-show="$store.theme.current === 'light'"
            x-cloak
            width="17"
            height="17"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
        >
            <circle
                cx="12"
                cy="12"
                r="4"
            />

            <path d="M12 2v2" />
            <path d="M12 20v2" />
            <path d="m4.93 4.93 1.41 1.41" />
            <path d="m17.66 17.66 1.41 1.41" />
            <path d="M2 12h2" />
            <path d="M20 12h2" />
            <path d="m6.34 17.66-1.41 1.41" />
            <path d="m19.07 4.93-1.41 1.41" />
        </svg>


        {{-- Moon --}}

        <svg
            x-show="$store.theme.current === 'dark'"
            x-cloak
            width="17"
            height="17"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
        >
            <path d="M21 12.8A8.5 8.5 0 1 1 11.2 3 6.7 6.7 0 0 0 21 12.8Z" />
        </svg>


        <span
            class="hidden text-xs font-bold sm:block"
            x-text="$store.theme.current === 'dark' ? 'تاریک' : 'روشن'"
        ></span>

    </button>

</div>
