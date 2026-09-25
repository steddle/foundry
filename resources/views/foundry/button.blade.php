@props(['href' => null, 'variant' => 'primary', 'size' => 'base', 'icon' => null])

{{--
    A link or a form's button, drawn by Flux. One primary per viewport.

    @group Elements
    @prop href Where the button leads; without one it is a button, a form's.
    @prop variant primary, secondary or ghost: secondary belongs on a card, ghost on bone or ink. Any other value draws primary.
    @prop size base, sm or xs, Flux's steps. A ghost at base with a label carries a hairline border; at sm or xs, or with an icon alone, it has none.
    @prop icon A Heroicon before the label. Without a label the button is square, and takes an aria-label.

    @example Primary and secondary
    <div class="flex flex-wrap gap-4">
        <foundry:button href="#">Primary</foundry:button>
        <foundry:button href="#" variant="secondary">Secondary</foundry:button>
    </div>

    @example Ghost, on ink
    @ground ink
    <foundry:button href="#" variant="ghost">Ghost</foundry:button>

    @example Ghost on the page, small and icon alone
    <div class="flex flex-wrap items-center gap-4">
        <foundry:button href="#" variant="ghost">Ghost</foundry:button>
        <foundry:button variant="ghost" size="sm" icon="pencil-square">Change</foundry:button>
        <foundry:button variant="ghost" size="sm" icon="ellipsis-horizontal" aria-label="More" />
    </div>
--}}
@php
    $ghost = $variant === 'ghost';
@endphp

<flux:button :href="$href" :$size :$icon :variant="['ghost' => 'ghost', 'secondary' => 'outline'][$variant] ?? 'primary'" {{ $attributes->class([
    'font-semibold! active:shadow-press',
    'text-zinc-950! dark:text-zinc-50! hover:bg-zinc-950/5! dark:hover:bg-zinc-50/5!' => $ghost,
    'border border-zinc-950/13 dark:border-zinc-50/13' => $ghost && $size === 'base' && $slot->isNotEmpty(),
]) }}>{{ $slot }}</flux:button>
