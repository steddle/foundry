<?php

namespace Steddle\Foundry\Console;

use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Steddle\Foundry\Brand\BrandAsset;
use Steddle\Foundry\Brand\BrandAssets;

final class RenderBrandAssets extends Command
{
    protected $signature = 'foundry:assets
        {--check : Fail where an image was rendered from other copy or markup than the imprint states now}
        {--url= : Where the site answers, when APP_URL is not it (https://steddle.test)}';

    protected $description = 'Render the imprint\'s OG image, social preview and README banners into public/';

    public function handle(): int
    {
        return $this->option('check') ? $this->check() : $this->render();
    }

    private function render(): int
    {
        $manifest = [];
        $base = rtrim($this->option('url') ?: config('app.url'), '/');

        $playwright = base_path('node_modules/.bin/playwright');

        if (! is_file($playwright)) {
            $this->error('Playwright renders the images: npm install --save-dev playwright && npx playwright install chromium');

            return self::FAILURE;
        }

        foreach (BrandAssets::all() as $asset) {
            $target = public_path($asset->path);
            $url = $base.route('foundry.brand', $asset->name, absolute: false);

            // Playwright screenshots whatever answers, an error page included.
            try {
                $status = Http::withOptions(['verify' => false])->get($url)->status();
            } catch (ConnectionException) {
                $status = 'nothing';
            }

            if ($status !== 200) {
                $this->error("{$url} answered {$status}, not the page to render. Pass --url with the address the site answers on.");

                return self::FAILURE;
            }

            File::ensureDirectoryExists(dirname($target));

            // Playwright's own Chromium reads the page through the site's own
            // server, so the image carries the stylesheet exactly as the site
            // builds it, and the reader's browser is never touched.
            $result = Process::path(base_path())->timeout(60)->run([
                $playwright,
                'screenshot',
                "--viewport-size={$asset->width},{$asset->height}",
                '--ignore-https-errors',
                '--wait-for-timeout=500',
                $url,
                $target,
            ]);

            if (! $result->successful() || ! is_file($target)) {
                $this->error("{$asset->name}: Playwright did not render it. {$result->errorOutput()}");

                return self::FAILURE;
            }

            $manifest[$asset->name] = $asset->digest();
            $this->line("{$asset->path} | {$asset->width}×{$asset->height}");
        }

        File::put(public_path(BrandAssets::MANIFEST), json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

        return self::SUCCESS;
    }

    private function check(): int
    {
        $manifest = BrandAssets::manifest();

        $stale = collect(BrandAssets::all())
            ->reject(fn (BrandAsset $asset): bool => ($manifest[$asset->name] ?? null) === $asset->digest() && is_file(public_path($asset->path)))
            ->keys();

        if ($stale->isEmpty()) {
            return self::SUCCESS;
        }

        $this->error('Rendered from other copy than the imprint states: '.$stale->implode(', ').'. Run php artisan foundry:assets.');

        return self::FAILURE;
    }
}
