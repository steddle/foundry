<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->artisan('view:clear');
    File::ensureDirectoryExists(resource_path('views/components/site'));
    File::put(resource_path('views/components/site/heading.blade.php'), '<h2 {{ $attributes }}>{{ $slot }}</h2>');
    File::put(resource_path('views/components/site/text.blade.php'), '<p {{ $attributes }}>{{ $slot }}</p>');
});

afterEach(function () {
    File::deleteDirectory(resource_path('views/components'));
});

test('steps number their rows in order, and a head without a lede prints none', function () {
    $html = Blade::render('<x-site.sections.steps id="how" eyebrow="How" title="Steps." :steps="[[\'Find\', \'One.\'], [\'Run\', \'Two.\']]" />', deleteCachedView: true);

    expect($html)->toContain('id="how"')
        ->toMatch('/01<\/h2>.*Find<\/h2>.*One\.<\/p>.*02<\/h2>.*Run<\/h2>.*Two\.<\/p>/s')
        ->not->toContain('variant="lede"');
});

test('features take as many columns as they are, with the lede beside the title', function () {
    $html = Blade::render('<x-site.sections.features eyebrow="Offer" title="Two." lead="Why." :features="[[\'A\', \'a.\'], [\'B\', \'b.\']]" />', deleteCachedView: true);

    expect($html)->toContain('lg:grid-cols-2')
        ->toContain('Why.</p>');
});

test('every answer is in the HTML, closed', function () {
    $html = Blade::render('<x-site.sections.faq eyebrow="Questions" title="Asked." :questions="[[\'Who?\', \'Funders.\']]" />', deleteCachedView: true);

    expect($html)->toContain('<details class="group')
        ->not->toMatch('/<details[^>]*\sopen/')
        ->toContain('Who?</h2>')
        ->toContain('Funders.</p>');
});

test('a split sets its actions under the words and its figure beside them, and leaves out what it was not given', function () {
    $html = Blade::render('<x-site.sections.split title="Two halves."><x-slot:actions><a href="#">Act</a></x-slot:actions><x-slot:figure><figure>F</figure></x-slot:figure></x-site.sections.split>', deleteCachedView: true);

    expect($html)->toMatch('/Two halves\..*<a href="#">Act<\/a>.*<figure>F<\/figure>/s')
        ->not->toContain('variant="label"')
        ->not->toContain('variant="lede"');
});

test('every section prints its slot after the rest, and takes its items keyed or in pairs', function (string $blade) {
    expect(Blade::render($blade, deleteCachedView: true))->toContain('After the rest.');
})->with([
    'hero' => '<x-site.sections.hero title="T.">After the rest.</x-site.sections.hero>',
    'page-title' => '<x-site.sections.page-title title="T.">After the rest.</x-site.sections.page-title>',
    'split' => '<x-site.sections.split title="T.">After the rest.</x-site.sections.split>',
    'steps' => '<x-site.sections.steps title="T." :steps="[[\'title\' => \'A\', \'body\' => \'a.\']]">After the rest.</x-site.sections.steps>',
    'features' => '<x-site.sections.features title="T." :features="[[\'title\' => \'A\', \'body\' => \'a.\']]">After the rest.</x-site.sections.features>',
    'faq' => '<x-site.sections.faq title="T." :questions="[[\'Q?\', \'A.\']]">After the rest.</x-site.sections.faq>',
    'cta' => '<x-site.sections.cta scene="s" title="T.">After the rest.</x-site.sections.cta>',
    'closing' => '<x-site.sections.closing title="T.">After the rest.</x-site.sections.closing>',
]);

test('a lede that holds markup arrives as a slot', function () {
    $html = Blade::render('<x-site.section-head title="T."><x-slot:lead>Run <code>bron</code>.</x-slot:lead></x-site.section-head>', deleteCachedView: true);

    expect($html)->toContain('Run <code>bron</code>.');
});
