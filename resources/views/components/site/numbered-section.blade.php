@props(['number', 'name', 'note' => null])

{{-- A section that opens on a ruled row: its number, its name, one fact about what follows, and the actions on it. --}}
<section {{ $attributes->class('scroll-mt-4') }}>
    <div class="border-b border-subtle dark:border-hairline-inverse">
        <x-site.container class="flex h-12 items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <p class="w-6 text-sm font-medium text-muted tabular-nums">{{ $number }}</p>
                <h2 class="font-semibold text-strong">{{ $name }}</h2>
            </div>
            @if ($note || isset($actions))
                <div class="flex items-center gap-6">
                    @if ($note)
                        <p class="text-sm text-muted tabular-nums max-sm:hidden">{{ $note }}</p>
                    @endif
                    {{ $actions ?? '' }}
                </div>
            @endif
        </x-site.container>
    </div>
    <x-site.container class="pt-12 pb-20 lg:pt-16 lg:pb-28">
        {{ $slot }}
    </x-site.container>
</section>
