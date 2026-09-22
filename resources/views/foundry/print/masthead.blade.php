@props(['eyebrow' => null, 'title' => null, 'lead' => null])

{{--
    The ink band a printed document opens on, bled to the paper's edges at
    the top of its first page: the lockup, then the words. Without a title
    the band is only the lockup and what the slot holds, for a document whose
    own text opens on its title.

    @prop eyebrow The line over the title.
    @prop title The document's title.
    @prop lead A sentence under the title.
    @slot aside At the right of the lockup: a seal, a reference.
    @slot slot Under the words, the particulars as `foundry:print.facts`.

    @example With words and particulars
    @ground bare
    <foundry:print.masthead eyebrow="Signing certificate" title="Mutual Non-Disclosure Agreement" lead="Between Ada Visser and Northwind B.V.">
        <foundry:print.facts :items="['Agreement' => 'a2cc5f8c', 'Sealed' => '21 Sep 2026, 10:37 UTC']" class="mt-8" />
    </foundry:print.masthead>
--}}
<style>
    @page :first {
        margin-top: 0;
    }
</style>

<header {{ $attributes->class('print-bleed ink bg-zinc-900 px-8 pt-14 pb-10 text-zinc-50') }}>
    <div class="flex items-start justify-between gap-8">
        <foundry:lockup class="h-7" />
        {{ $aside ?? '' }}
    </div>

    @if ($eyebrow || $title || $lead)
        <div class="mt-14">
            @if ($eyebrow)
                <foundry:text variant="label" tone="accent">{{ $eyebrow }}</foundry:text>
            @endif
            @if ($title)
                <foundry:heading size="1" level="1" class="mt-3">{{ $title }}</foundry:heading>
            @endif
            @if ($lead)
                <foundry:text variant="lede" tone="muted" class="mt-4">{{ $lead }}</foundry:text>
            @endif
        </div>
    @endif

    {{ $slot }}
</header>
