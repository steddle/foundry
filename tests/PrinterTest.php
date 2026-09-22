<?php

use Illuminate\Support\Facades\Http;
use Steddle\Foundry\Pdf\Printer;

function inlineAssets(string $html): string
{
    $printer = new Printer;

    return (fn () => $this->inlineAssets($html))->call($printer);
}

beforeEach(function () {
    config(['app.url' => 'https://imprint.test']);

    Http::fake([
        'imprint.test/build/assets/app.css' => Http::response(
            '@font-face{font-family:"Oooh Baby";src:url(https://imprint.test/storage/fonts/x.woff2) format("woff2")}.font-cursive{font-family:Oooh Baby}',
        ),
        'imprint.test/storage/fonts/*' => Http::response('FONTBYTES'),
    ]);
});

it('pulls the stylesheet and its fonts into the document', function () {
    $html = inlineAssets(
        '<html><head><link rel="stylesheet" href="https://imprint.test/build/assets/app.css"></head><body>x</body></html>'
    );

    expect($html)
        ->toContain('<style>')
        ->toContain('.font-cursive{font-family:Oooh Baby}')
        ->toContain('url(data:font/woff2;base64,'.base64_encode('FONTBYTES').')')
        ->not->toContain('<link rel="stylesheet"')
        ->not->toContain('https://imprint.test/storage/fonts/x.woff2');
});

it('pulls in fonts declared in the page itself', function () {
    $html = inlineAssets(
        '<html><head><style>@font-face{src:url("https://imprint.test/storage/fonts/y.woff2")}</style></head><body>x</body></html>'
    );

    expect($html)->toContain('url(data:font/woff2;base64,'.base64_encode('FONTBYTES').')');
});

it('pulls in a font by the root-relative path Vite writes, from its own host', function () {
    Http::fake(['imprint.test/build/assets/*.woff2' => Http::response('VITEFONT')]);

    $html = inlineAssets(
        '<html><head><style>@font-face{src:url(/build/assets/Chivo-VF.woff2)}</style></head><body>url(//evil.example.com/x.woff2)</body></html>'
    );

    expect($html)
        ->toContain('url(data:font/woff2;base64,'.base64_encode('VITEFONT').')')
        ->toContain('url(//evil.example.com/x.woff2)');
});

it('leaves the reference alone when an asset cannot be fetched', function () {
    Http::fake(['imprint.test/build/assets/gone.css' => Http::response('', 404)]);

    $html = inlineAssets('<html><head><link rel="stylesheet" href="https://imprint.test/build/assets/gone.css"></head></html>');

    expect($html)->toContain('<link rel="stylesheet" href="https://imprint.test/build/assets/gone.css">')
        ->not->toContain('<style>');
});

it("refuses to fetch anything off the imprint's own host", function () {
    Http::fake(['*' => Http::response('SECRET')]);

    // A reader types this into a field that prints in the document.
    $html = inlineAssets(
        '<html><body>Field: url(http://169.254.169.254/latest/meta-data/x.woff2) and '
        .'<link rel="stylesheet" href="https://evil.example.com/x.css"></body></html>'
    );

    expect($html)
        ->toContain('url(http://169.254.169.254/latest/meta-data/x.woff2)')
        ->toContain('<link rel="stylesheet" href="https://evil.example.com/x.css">')
        ->not->toContain('SECRET')
        ->not->toContain('data:font/');

    Http::assertNothingSent();
});

it('fetches nothing from its own host on another port or scheme', function () {
    Http::fake(['*' => Http::response('LEAK')]);

    $html = inlineAssets(
        '<html><head><style>@font-face{src:url(https://imprint.test:6379/x.woff2)}@font-face{src:url(http://imprint.test/storage/fonts/x.woff2)}</style></head></html>'
    );

    expect($html)
        ->not->toContain('data:font')
        ->toContain('url(https://imprint.test:6379/x.woff2)')
        ->toContain('url(http://imprint.test/storage/fonts/x.woff2)');

    Http::assertNothingSent();
});
