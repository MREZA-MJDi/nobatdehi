<div x-data class="relative">
    <button
        type="button"
        @click="$store.theme.toggle()"
        :aria-label="$store.theme.current === 'dark' ? 'فعال کردن حالت روشن' : 'فعال کردن حالت تاریک'"
        :aria-pressed="$store.theme.current === 'dark'"
        class="group pressable inline-flex h-10 items-center gap-2 rounded-xl border border-border bg-surface px-3 text-content-soft shadow-soft transition-all duration-200 hover:-translate-y-0.5 hover:border-border-strong hover:bg-surface-soft hover:text-content focus:outline-none focus-visible:ring-2 focus-visible:ring-ring"
    >
        <span class="grid size-7 shrink-0 place-items-center rounded-lg bg-surface-soft transition-transform duration-200 group-hover:scale-105">
            <svg x-show="$store.theme.current === 'light'" x-cloak class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
            <svg x-show="$store.theme.current === 'dark'" x-cloak class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 12.8A8.5 8.5 0 1 1 11.2 3 6.7 6.7 0 0 0 21 12.8Z"/></svg>
        </span>
        <span class="hidden text-xs font-bold leading-none sm:block" x-text="$store.theme.current === 'dark' ? 'روشن' : 'تاریک'"></span>
        <span class="hidden size-1.5 rounded-full bg-primary sm:block"></span>
    </button>
</div>
