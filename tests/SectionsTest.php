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
        ->toMatch('/01<\/span>.*Find<\/h2>.*One\.<\/p>.*02<\/span>.*Run<\/h2>.*Two\.<\/p>/s')
        ->not->toContain('variant="lede"');
});

test('features take as many columns as they are, and the slot is the lede', function () {
    $html = Blade::render('<x-site.sections.features eyebrow="Offer" title="Two." :features="[[\'A\', \'a.\'], [\'B\', \'b.\']]">Why.</x-site.sections.features>', deleteCachedView: true);

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

test('a split sets its figure beside the words, and leaves out what it was not given', function () {
    $html = Blade::render('<x-site.sections.split title="Two halves."><a href="#">Act</a><x-slot:figure><figure>F</figure></x-slot:figure></x-site.sections.split>', deleteCachedView: true);

    expect($html)->toMatch('/Two halves\.<\/h2>\s*<a href="#">Act<\/a>\s*<\/div>\s*<figure>F<\/figure>/')
        ->not->toContain('variant="label"')
        ->not->toContain('variant="lede"');
});
