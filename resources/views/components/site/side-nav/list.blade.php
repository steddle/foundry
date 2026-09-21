@props(['groups'])

<div {{ $attributes->class('flex flex-col gap-8') }}>
    @foreach ($groups as $title => $links)
        <nav aria-label="{{ $title }}" class="flex flex-col gap-3">
            <x-site.text variant="label" tone="muted">{{ $title }}</x-site.text>
            <ul role="list" class="flex flex-col border-l border-subtle">
                @foreach ($links as $link)
                    <li>
                        <a href="{{ $link['href'] }}" @if ($link['current'] ?? false) aria-current="page" @endif @class([
                            '-ml-px flex border-l py-1.5 pl-4 text-copy',
                            'border-accent text-strong' => $link['current'] ?? false,
                            'border-transparent hover:border-control hover:text-strong' => ! ($link['current'] ?? false),
                        ])>{{ $link['label'] }}</a>
                    </li>
                @endforeach
            </ul>
        </nav>
    @endforeach
</div>
