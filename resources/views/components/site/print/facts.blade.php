@props(['items', 'columns' => 2])

{{--
    A document's particulars: label over value, in columns as wide as what
    they hold, spread across the line. Values set in tabular numerals, and a
    long one, a hash, breaks anywhere rather than overflow.

    @prop items label => value.
    @prop columns 1, 2, 3 or 4.

    @example Particulars
    <x-site.print.facts :items="['Agreement' => 'a2cc5f8c-2262-4cd5-ac4c-1d7b85c9074d', 'Template' => '2.2', 'Sealed' => '21 Sep 2026, 10:37 UTC', 'Paper' => 'A4']" />
--}}
@php
    $grids = [1 => 'grid-cols-1', 2 => 'grid-cols-[repeat(2,minmax(0,auto))]', 3 => 'grid-cols-[repeat(3,minmax(0,auto))]', 4 => 'grid-cols-[repeat(4,minmax(0,auto))]'];
@endphp

<dl {{ $attributes->class(['grid justify-between gap-x-10 gap-y-4', $grids[$columns]]) }}>
    @foreach ($items as $label => $value)
        <div class="min-w-0 break-inside-avoid">
            <dt class="text-meta text-zinc-600 dark:text-zinc-400">{{ $label }}</dt>
            <dd class="mt-0.5 text-small font-medium break-all text-zinc-950 tabular-nums dark:text-zinc-50">{{ $value }}</dd>
        </div>
    @endforeach
</dl>
