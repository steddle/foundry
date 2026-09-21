{{-- The row of buttons a band, a page or a form ends on. Chrome, so a page's markdown leaves it out. --}}
<div {{ $attributes->class('flex flex-wrap items-center gap-3') }} data-markdown-skip>
    {{ $slot }}
</div>
