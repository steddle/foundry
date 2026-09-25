<?php

namespace Steddle\Foundry\Console;

use GdImage;
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
        {--check : Fail where an image or file was made from other copy, markup or colours than the imprint states now}
        {--url= : Where the site answers, when APP_URL is not it (https://steddle.test)}';

    protected $description = 'Render the imprint\'s images and icons into public/';

    /** The sizes favicon.ico carries, cut from icon-512.png. */
    private const ICO_SIZES = [16, 32, 48];

    public function handle(): int
    {
        return $this->option('check') ? $this->check() : $this->render();
    }

    private function render(): int
    {
        // A site's node_modules, or the foundry's own where the workbench renders Foundry's images.
        if (! is_file(base_path('node_modules/playwright/package.json')) && ! is_file(__DIR__.'/../../node_modules/playwright/package.json')) {
            $this->error('Playwright renders the images: npm install --save-dev playwright && npx playwright install chromium');

            return self::FAILURE;
        }

        $base = rtrim($this->option('url') ?: config('app.url'), '/');
        $jobs = [];

        foreach (BrandAssets::all() as $asset) {
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

            File::ensureDirectoryExists(dirname(public_path($asset->path)));
            $jobs[] = ['url' => $url, 'path' => public_path($asset->path), 'width' => $asset->width, 'height' => $asset->height, 'transparent' => $asset->transparent];
        }

        $jobFile = tempnam(sys_get_temp_dir(), 'foundry-jobs');
        File::put($jobFile, json_encode($jobs));

        $result = Process::path(base_path())->timeout(300)->run(['node', __DIR__.'/../../bin/render.mjs', $jobFile]);
        File::delete($jobFile);

        if (! $result->successful()) {
            $this->error('Playwright did not render the images. '.$result->errorOutput());

            return self::FAILURE;
        }

        foreach (BrandAssets::files() as $path => $contents) {
            File::ensureDirectoryExists(dirname(public_path($path)));
            File::put(public_path($path), $contents);
        }

        File::put(public_path('favicon.ico'), $this->ico(public_path('icon-512.png')));

        $manifest = collect(BrandAssets::all())->map(fn (BrandAsset $asset): string => $asset->digest())->all();
        File::put(public_path(BrandAssets::MANIFEST), json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");

        foreach ([...array_map(fn (BrandAsset $asset): string => $asset->path, BrandAssets::all()), ...array_keys(BrandAssets::files()), 'favicon.ico'] as $path) {
            $this->line($path);
        }

        return self::SUCCESS;
    }

    private function check(): int
    {
        $manifest = BrandAssets::manifest();

        $stale = collect(BrandAssets::all())
            ->reject(fn (BrandAsset $asset): bool => ($manifest[$asset->name] ?? null) === $asset->digest() && is_file(public_path($asset->path)))
            ->keys()
            ->merge(collect(BrandAssets::files())->reject(fn (string $contents, string $path): bool => is_file(public_path($path)) && File::get(public_path($path)) === $contents)->keys())
            ->when(! is_file(public_path('favicon.ico')), fn ($stale) => $stale->push('favicon.ico'));

        if ($stale->isEmpty()) {
            return self::SUCCESS;
        }

        $this->error('Made from other copy, markup or colours than the imprint states: '.$stale->implode(', ').'. Run php artisan foundry:assets.');

        return self::FAILURE;
    }

    /** Every browser that still asks for favicon.ico reads PNG entries. */
    private function ico(string $source): string
    {
        $image = imagecreatefrompng($source);
        $entries = '';
        $payload = '';
        $offset = 6 + 16 * count(self::ICO_SIZES);

        foreach (self::ICO_SIZES as $size) {
            $png = $this->png($image, $size);
            $entries .= pack('CCCCvvVV', $size, $size, 0, 0, 1, 32, strlen($png), $offset);
            $payload .= $png;
            $offset += strlen($png);
        }

        return pack('vvv', 0, 1, count(self::ICO_SIZES)).$entries.$payload;
    }

    private function png(GdImage $source, int $size): string
    {
        $image = imagecreatetruecolor($size, $size);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagecopyresampled($image, $source, 0, 0, 0, 0, $size, $size, imagesx($source), imagesy($source));

        ob_start();
        imagepng($image);

        return ob_get_clean();
    }
}
