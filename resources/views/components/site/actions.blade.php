{{--
    The row of buttons a band, a page or a form ends on. Chrome, so a page's markdown leaves it out.

    @group Actions

    @example Two buttons
    <x-site.actions>
        <x-site.button href="#">Talk to us</x-site.button>
        <x-site.button href="#" variant="secondary">Read the docs</x-site.button>
    </x-site.actions>
--}}
<div {{ $attributes->class('flex flex-wrap items-center gap-3') }} data-markdown-skip>
    {{ $slot }}
</div>
