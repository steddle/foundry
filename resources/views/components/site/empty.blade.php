@props(['icon' => null, 'title'])

{{--
    What a list shows before it holds anything: an icon, a title, a sentence
    on what goes here, and the action that adds the first, in a dashed panel.

    @group Elements
    @prop icon A Flux icon's name, set in a muted square.
    @prop title What is missing, at heading size 3.
    @slot default A sentence on what the list will hold.
    @slot actions The button that adds the first.

    @example No agreements yet
    <x-site.empty icon="document-text" title="No agreements yet">
        Send your first NDA and it shows here, with where it stands.
        <x-slot:actions>
            <x-site.button href="#">New agreement</x-site.button>
        </x-slot:actions>
    </x-site.empty>
--}}
<div {{ $attributes->class('flex flex-col items-start gap-4 rounded-lg border border-dashed border-zinc-300 dark:border-zinc-600 p-6 sm:p-8') }}>
    @if ($icon)
        <div class="flex size-10 items-center justify-center rounded-md bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400">
            <flux:icon :name="$icon" variant="outline" class="size-5" />
        </div>
    @endif
    <div class="flex flex-col gap-1.5">
        <x-site.heading size="3">{{ $title }}</x-site.heading>
        @if ($slot->isNotEmpty())
            <x-site.text tone="muted" class="max-w-[60ch]">{{ $slot }}</x-site.text>
        @endif
    </div>
    @isset($actions)
        <x-site.actions>{{ $actions }}</x-site.actions>
    @endisset
</div>
