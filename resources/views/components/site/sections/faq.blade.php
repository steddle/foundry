@props(['eyebrow' => null, 'title', 'lead' => null, 'questions', 'sunken' => false])

{{--
    The questions a reader asks first, each closed until opened, so the page
    stays short while every answer stays in the HTML for search and for the
    page's markdown. A page that wants them in its structured data writes the
    FAQPage itself, beside what else it states.

    @group Sections
    @prop eyebrow A label in the accent above the title.
    @prop title The section's h2, at size 1.
    @prop lead The lede, beside the title on a wide screen.
    @prop questions A list of [question, answer], or of rows keyed `question` and `answer`.
    @prop sunken Sets the section a step below the page.
    @slot slot What follows the rest of the section, in the same band.

    @example Two questions
    @ground bare
    <x-site.sections.faq eyebrow="Questions" title="What it is, and what it is not." :questions="[
        ['Who is it for?', 'For litigation funders, counsel and claim foundations.'],
        ['What does it cost?', 'Nothing up front. We are paid from the outcome.'],
    ]" />
--}}
<x-site.section :$sunken {{ $attributes }}>
    <x-site.section-head :$eyebrow :$title :$lead />

    <div class="flex flex-col border-t border-zinc-200 dark:border-zinc-700">
        @foreach ($questions as $question)
            @php([$question, $answer] = array_values($question))
            <details class="group border-b border-zinc-200 dark:border-zinc-700">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-6 py-6 [&::-webkit-details-marker]:hidden">
                    <x-site.heading size="3" level="3">{{ $question }}</x-site.heading>
                    <span class="text-heading-3 text-zinc-600 dark:text-zinc-400 transition-transform duration-200 group-open:rotate-45" aria-hidden="true" data-markdown-skip>+</span>
                </summary>
                <x-site.text class="max-w-[62ch] pb-8">{{ $answer }}</x-site.text>
            </details>
        @endforeach
    </div>

    {{ $slot }}
</x-site.section>
