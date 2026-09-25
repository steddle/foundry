<?php

namespace Steddle\Foundry\Pdf;

use Illuminate\Support\Facades\Http;
use Spatie\Browsershot\Browsershot;

/**
 * The page sets its paper and margins itself, in the `@page` rule
 * `foundry:print.document` writes.
 */
class Printer
{
    /**
     * Stylesheets and fonts are pulled in: a stored document has to print the
     * same after its assets are replaced.
     */
    public function capture(string $url): string
    {
        // An error page would otherwise print, and be kept, as the document.
        return $this->inlineAssets($this->configure(Browsershot::url($url))->preventUnsuccessfulResponse()->bodyHtml());
    }

    public function print(string $html): string
    {
        return $this->configure(Browsershot::html($html))->pdf();
    }

    /**
     * The PDF is printed from a temporary file, so the page has no origin and
     * Chrome refuses cross-origin font files: unlinked, a font would print in
     * its fallback.
     */
    private function inlineAssets(string $html): string
    {
        // A <link rel="stylesheet" href="…">, rel before href as @vite writes it.
        $html = preg_replace_callback(
            '#<link[^>]+rel="stylesheet"[^>]+href="([^"]+)"[^>]*>#i',
            function (array $match): string {
                $css = $this->fetch($match[1]);

                return $css === null ? $match[0] : '<style>'.$this->inlineFonts($css).'</style>';
            },
            $html,
        );

        return $this->inlineFonts($html);
    }

    /**
     * Runs on the page itself as well as on each stylesheet, so an
     * `@font-face` in an inline <style> is caught too.
     */
    private function inlineFonts(string $css): string
    {
        // A url(…) to a font file by an absolute http(s) URL or a root-relative
        // path, as Vite writes them. `//host/…` is no path of this host's.
        return preg_replace_callback(
            '#url\((["\']?)((?:https?://|/(?!/))[^)"\']+\.(?:woff2|woff|ttf|otf))\1\)#i',
            function (array $match): string {
                $url = str_starts_with($match[2], '/') ? $this->origin().$match[2] : $match[2];
                $font = $this->fetch($url);

                if ($font === null) {
                    return $match[0];
                }

                return 'url(data:font/'.pathinfo($url, PATHINFO_EXTENSION).';base64,'.base64_encode($font).')';
            },
            $css,
        );
    }

    /**
     * A printed page can carry text its readers typed, so a url() in it could
     * send the server to an internal address. Only the imprint's own origin
     * is fetched, scheme and port included, and a redirect off it is refused.
     */
    private function fetch(string $url): ?string
    {
        if (! str_starts_with($url, $this->origin().'/')) {
            return null;
        }

        $response = Http::withOptions(['allow_redirects' => false])->timeout(10)->get($url);

        return $response->successful() ? $response->body() : null;
    }

    private function origin(): string
    {
        return rtrim(config('app.asset_url') ?? config('app.url'), '/');
    }

    private function configure(Browsershot $browsershot): Browsershot
    {
        return $browsershot
            ->setOption('preferCSSPageSize', true)
            ->showBackground()
            ->noSandbox()
            ->waitUntilNetworkIdle();
    }
}
