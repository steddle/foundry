{{--
    The page's width and gutters. Every band sets its content in one.

    @group Layout

    @example Content at the page's width
    <x-site.container>
        <div class="rounded-md border border-dashed border-zinc-200 dark:border-zinc-700 p-6 text-zinc-600 dark:text-zinc-400">Wide, with the phone and desktop gutters.</div>
    </x-site.container>
--}}
<div {{ $attributes->class('mx-auto w-full max-w-wide px-4 sm:px-12') }}>
    {{ $slot }}
</div>
