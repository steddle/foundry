@props(['eyebrow', 'title'])

{{--
    A section's opening: the eyebrow and the title, and the lede from the slot
    beside them on a wide screen. A div and not a header: a page's markdown
    drops every <header> as chrome.
--}}
<div {{ $attributes->class('grid grid-cols-1 items-end gap-6 lg:grid-cols-2 lg:gap-12') }}>
    <div class="flex flex-col gap-5">
        <x-site.text variant="label" tone="accent">{{ $eyebrow }}</x-site.text>
        <x-site.heading size="1" level="2" class="max-w-[22ch]">{{ $title }}</x-site.heading>
    </div>
    @if ($slot->isNotEmpty())
        <x-site.text variant="lede" class="max-w-[48ch]">{{ $slot }}</x-site.text>
    @endif
</div>
