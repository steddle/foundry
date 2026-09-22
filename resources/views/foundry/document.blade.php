{{--
    A reading layout: navigation beside the text on wide screens, above it on narrow ones.

    @group Layout
    @slot aside The navigation, 15rem wide and sticky beside the text on a wide screen, and left out of the page's markdown.

    @example Side navigation and text
    <foundry:document>
        <x-slot:aside>
            <foundry:side-nav :groups="['On this page' => [['label' => 'Install', 'href' => '#', 'current' => true], ['label' => 'Configure', 'href' => '#']]]" />
        </x-slot:aside>
        <foundry:text>The text beside it.</foundry:text>
    </foundry:document>
--}}
<div {{ $attributes->class('grid grid-cols-1 gap-10 lg:grid-cols-[15rem_minmax(0,1fr)] lg:gap-12') }}>
    <aside data-markdown-skip class="lg:sticky lg:top-8 lg:self-start">
        {{ $aside }}
    </aside>
    <div class="min-w-0">
        {{ $slot }}
    </div>
</div>
