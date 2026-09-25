<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Process;

/** Tailwind's own compiler, on the foundry's stylesheet alone, with the utilities named. */
function compiled(array $utilities): string
{
    $script = <<<'JS'
        import { compile } from '@tailwindcss/node';
        const compiler = await compile("@import 'tailwindcss'; @import './resources/css/foundry.css';", { base: process.cwd(), onDependency() {} });
        process.stdout.write(compiler.build(JSON.parse(process.argv[1])));
        JS;

    $result = Process::path(dirname(__DIR__))->run(['node', '--input-type=module', '-e', $script, json_encode($utilities)]);

    expect($result->successful())->toBeTrue($result->errorOutput());

    return $result->output();
}

test('zinc, primary and the names that resolve through them are utilities that read the variables a palette sets', function () {
    $css = compiled(['bg-zinc-800', 'text-zinc-25', 'bg-primary-300', 'text-primary-700', 'text-gray-500', 'border-neutral-200']);

    expect($css)->toContain('background-color: var(--color-zinc-800)')
        ->toContain('color: var(--color-zinc-25)')
        ->toContain('background-color: var(--color-primary-300)')
        ->toContain('color: var(--color-primary-700)')
        ->toContain('color: var(--color-gray-500)')
        ->toContain('--color-gray-500: var(--color-zinc-500)')
        ->toContain('--color-accent: var(--color-primary-300)');
});

test('every palette sets all of zinc and primary, on the root element', function (string $palette) {
    preg_match("/:root\[data-palette='{$palette}'\] \{([^}]*)\}/", compiled([]), $block);

    expect($block)->not->toBeEmpty();

    foreach (['25', '50', '100', '200', '300', '400', '500', '600', '700', '800', '900', '950'] as $step) {
        expect($block[1])->toContain("--color-zinc-{$step}:")->toContain("--color-primary-{$step}:");
    }
})->with(['steddle', 'sendnda', 'bron', 'righted']);

test('a page names the imprint\'s palette on its root element, and none where the imprint sets none', function () {
    $this->withoutVite();
    config(['imprint.name' => 'Imprint', 'imprint.stylesheet' => null]);
    $page = fn (): string => Blade::render('<foundry:layouts.app title="A page"><p>The page.</p></foundry:layouts.app>', deleteCachedView: true);

    expect($page())->toContain('<html lang="en">');

    config(['imprint.palette' => 'righted']);

    expect($page())->toContain('<html lang="en" data-palette="righted">');
});
