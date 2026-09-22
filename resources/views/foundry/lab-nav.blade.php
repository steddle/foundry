@props(['home' => '/', 'homeLabel' => null])

{{--
    The bar of the lab's own pages, on bone: the lockup home, then the lab,
    the design page and the components.

    @group Shell
    @prop home Where the lockup leads.
    @prop homeLabel The lockup link's accessible name; without one, `foundry::nav.home` with the imprint's name.

    @example As this imprint sets it
    @ground bare
    <foundry:lab-nav />
--}}
@php
    $homeLabel ??= __('foundry::nav.home', ['name' => config('imprint.name')]);
    $pages = ['Lab' => 'foundry.lab', 'Design' => 'foundry.design', 'Components' => 'foundry.components'];
@endphp

<header class="border-b border-zinc-200 dark:border-zinc-700">
    <nav aria-label="Lab">
        <foundry:container class="flex items-center justify-between gap-6 py-5">
            <a href="{{ $home }}" aria-label="{{ $homeLabel }}" class="shrink-0 text-zinc-950 dark:text-zinc-50">
                <foundry:lockup class="h-5" />
            </a>
            <div class="flex items-center gap-5 sm:gap-7">
                @foreach ($pages as $label => $name)
                    <a href="{{ route($name) }}" @if (request()->routeIs($name)) aria-current="page" @endif
                        class="text-copy font-medium whitespace-nowrap text-zinc-600 hover:text-zinc-950 aria-[current=page]:text-zinc-950 dark:text-zinc-400 dark:hover:text-zinc-50 dark:aria-[current=page]:text-zinc-50">{{ $label }}</a>
                @endforeach
            </div>
        </foundry:container>
    </nav>
</header>
