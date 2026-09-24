@props(['eyebrow' => null, 'title' => null, 'lead' => null])

{{--
    The first page of a long printed document, ink to every edge: the
    lockup, the words under it, and at the foot the particulars over a rule
    and a last line. The page carries no footer and no count; the document
    goes on from page 2. It takes foundry:print.masthead's props and slots,
    and a foot of its own.

    @prop eyebrow The line over the title.
    @prop title The document's title.
    @prop lead A sentence under the title.
    @slot aside At the right of the lockup: a seal, a reference.
    @slot slot At the foot, the particulars as `foundry:print.facts`.
    @slot foot The last line: what the document is to its readers, and who issued it, each a span at either end.

    @example A briefing's cover
    @ground bare
    <foundry:print.cover eyebrow="Internal briefing" title="Where we stand" lead="The company, the engine, the corpus and matter one.">
        <foundry:print.facts :items="['Prepared for' => 'The Steddle team', 'Prepared by' => 'Mischa Sigtermans', 'Date' => '18 September 2026', 'Basis' => 'Measured 18 September 2026']" />
        <x-slot:foot>
            <span>Confidential, internal</span>
            <span>Steddle B.V. | Amsterdam | 2026</span>
        </x-slot:foot>
    </foundry:print.cover>
--}}
<style>
    @page cover {
        margin: 0;

        @bottom-left {
            content: none;
        }

        @bottom-right {
            content: none;
        }
    }
</style>

{{-- The page's full height, from foundry:print.document; on a screen, A4's. --}}
<section {{ $attributes->class('print-bleed ink relative isolate flex h-[var(--print-page-height,297mm)] flex-col overflow-hidden bg-zinc-900 px-8 pt-24 pb-10 text-zinc-50 break-after-page [page:cover]') }}>
    <foundry:mark class="absolute -right-24 -bottom-32 -z-10 size-[34rem] text-zinc-50 opacity-[0.05]" />

    <div class="flex items-start justify-between gap-8">
        <foundry:lockup class="h-8" />
        {{ $aside ?? '' }}
    </div>

    @if ($eyebrow || $title || $lead)
        <div class="mt-32 max-w-[150mm]">
            @if ($eyebrow)
                <foundry:text variant="label" tone="accent">{{ $eyebrow }}</foundry:text>
            @endif
            @if ($title)
                <foundry:heading size="display" level="1" class="mt-4">{{ $title }}</foundry:heading>
            @endif
            @if ($lead)
                <foundry:text variant="lede" tone="muted" class="mt-5">{{ $lead }}</foundry:text>
            @endif
        </div>
    @endif

    <div class="mt-auto">
        @if ($slot->isNotEmpty())
            <div class="border-t border-zinc-50/13 pt-6">
                {{ $slot }}
            </div>
        @endif
        @isset($foot)
            <div class="mt-10 flex justify-between gap-8 text-meta text-zinc-400">
                {{ $foot }}
            </div>
        @endisset
    </div>
</section>
