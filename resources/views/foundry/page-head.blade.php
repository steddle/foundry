@props(['breadcrumbs' => [], 'back' => [], 'eyebrow' => null, 'title', 'lead' => null])

{{--
    The top of a signed-in page: the trail of pages above it, the eyebrow, the
    title with its status beside it, a line under it, and the page's actions,
    beside the words from sm and under them below. The title is a heading-2:
    an app page is a place to work, not a cover. A div and not a header: a
    page's markdown drops every <header> as chrome.

    @group Type
    @prop breadcrumbs label => href for each page above this one, outermost first, as foundry:breadcrumb.
    @prop back label => href of the one page to go back to, e.g. a list above a record, as a link with an arrow: for a page the bar's links don't name.
    @prop eyebrow A label in the accent above the title.
    @prop title The page's h1.
    @prop lead A line under the title.
    @slot status A badge beside the title.
    @slot actions The page's buttons, set in foundry:actions.

    @example A record's page
    <foundry:page-head :back="['All agreements' => '#']" title="Mutual NDA with Northwind" lead="Sent 21 Sep 2026, 10:42 UTC | 2 years + 3 years confidential">
        <x-slot:status><foundry:badge tone="warning" dot>Awaiting signature</foundry:badge></x-slot:status>
        <x-slot:actions>
            <foundry:button href="#" variant="secondary">Resend</foundry:button>
            <foundry:button href="#">Download PDF</foundry:button>
        </x-slot:actions>
    </foundry:page-head>

    @example A list's page
    <foundry:page-head eyebrow="Dashboard" title="Agreements" lead="Every NDA you sent or signed.">
        <x-slot:actions>
            <foundry:button href="#">New agreement</foundry:button>
        </x-slot:actions>
    </foundry:page-head>
--}}
<div {{ $attributes->class('flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between') }}>
    <div class="flex min-w-0 flex-col gap-2">
        @if ($breadcrumbs)
            <foundry:breadcrumb :items="$breadcrumbs" data-markdown-skip />
        @endif
        @foreach ($back as $label => $href)
            <a href="{{ $href }}" class="inline-flex w-fit items-center gap-1 text-small font-semibold text-zinc-600 dark:text-zinc-400 hover:text-zinc-950 dark:hover:text-zinc-50" data-markdown-skip>
                <flux:icon.arrow-left variant="micro" />{{ $label }}
            </a>
        @endforeach
        @if ($eyebrow)
            <foundry:text variant="label" tone="accent">{{ $eyebrow }}</foundry:text>
        @endif
        <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
            <foundry:heading size="2" level="1">{{ $title }}</foundry:heading>
            {{ $status ?? '' }}
        </div>
        @if ($lead)
            <foundry:text tone="muted" class="max-w-[60ch]">{{ $lead }}</foundry:text>
        @endif
    </div>

    @isset($actions)
        <foundry:actions class="shrink-0">{{ $actions }}</foundry:actions>
    @endisset
</div>
