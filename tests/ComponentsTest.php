<?php

use Illuminate\Support\Facades\Blade;

test('a site resolves the foundry components under x-site', function () {
    $html = Blade::render('<x-site.section id="band">Body</x-site.section>');

    expect($html)
        ->toContain('<section class="scroll-mt-4')
        ->toContain('id="band"')
        ->toContain('mx-auto w-full max-w-wide')
        ->toContain('Body');
});
