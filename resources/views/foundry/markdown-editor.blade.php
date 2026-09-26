@props(['source' => false, 'label' => 'Text'])

{{--
    A markdown text in the rich editor, or as markdown with the switch on. It
    binds the page's EditsMarkdown state: `html`, `markdown` and `source`. A
    text the editor would not give back unchanged opens as markdown. Tables
    stay tables: foundry:head switches Flux's table extensions on before any
    editor boots.

    @group Forms
    @prop source Whether the page edits the markdown, its `source` property.
    @prop label The field's label.

    @example In a form
    <foundry:markdown-editor />
--}}
<div {{ $attributes->class('flex flex-col gap-4') }}>
    <div class="flex items-center justify-between gap-4">
        <flux:label>{{ $label }}</flux:label>
        <flux:switch wire:model.live="source" label="Markdown" align="left" />
    </div>

    @if ($source)
        <flux:textarea wire:key="markdown-source" wire:model="markdown" rows="24" class="font-mono text-small" :aria-label="$label" />
    @else
        <flux:editor wire:key="markdown-rich" wire:model="html" toolbar="heading | bold italic strike | bullet ordered blockquote | link code ~ undo redo" class="longform **:data-[slot=content]:min-h-96" :aria-label="$label" />
    @endif
    <flux:error name="markdown" />
</div>
