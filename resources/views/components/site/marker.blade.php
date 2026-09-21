{{--
    The lichen highlighter behind one phrase of a headline. Once per viewport, never on body copy or anything clickable.
    `inline-block leading-none` sizes it to the glyphs: an inline box is as tall as the face's content area, which at display sizes spills onto the next line.

    @group Type

    @example On a headline, on ink
    @ground ink
    <x-site.heading size="1">A headline with <x-site.marker>one phrase marked.</x-site.marker></x-site.heading>
--}}
<mark {{ $attributes->class('relative z-0 inline-block bg-transparent leading-none whitespace-nowrap text-zinc-950 before:absolute before:-inset-x-[0.12em] before:-top-[0.05em] before:bottom-[0.06em] before:-z-10 before:-rotate-1 before:rounded-sm before:bg-secondary-300') }}>{{ $slot }}</mark>
