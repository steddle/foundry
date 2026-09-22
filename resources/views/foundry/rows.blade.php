{{--
    A ruled list of records, each an foundry:record-row, with a rule above,
    between and below them.

    @group Elements

    @example Two records
    <foundry:rows>
        <foundry:record-row href="#" title="Mutual NDA with Northwind" meta="Sent 21 Sep 2026 | 2 years">
            <x-slot:status><foundry:badge tone="success">Signed</foundry:badge></x-slot:status>
        </foundry:record-row>
        <foundry:record-row href="#" title="One-way NDA with Contoso" meta="Sent 18 Sep 2026 | 3 years">
            <x-slot:status><foundry:badge tone="warning" dot>Awaiting signature</foundry:badge></x-slot:status>
        </foundry:record-row>
    </foundry:rows>
--}}
<ul role="list" {{ $attributes->class('divide-y divide-zinc-200 border-y border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700') }}>
    {{ $slot }}
</ul>
