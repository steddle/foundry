@props(['back' => null, 'eyebrow' => null, 'title', 'lead' => null])

{{--
    The top of a signed-in page: a way back, the eyebrow, the title with its
    status beside it, the lede, and the page's actions, beside the words from
    sm and under them below. A div and not a header: a page's markdown drops
    every <header> as chrome.

    @group Type
    @prop back [label, href] of the page above, set small with an arrow over the rest.
    @prop eyebrow A label in the accent above the title.
    @prop title The page's h1.
    @prop lead The lede under the title.
    @slot status A badge beside the title.
    @slot actions The page's buttons, set in x-site.actions.

    @example A record's page
    <x-site.page-head :back="['Agreements', '#']" title="Mutual NDA with Northwind" lead="Sent 21 Sep 2026, 10:42 UTC | 2 years + 3 years confidential">
        <x-slot:status><x-site.badge tone="warning" dot>Awaiting signature</x-site.badge></x-slot:status>
        <x-slot:actions>
            <x-site.button href="#" variant="secondary">Resend</x-site.button>
            <x-site.button href="#">Download PDF</x-site.button>
        </x-slot:actions>
    </x-site.page-head>

    @example A list's page
    <x-site.page-head eyebrow="Dashboard" title="Agreements" lead="Every NDA you sent or signed.">
        <x-slot:actions>
            <x-site.button href="#">New agreement</x-site.button>
        </x-slot:actions>
    </x-site.page-head>
--}}
<div {{ $attributes->class('flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between') }}>
    <div class="flex min-w-0 flex-col gap-3">
        @if ($back)
            <a href="{{ $back[1] }}" class="w-fit text-small font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-950 dark:hover:text-zinc-50" data-markdown-skip>← {{ $back[0] }}</a>
        @endif
        @if ($eyebrow)
            <x-site.text variant="label" tone="accent">{{ $eyebrow }}</x-site.text>
        @endif
        <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
            <x-site.heading size="1" level="1">{{ $title }}</x-site.heading>
            {{ $status ?? '' }}
        </div>
        @if ($lead)
            <x-site.text variant="lede" tone="muted" class="max-w-[60ch]">{{ $lead }}</x-site.text>
        @endif
    </div>

    @isset($actions)
        <x-site.actions class="shrink-0">{{ $actions }}</x-site.actions>
    @endisset
</div>
