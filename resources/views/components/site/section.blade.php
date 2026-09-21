{{-- In dark mode every band shares the page ground, so a hairline separates them. --}}
<section {{ $attributes->class('scroll-mt-4 py-18 lg:py-32 dark:border-t dark:border-zinc-50/13') }}>
    <x-site.container class="flex flex-col gap-12 lg:gap-18">
        {{ $slot }}
    </x-site.container>
</section>
