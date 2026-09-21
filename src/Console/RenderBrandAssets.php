<?php

namespace Steddle\Foundry\Console;

use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Steddle\Foundry\Brand\BrandAsset;
use Steddle\Foundry\Brand\BrandAssets;

final class RenderBrandAssets extends Command
{
    protected $signature = 'foundry:assets
        {--check : Fail where an image was rendered from other copy or markup than the imprint states now}
        {--url= : Where the site answers, when APP_URL is not it (https://steddle.test)}
        {--chrome=/Applications/Google Chrome.app/Contents/MacOS/Google Chrome : The Chrome binary to render with}';

    protected $description = 'Render the imprint\'s OG image, social preview and README banners into public/';

    public function handle(): int
    {
        return $this->option('check') ? $this->check() : $this->render();
    }

    private function render(): int
    {
        $manifest = [];
        $base = rtrim($this->option('url') ?: config('app.url'), '/');

        foreach (BrandAssets::all() as $asset) {
            $target = public_path($asset->path);
            $url = $base.route('foundry.brand', $asset->name, absolute: false);

            // Chrome screenshots whatever answers, an error page included.
            try {
                $status = Http::withOptions(['verify' => false])->get($url)->status();
            } catch (ConnectionException) {
                $status = 'nothing';
            }

            if ($status !== 200) {
                $this->error("{$url} answered {$status}, not the page to render. Pass --url with the address the site answers on.");

                return self::FAILURE;
            }

            if (! is_dir(dirname($target))) {
                mkdir(dirname($target), 0755, true);
            }

            // Chrome reads the page through the site's own server, so the image
            // carries the stylesheet exactly as the site builds it.
            $result = Process::timeout(60)->run([
                $this->option('chrome'),
                '--headless=new',
                '--hide-scrollbars',
                '--force-device-scale-factor=1',
                '--virtual-time-budget=5000',
                "--window-size={$asset->width},{$asset->height}",
                "--screenshot={$target}",
                '--ignore-certificate-errors',
                $url,
            ]);

            if (! $result->successful() || ! is_file($target)) {
                $this->error("{$asset->name}: Chrome did not render it. {$result->errorOutput()}");

                return self::FAILURE;
            }

            $manifest[$asset->name] = $asset->digest();
            $this->line("{$asset->path} | {$asset->width}×{$asset->height}");
        }

        file_put_contents(public_path(BrandAssets::MANIFEST), json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

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
