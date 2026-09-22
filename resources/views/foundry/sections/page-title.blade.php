@props(['eyebrow' => null, 'title', 'lead' => null])

{{--
    The first band of a page with no hero, under the bar on bone: its
    eyebrow, title and lede, then the slot in the same band.

    @group Sections
    @prop eyebrow A label in the accent above the title.
    @prop title The page's h1, at size 1.
    @prop lead The lede under the title.
    @slot actions The buttons under the lede.
    @slot slot What follows the rest of the section, in the same band.

    @example A title and a lede
    @ground bare
    <foundry:sections.page-title title="Lab." lead="The design system, every component the site renders, and the questions still open." />
--}}
<foundry:section {{ $attributes }}>
    <foundry:section-head stacked :level="1" :$eyebrow :$title :$lead />
    @isset($actions)
        <foundry:actions>{{ $actions }}</foundry:actions>
    @endisset
    {{ $slot }}
</foundry:section>
