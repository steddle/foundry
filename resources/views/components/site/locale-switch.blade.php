@props(['locales' => null])

{{--
    Every language the imprint speaks, the current one marked. `$locales`:
    locale => this page in that locale. Sits on ink.

    A form and not a link: the choice is remembered, and an address in the
    root language reached by a link would be sent straight back by the
    browser's own preference. The page names its counterparts to a crawler in
    its head.

    @group Bands

    @example In the bar, where the imprint speaks more than one language
    @code
    <x-site.locale-switch />
--}}
@php
    $locales ??= localized_alternates(absolute: false);
@endphp

<ul role="list" {{ $attributes->class('flex items-center gap-1 text-sm font-semibold') }}>
    @foreach ($locales as $locale => $to)
        <li>
            @if ($locale === app()->getLocale())
                <span aria-current="true" lang="{{ $locale }}" class="flex rounded-sm px-2 py-1 text-zinc-50">{{ strtoupper($locale) }}</span>
            @else
                <form method="POST" action="{{ route('locale.update', $locale) }}">
                    @csrf
                    <input type="hidden" name="to" value="{{ $to }}" />
                    <button type="submit" lang="{{ $locale }}" class="flex cursor-pointer rounded-sm px-2 py-1 text-zinc-400 hover:text-zinc-50">{{ strtoupper($locale) }}</button>
                </form>
            @endif
        </li>
    @endforeach
</ul>
