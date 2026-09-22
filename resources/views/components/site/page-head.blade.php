@props(['breadcrumbs' => [], 'eyebrow' => null, 'title', 'lead' => null])

{{--
    The top of a signed-in page: the trail of pages above it, the eyebrow, the
    title with its status beside it, a line under it, and the page's actions,
    beside the words from sm and under them below. The title is a heading-2:
    an app page is a place to work, not a cover. A div and not a header: a
    page's markdown drops every <header> as chrome.

    @group Type
    @prop breadcrumbs label => href for each page above this one, outermost first, as x-site.breadcrumb.
    @prop eyebrow A label in the accent above the title.
    @prop title The page's h1.
    @prop lead A line under the title.
    @slot status A badge beside the title.
    @slot actions The page's buttons, set in x-site.actions.

    @example A record's page
    <x-site.page-head :breadcrumbs="['Agreements' => '#']" title="Mutual NDA with Northwind" lead="Sent 21 Sep 2026, 10:42 UTC | 2 years + 3 years confidential">
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
<div {{ $attributes->class('flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between') }}>
    <div class="flex min-w-0 flex-col gap-2">
        @if ($breadcrumbs)
            <x-site.breadcrumb :items="$breadcrumbs" data-markdown-skip />
        @endif
        @if ($eyebrow)
            <x-site.text variant="label" tone="accent">{{ $eyebrow }}</x-site.text>
        @endif
        <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
            <x-site.heading size="2" level="1">{{ $title }}</x-site.heading>
            {{ $status ?? '' }}
        </div>
        @if ($lead)
            <x-site.text tone="muted" class="max-w-[60ch]">{{ $lead }}</x-site.text>
        @endif
    </div>

    @isset($actions)
        <x-site.actions class="shrink-0">{{ $actions }}</x-site.actions>
    @endisset
</div>
