@props(['href', 'title'])

{{--
    One row in a ruled list: a serif title, a sentence from the slot, an
    arrow. The parent draws the rules.

    @group Navigation
    @prop href Where the whole row leads.
    @prop title The row's heading, at size 2.

    @example Two rows
    <div class="flex flex-col divide-y divide-zinc-200 border-y border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
        <foundry:link-row href="#" title="Design">The brand, the colours, the type and the assets, on one page.</foundry:link-row>
        <foundry:link-row href="#" title="Components">Every component the site renders, live.</foundry:link-row>
    </div>
--}}
<a href="{{ $href }}" {{ $attributes->class('group flex items-start justify-between gap-6 py-6') }}>
    <div class="flex flex-col gap-1.5">
        <foundry:heading size="2" class="group-hover:text-primary-700 dark:group-hover:text-primary-300">{{ $title }}</foundry:heading>
        <foundry:text class="max-w-[60ch]">{{ $slot }}</foundry:text>
    </div>
    <flux:icon.arrow-right variant="micro" class="mt-2 shrink-0 fill-zinc-600 dark:fill-zinc-400 group-hover:fill-primary-700 dark:group-hover:fill-primary-300" />
</a>
