<?php

namespace Steddle\Foundry\Tests;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\MarkdownResponse\MarkdownResponseServiceProvider;
use Steddle\Foundry\FoundryServiceProvider;
use Steddle\Foundry\Markdown\DetectsMarkdownRequest;
use Steddle\Foundry\Markdown\RemoveMarkdownSkipPreprocessor;

abstract class TestCase extends Orchestra
{
    /**
     * The lockup and the mark every imprint supplies, which the foundry's
     * error pages draw; a test that needs its own writes over them.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $directory = resource_path('views/foundry');
        File::ensureDirectoryExists($directory);
        File::put($directory.'/lockup.blade.php', '<span {{ $attributes }}>Imprint</span>');
        File::put($directory.'/mark.blade.php', '<svg {{ $attributes }}></svg>');
    }

    protected function getPackageProviders($app): array
    {
        return [MarkdownResponseServiceProvider::class, FoundryServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('markdown-response.detection.detector', DetectsMarkdownRequest::class);
        $app['config']->set('markdown-response.preprocessors', [RemoveMarkdownSkipPreprocessor::class]);
        $app['config']->set('markdown-response.cache.store', 'array');
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('k', 32)));
    }
}
