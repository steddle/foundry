@props(['count'])

{{--
    Tiles on a hairline grid, one to three columns, each three rows tall.

    @group Layout
    @prop count How many tiles the slot holds, so blank tiles close the last row at every width.

    @example Two topics
    <foundry:tile-grid :count="2">
        <foundry:topic icon="command-line" eyebrow="6 articles" title="The CLI" href="#" :links="['bron law' => '#', 'bron case' => '#']" more="All 6 articles">
            Every command, every option, and what a script can rely on when it reads the output.
        </foundry:topic>
        <foundry:topic icon="book-open" eyebrow="2 articles" title="Concepts" href="#" :links="['Two dates' => '#', 'Two lanes' => '#']">
            The ideas the answers rest on.
        </foundry:topic>
    </foundry:tile-grid>
--}}
<div {{ $attributes->class('grid grid-cols-1 gap-px overflow-hidden rounded-md border border-zinc-200 bg-zinc-200 sm:grid-cols-2 lg:grid-cols-3 dark:border-zinc-700 dark:bg-zinc-700') }}>
    {{ $slot }}
    @for ($i = $count; $i % 3 !== 0; $i++)
        <div aria-hidden="true" class="row-span-3 bg-zinc-25 max-lg:hidden dark:bg-zinc-800"></div>
    @endfor
    @if ($count % 2 !== 0)
        <div aria-hidden="true" class="row-span-3 bg-zinc-25 max-sm:hidden lg:hidden dark:bg-zinc-800"></div>
    @endif
</div>
