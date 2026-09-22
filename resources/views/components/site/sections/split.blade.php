@props(['eyebrow' => null, 'title', 'lead' => null, 'scene' => null, 'sunken' => false])

{{--
    A section in two halves: the eyebrow, the title and the lede on the left,
    what shows it on the right. On a phone the figure goes under the words.

    @group Sections
    @prop eyebrow A label in the accent above the title.
    @prop title The section's h2, at size 1.
    @prop lead The lede under the title.
    @prop scene The scene the band is ink over, by its name in config/imprint.php.
    @prop sunken Sets a section without a scene a step below the page.
    @slot slot A note after the actions.
    @slot figure The right half.
    @slot actions The buttons under the lede.

    @example With a figure
    @ground bare
    <x-site.sections.split eyebrow="The book" title="Graded before it counts." lead="Every claimant passes four checks, and every check keeps its date and its source.">
        <x-slot:figure>
            <div class="h-64 rounded-lg border border-zinc-200 bg-zinc-25 dark:border-zinc-700 dark:bg-zinc-800"></div>
        </x-slot:figure>
    </x-site.sections.split>

    @example On a scene, with an action
    @ground bare
    <x-site.sections.split :scene="array_key_first(config('imprint.scenes'))" eyebrow="For agents" title="Send one from Claude." lead="Connect it once, and your assistant does the rest.">
        <x-slot:actions>
            <x-site.button href="#">Connect</x-site.button>
        </x-slot:actions>
        <x-slot:figure>
            <div class="h-64 rounded-lg border border-zinc-50/13 bg-zinc-950/70"></div>
        </x-slot:figure>
    </x-site.sections.split>
--}}
<x-site.section :$scene :$sunken {{ $attributes }}>
    <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2 lg:gap-16">
        <div class="flex flex-col items-start gap-5">
            @if ($eyebrow)
                <x-site.text variant="label" tone="accent">{{ $eyebrow }}</x-site.text>
            @endif
            <x-site.heading size="1" level="2" class="max-w-[16ch]">{{ $title }}</x-site.heading>
            @if ($lead)
                <x-site.text variant="lede" class="max-w-[44ch]">{{ $lead }}</x-site.text>
            @endif
            @isset($actions)
                <x-site.actions>{{ $actions }}</x-site.actions>
            @endisset
            {{ $slot }}
        </div>
        {{ $figure ?? '' }}
    </div>
</x-site.section>
