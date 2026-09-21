{{--
    Numbered commitments, two to a row on a wide screen: a list of
    x-site.promises.item. A CSS counter numbers them, so an item needs no
    index of its own.
--}}
<dl {{ $attributes->class('grid grid-cols-1 gap-x-12 border-t border-zinc-200 dark:border-zinc-700 [counter-reset:promise] lg:grid-cols-2') }}>
    {{ $slot }}
</dl>
