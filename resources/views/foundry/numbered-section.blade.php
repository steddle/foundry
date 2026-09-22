@props(['number', 'name', 'note' => null, 'sunken' => false])

{{--
    A section that opens on a ruled row: its number, its name, one fact about
    what follows, and the actions on it.

    @group Layout
    @prop number The section's number as printed, `04`.
    @prop name The section's name, its h2.
    @prop note One fact about what follows, at the row's end; hidden on a phone.
    @prop sunken Sets the section a step below the page.
    @slot actions The actions on the section, at the row's end after the note.

    @example With a note
    <foundry:numbered-section number="04" name="Social images" note="4 files">
        <foundry:text>What follows the row.</foundry:text>
    </foundry:numbered-section>
--}}
<section {{ $attributes->class(['scroll-mt-4', 'bg-zinc-100 dark:bg-zinc-950' => $sunken]) }}>
    <div class="border-b border-zinc-200 dark:border-zinc-50/13">
        <foundry:container class="flex h-12 items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <p class="w-6 text-sm font-medium text-zinc-600 dark:text-zinc-400 tabular-nums">{{ $number }}</p>
                <h2 class="font-semibold text-zinc-950 dark:text-zinc-50">{{ $name }}</h2>
            </div>
            @if ($note || isset($actions))
                <div class="flex items-center gap-6">
                    @if ($note)
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 tabular-nums max-sm:hidden">{{ $note }}</p>
                    @endif
                    {{ $actions ?? '' }}
                </div>
            @endif
        </foundry:container>
    </div>
    <foundry:container class="pt-12 pb-20 lg:pt-16 lg:pb-28">
        {{ $slot }}
    </foundry:container>
</section>
