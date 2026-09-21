@props(['href' => null, 'variant' => 'primary'])

{{-- One primary per viewport. Ghost belongs on ink, secondary on a card. A button without an href is a form's. --}}
<flux:button :href="$href" :variant="['ghost' => 'ghost', 'secondary' => 'outline'][$variant] ?? 'primary'" {{ $attributes->class([
    'font-semibold! active:shadow-press',
    'border border-zinc-50/13 text-zinc-50! hover:bg-zinc-50/5!' => $variant === 'ghost',
]) }}>{{ $slot }}</flux:button>
