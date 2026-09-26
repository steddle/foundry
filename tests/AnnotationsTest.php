<?php

use Steddle\Foundry\Annotations\PlainText;
use Steddle\Foundry\Annotations\TextQuote;
use Steddle\Foundry\Diff\WordDiff;

test('a quote with its context is found where the text still holds it', function () {
    $text = 'The fund closes in March. The fund opens in May.';
    $quote = TextQuote::between($text, 26, 34);

    expect($quote->quote)->toBe('The fund')
        ->and($quote->locateIn('Preface. '.$text))->toBe([35, 43]);
});

test('a quote found once is found without its context', function () {
    $quote = new TextQuote('closes in March', 'something else ', ' and more');

    expect($quote->locateIn('The fund closes in March, as agreed.'))->toBe([9, 24]);
});

test('a quote that occurs twice is told apart by its context, and orphaned when nothing tells them apart', function () {
    $text = 'Holly owns it. Mischa owns it.';

    expect((new TextQuote('owns it', 'Mischa '))->locateIn($text))->toBe([22, 29])
        ->and((new TextQuote('owns it'))->locateIn($text))->toBeNull();
});

test('a quote the text no longer holds is orphaned', function () {
    expect((new TextQuote('closes in March'))->locateIn('The fund closes in April.'))->toBeNull();
});

test('offsets count UTF-16 code units, as the browser does', function () {
    $text = 'Café 🚀 launch on Monday';
    $quote = TextQuote::of($text, 'launch');

    expect($quote->locateIn($text))->toBe([8, 14])
        ->and($quote->prefix)->toBe('Café 🚀 ');
});

test('the text of rendered HTML reads as textContent does', function () {
    $html = "<h2>Plan &amp; <em>budget</em></h2>\n<!-- note --><p>It&#39;s &lt;fine&gt;</p>\n<pre><code>\nx = 1\n</code></pre>";

    expect(PlainText::fromHtml($html))->toBe("Plan & budget\nIt's <fine>\n\nx = 1\n");
});

test('a word diff keeps what stayed and marks what went and came', function () {
    expect(WordDiff::between('The fund closes in March.', 'The fund closes in May.'))
        ->toBe([['same', 'The fund closes in'], ['removed', 'March.'], ['added', 'May.']]);
});
