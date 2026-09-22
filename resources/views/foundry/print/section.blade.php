@props(['title', 'newPage' => false])

{{--
    A section of a printed document: its heading under a bar in the accent,
    the two kept together and on the page with what follows them.

    @prop title The section's heading.
    @prop newPage Starts the section on a page of its own, as a signature page does.

    @example A section
    <foundry:print.section title="The agreement">
        <foundry:text>What the section says.</foundry:text>
    </foundry:print.section>
--}}
<section {{ $attributes->class($newPage ? 'break-before-page' : 'mt-12 first:mt-10') }}>
    <foundry:heading size="2" level="2" class="mb-5 before:mb-4 before:block before:h-1 before:w-12 before:bg-primary-300 before:content-['']">{{ $title }}</foundry:heading>
    {{ $slot }}
</section>
