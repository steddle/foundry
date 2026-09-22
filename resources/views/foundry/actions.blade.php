{{--
    The row of buttons a band, a page or a form ends on. Chrome, so a page's markdown leaves it out.

    @group Elements

    @example Two buttons
    <foundry:actions>
        <foundry:button href="#">Talk to us</foundry:button>
        <foundry:button href="#" variant="secondary">Read the docs</foundry:button>
    </foundry:actions>
--}}
<div {{ $attributes->class('flex flex-wrap items-center gap-3') }} data-markdown-skip>
    {{ $slot }}
</div>
