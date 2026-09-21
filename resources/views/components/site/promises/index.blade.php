{{--
    Numbered commitments, two to a row on a wide screen: a list of
    x-site.promises.item. A CSS counter numbers them, so an item needs no
    index of its own.

    @group Layout

    @example Two promises
    <x-site.promises>
        <x-site.promises.item title="We give no legal advice.">What a text said, and where. What follows from it is for you and your counsel.</x-site.promises.item>
        <x-site.promises.item title="Every quotation can be checked.">Fetch the document, hash it and cut the span.</x-site.promises.item>
    </x-site.promises>
--}}
<dl {{ $attributes->class('grid grid-cols-1 gap-x-12 border-t border-zinc-200 dark:border-zinc-700 [counter-reset:promise] lg:grid-cols-2') }}>
    {{ $slot }}
</dl>
