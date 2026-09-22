@props(['href', 'title'])

{{--
    One row in a ruled list: a serif title, a sentence, an arrow. The parent draws the rules.

    @group Navigation

    @example Two rows
    <div class="flex flex-col divide-y divide-zinc-200 border-y border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
        <x-site.link-row href="#" title="Design">The brand, the colours, the type and the assets, on one page.</x-site.link-row>
        <x-site.link-row href="#" title="Components">Every component the site renders, live.</x-site.link-row>
    </div>
--}}
<a href="{{ $href }}" {{ $attributes->class('group flex items-start justify-between gap-6 py-6') }}>
    <div class="flex flex-col gap-1.5">
        <x-site.heading size="2" class="group-hover:text-primary-700 dark:group-hover:text-primary-300">{{ $title }}</x-site.heading>
        <x-site.text class="max-w-[60ch]">{{ $slot }}</x-site.text>
    </div>
    <flux:icon.arrow-right variant="micro" class="mt-2 shrink-0 fill-zinc-600 dark:fill-zinc-400 group-hover:fill-primary-700 dark:group-hover:fill-primary-300" />
</a>
