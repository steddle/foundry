@props(['heading', 'lede' => null, 'eyebrow' => null, 'marked' => null])

{{--
    GitHub's social preview: the OG image's design on GitHub's 1280×640.
    Its words go to foundry:og-image as props, and any other attribute with
    them.

    @group Brand
    @prop heading The image's headline.
    @prop lede The line under the heading.
    @prop eyebrow A label in the accent, opposite the lockup.
    @prop marked The one phrase of the heading that carries the marker.

    @example Heading and lede
    @ground bare
    <foundry:social-preview heading="A heading with its phrase marked." marked="phrase marked." lede="The lede under it." />
--}}
{{-- Bound one by one: an attribute bag passed on escapes its values once more, and an apostrophe would read `&#039;`. --}}
<foundry:og-image :width="1280" :height="640" :$heading :$lede :$eyebrow :$marked {{ $attributes }} />
