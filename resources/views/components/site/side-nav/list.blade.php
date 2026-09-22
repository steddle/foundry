@props(['groups'])

{{--
    The groups of x-site.side-nav, each a nav under its title.

    @prop groups title => list of ['label' => …, 'href' => …, 'current' => bool], as x-site.side-nav takes them.
--}}

<div {{ $attributes->class('flex flex-col gap-8') }}>
    @foreach ($groups as $title => $links)
        <nav aria-label="{{ $title }}" class="flex flex-col gap-3">
            <x-site.text variant="label" tone="muted">{{ $title }}</x-site.text>
            <ul role="list" class="flex flex-col border-l border-zinc-200 dark:border-zinc-700">
                @foreach ($links as $link)
                    <li>
                        <a href="{{ $link['href'] }}" @if ($link['current'] ?? false) aria-current="page" @endif @class([
                            '-ml-px flex border-l py-1.5 pl-4 text-copy',
                            'border-primary-700 dark:border-primary-300 text-zinc-950 dark:text-zinc-50' => $link['current'] ?? false,
                            'border-transparent hover:border-zinc-500 hover:text-zinc-950 dark:hover:text-zinc-50' => ! ($link['current'] ?? false),
                        ])>{{ $link['label'] }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>
    @endforeach
</div>
