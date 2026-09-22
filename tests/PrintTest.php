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
    foreach (['print-document', 'print-facts', 'print-masthead', 'print-section'] as $slug) {
        foreach (Catalog::shared()[$slug]['props'] as $prop) {
            expect($prop['default'])->not->toBe('passed on', "{$slug}: {$prop['name']} is not declared")
                ->and($prop['description'])->not->toBeEmpty("{$slug}: {$prop['name']} is undocumented");
        }
    }
});
