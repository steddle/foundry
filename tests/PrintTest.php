<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Steddle\Foundry\Catalog\Catalog;

beforeEach(function () {
    config(['imprint.stylesheet' => null]);
});

test('a printed document sets its paper and writes its footer on every page', function () {
    $html = Blade::render('<foundry:print.document title="Certificate" paper="Letter" footer="Signing certificate | Ada Visser">Body</foundry:print.document>', deleteCachedView: true);

    expect($html)
        ->toContain('<title>Certificate</title>')
        ->toContain('size: letter;')
        ->toContain('content: "Signing certificate | Ada Visser";')
        ->toContain('content: counter(page) " / " counter(pages);')
        ->toContain('Body');
});

test('a name in the footer cannot close its string or the stylesheet', function () {
    $html = Blade::render('<foundry:print.document title="x" :footer="$footer">Body</foundry:print.document>', ['footer' => 'Ada "Visser" </style><script>alert(1)</script>'], deleteCachedView: true);

    expect($html)
        ->toContain('content: "Ada \22 Visser\22  \3c /style\3e \3c script\3e alert(1)\3c /script\3e ";')
        ->not->toContain('<script>')
        ->not->toContain('</style><script>');
});

test('a masthead bleeds to the first page\'s top edge', function () {
    $this->artisan('view:clear');
    File::ensureDirectoryExists(resource_path('views/foundry'));
    File::put(resource_path('views/foundry/lockup.blade.php'), '<span {{ $attributes }}>Imprint</span>');

    try {
        $html = Blade::render('<foundry:print.masthead eyebrow="Signing certificate" title="Mutual NDA" />', deleteCachedView: true);
    } finally {
        File::deleteDirectory(resource_path('views/components'));
        File::deleteDirectory(resource_path('views/foundry'));
    }

    expect($html)
        ->toContain('@page :first')
        ->toContain('print-bleed')
        ->toContain('Signing certificate')
        ->toContain('Mutual NDA');
});

test('a printed document keeps a heading with the start of what follows it', function () {
    $html = Blade::render('<foundry:print.document title="x">Body</foundry:print.document>', deleteCachedView: true);

    expect($html)
        ->toContain(':is(h1, h2, h3, h4) + * {')
        ->toContain('break-before: avoid-page;')
        ->toContain('orphans: 3;');
});

test('a section starts on a page of its own when asked', function () {
    expect(Blade::render('<foundry:print.section title="Signed" new-page>Body</foundry:print.section>', deleteCachedView: true))
        ->toContain('break-before-page')
        ->not->toContain('mt-12');

    expect(Blade::render('<foundry:print.section title="Signed">Body</foundry:print.section>', deleteCachedView: true))
        ->toContain('mt-12')
        ->not->toContain('break-before-page');
});

test('the catalogue describes every print prop under the name the component declares', function () {
    foreach (['print-cover', 'print-document', 'print-facts', 'print-masthead', 'print-prose', 'print-section'] as $slug) {
        foreach (Catalog::shared()[$slug]['props'] as $prop) {
            expect($prop['default'])->not->toBe('passed on', "{$slug}: {$prop['name']} is not declared")
                ->and($prop['description'])->not->toBeEmpty("{$slug}: {$prop['name']} is undocumented");
        }
    }
});

test('a printed document tells the cover how tall its paper is', function () {
    expect(Blade::render('<foundry:print.document title="x">Body</foundry:print.document>', deleteCachedView: true))->toContain('--print-page-height: 297mm;')
        ->and(Blade::render('<foundry:print.document title="x" paper="Letter">Body</foundry:print.document>', deleteCachedView: true))->toContain('--print-page-height: 279.4mm;');
});

test('a cover fills the first page without the page\'s footer, and holds the words, the particulars and the foot', function () {
    $html = Blade::render(<<<'BLADE'
        <foundry:print.cover eyebrow="Internal briefing" title="Where we stand" lead="The company and the first matter.">
            <x-slot:aside><span id="seal"></span></x-slot:aside>
            <dl id="facts"></dl>
            <x-slot:foot><span>Confidential, internal</span></x-slot:foot>
        </foundry:print.cover>
        BLADE, deleteCachedView: true);

    expect($html)
        ->toMatch('/@page cover \{\s*margin: 0;\s*@bottom-left \{\s*content: none;\s*\}\s*@bottom-right \{\s*content: none;/')
        ->toContain('[page:cover]')
        ->toContain('Imprint</span>')
        ->toContain('<span id="seal"></span>')
        ->toContain('Internal briefing')
        ->toContain('Where we stand')
        ->toContain('The company and the first matter.')
        ->toContain('<dl id="facts"></dl>')
        ->toContain('<span>Confidential, internal</span>');
});

test('a cover without words is the lockup alone', function () {
    expect(Blade::render('<foundry:print.cover />', deleteCachedView: true))->toContain('Imprint</span>')->not->toContain('<h1');
});

test('prose sets rendered markdown, and starts each h2 after the first on a page of its own when asked', function () {
    $html = Blade::render('<foundry:print.prose><h2>Where we stand</h2><p>Body</p></foundry:print.prose>', deleteCachedView: true);

    expect($html)->toContain('<div class="print-prose"')->toContain('<h2>Where we stand</h2><p>Body</p>')->not->toContain('data-new-page')
        ->and(Blade::render('<foundry:print.prose new-page><h2>x</h2></foundry:print.prose>', deleteCachedView: true))->toContain('data-new-page');
});

test('the prose stylesheet is written in the ramps, and sets code alone in mono', function () {
    $css = (string) str(File::get(__DIR__.'/../resources/css/foundry.css'))->after('@utility print-prose {')->before("\n}\n");

    expect($css)->not->toMatch('/#[0-9a-f]{3,8}\b/i')
        ->and(str($css)->matchAll('/font-family: ([^;]+);/')->unique()->values()->all())->toBe(['var(--font-serif)', 'var(--font-sans)', 'var(--font-mono)'])
        ->and(substr_count($css, 'var(--font-mono)'))->toBe(1)
        ->and((string) str($css)->before('& pre {')->afterLast('& code {'))->toContain('font-family: var(--font-mono);');
});
