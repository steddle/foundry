{{--
    GitHub's social preview: the OG image's design on GitHub's 1280×640.

    @group Brand

    @example Heading and lede
    @ground bare
    @zoom 0.5
    <x-site.social-preview heading="A heading with its phrase marked." marked="phrase marked." lede="The lede under it." />
--}}
<x-site.og-image :width="1280" :height="640" {{ $attributes }} />
