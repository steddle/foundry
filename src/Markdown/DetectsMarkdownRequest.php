<?php

namespace Steddle\Foundry\Markdown;

use Illuminate\Http\Request;
use Spatie\MarkdownResponse\Actions\DetectsMarkdownRequest as BaseDetector;
use Spatie\MarkdownResponse\Enums\DetectionMethod;

/**
 * Never answers a HEAD request in markdown. Laravel empties a HEAD response's
 * body before any middleware sees it, so the package would convert and cache
 * an empty page, and every GET for that address would then get the empty
 * page from the cache for the cache's lifetime.
 */
final class DetectsMarkdownRequest extends BaseDetector
{
    public function __invoke(Request $request): ?DetectionMethod
    {
        return $request->isMethod('HEAD') ? null : parent::__invoke($request);
    }
}
