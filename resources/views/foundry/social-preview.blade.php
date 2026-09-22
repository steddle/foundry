{{--
    GitHub's social preview: the OG image's design on GitHub's 1280×640.
    Every attribute goes to foundry:og-image.

    @group Brand
    @prop heading The image's headline.
    @prop lede The line under the heading.
    @prop eyebrow A label in the accent, opposite the lockup.
    @prop marked The one phrase of the heading that carries the marker.

    @example Heading and lede
    @ground bare
    <foundry:social-preview heading="A heading with its phrase marked." marked="phrase marked." lede="The lede under it." />
--}}
<foundry:og-image :width="1280" :height="640" {{ $attributes }} />
