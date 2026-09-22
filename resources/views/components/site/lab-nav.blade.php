@props(['home' => '/', 'homeLabel' => null])

{{--
    The bar of the lab's own pages, on bone: the lockup home, then the lab,
    the design page and the components.

    @group Shell

    @example As this imprint sets it
    @ground bare
    <x-site.lab-nav />
--}}
@php
    $homeLabel ??= __('foundry::nav.home', ['name' => config('imprint.name')]);
    $pages = ['Lab' => 'foundry.lab', 'Design' => 'foundry.design', 'Components' => 'foundry.components'];
@endphp

<header class="border-b border-zinc-200 dark:border-zinc-700">
    <nav aria-label="Lab">
        <x-site.container class="flex items-center justify-between gap-6 py-5">
            <a href="{{ $home }}" aria-label="{{ $homeLabel }}" class="shrink-0 text-zinc-950 dark:text-zinc-50">
                <x-site.lockup class="h-6" />
            </a>
            <div class="flex items-center gap-5 sm:gap-7">
                @foreach ($pages as $label => $name)
                    <a href="{{ route($name) }}" @if (request()->routeIs($name)) aria-current="page" @endif
                        class="text-copy font-medium whitespace-nowrap text-zinc-600 hover:text-zinc-950 aria-[current=page]:text-zinc-950 dark:text-zinc-400 dark:hover:text-zinc-50 dark:aria-[current=page]:text-zinc-50">{{ $label }}</a>
                @endforeach
            </div>
        </x-site.container>
    </nav>
</header>
