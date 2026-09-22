@props(['href' => null, 'variant' => 'primary'])

{{--
    A link or a form's button, drawn by Flux. One primary per viewport.

    @group Elements
    @prop href Where the button leads; without one it is a button, a form's.
    @prop variant primary, secondary or ghost: ghost belongs on ink, secondary on a card. Any other value draws primary.

    @example Primary and secondary
    <div class="flex flex-wrap gap-4">
        <x-site.button href="#">Primary</x-site.button>
        <x-site.button href="#" variant="secondary">Secondary</x-site.button>
    </div>

    @example Ghost, on ink
    @ground ink
    <x-site.button href="#" variant="ghost">Ghost</x-site.button>
--}}
<flux:button :href="$href" :variant="['ghost' => 'ghost', 'secondary' => 'outline'][$variant] ?? 'primary'" {{ $attributes->class([
    'font-semibold! active:shadow-press',
    'border border-zinc-50/13 text-zinc-50! hover:bg-zinc-50/5!' => $variant === 'ghost',
]) }}>{{ $slot }}</flux:button>
