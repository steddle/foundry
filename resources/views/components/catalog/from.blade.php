@props(['from', 'hasCustom'])

@if ($hasCustom)
    <nav aria-label="Which components" class="flex self-start rounded-md border border-zinc-200 dark:border-zinc-700 p-0.5 text-small font-medium">
        @foreach ([null => 'All', 'foundry' => 'Foundry', 'custom' => 'Custom'] as $value => $label)
            <a href="{{ route('foundry.components', $value ? ['from' => $value] : []) }}" @if ($from === ($value ?: null)) aria-current="page" @endif @class([
                'rounded-sm px-3 py-1',
                'bg-zinc-200 dark:bg-zinc-700 text-zinc-950 dark:text-zinc-50' => $from === ($value ?: null),
                'text-zinc-600 dark:text-zinc-400 hover:text-zinc-950 dark:hover:text-zinc-50' => $from !== ($value ?: null),
            ])>{{ $label }}</a>
        @endforeach
    </nav>
@endif
