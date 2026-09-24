@php
    $resolveImage = function ($path) {
        if (! $path) {
            return null;
        }

        if (
            str_starts_with($path, 'http://') ||
            str_starts_with($path, 'https://') ||
            str_starts_with($path, '/')
        ) {
            return $path;
        }

        return \Illuminate\Support\Facades\Storage::url($path);
    };
@endphp

<div id="discoverDynamicContent">
    @include('customer.discover.sections.results')
</div>
