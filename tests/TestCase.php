<?php

namespace Steddle\Foundry\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\MarkdownResponse\MarkdownResponseServiceProvider;
use Steddle\Foundry\FoundryServiceProvider;
use Steddle\Foundry\Markdown\DetectsMarkdownRequest;
use Steddle\Foundry\Markdown\RemoveMarkdownSkipPreprocessor;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [MarkdownResponseServiceProvider::class, FoundryServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('markdown-response.detection.detector', DetectsMarkdownRequest::class);
        $app['config']->set('markdown-response.preprocessors', [RemoveMarkdownSkipPreprocessor::class]);
        $app['config']->set('markdown-response.cache.store', 'array');
    }
}
