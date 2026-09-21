@props([
    'photo',
    'photoClass' => 'object-bottom-right',
    'scrim',
    'flatScrim',
    'links' => [],
    'home' => '/',
    'homeLabel' => null,
    'lockupClass' => 'h-8',
    'endorsed' => true,
])

@php
    $homeLabel ??= config('imprint.name').', home';
@endphp

{{--
    Ink closes the page, on the imprint's own photo. The slot is the page's
    closing section, set on the same photo. `scrim` is measured against the
    tall band a closing section makes; without one only the photo's floor
    shows, so `flatScrim` keeps the links legible. `items` replaces the list
    `links` would make, and `colophon` is the line below the rule.
--}}
<footer class="relative isolate overflow-hidden bg-inverse ink">
    <x-site.photo :name="$photo" :class="$photoClass" :scrim="$slot->hasActualContent() ? $scrim : $flatScrim" />

    <div class="relative">
        {{ $slot }}

        <x-site.container class="flex flex-col gap-10 py-14">
            <div class="flex flex-wrap items-center justify-between gap-8">
                <a href="{{ $home }}" aria-label="{{ $homeLabel }}" class="shrink-0 text-3xl text-strong">
                    <x-site.lockup :endorsed="$endorsed" class="{{ $lockupClass }}" />
                </a>
                <ul role="list" class="flex flex-wrap gap-x-7 gap-y-2">
                    @isset($items)
                        {{ $items }}
                    @else
                        @foreach ($links as $label => $href)
                            <li><a href="{{ $href }}" class="hover:text-strong">{{ $label }}</a></li>
                        @endforeach
                    @endisset
                </ul>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-x-8 gap-y-4 border-t border-hairline-inverse pt-6">
                {{ $colophon ?? '' }}
            </div>
        </x-site.container>
    </div>
</footer>
