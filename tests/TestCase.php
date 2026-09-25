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
     * Set from a test file's beforeAll(): the foundry reads some of its
     * config, `imprint.onboarding` among it, only as it boots.
     *
     * @var array<string, mixed>
     */
    public static array $config = [];

    /** @var list<class-string> */
    public static array $providers = [];

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
        return [...static::$providers, MarkdownResponseServiceProvider::class, FoundryServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('markdown-response.detection.detector', DetectsMarkdownRequest::class);
        $app['config']->set('markdown-response.preprocessors', [RemoveMarkdownSkipPreprocessor::class]);
        $app['config']->set('markdown-response.cache.store', 'array');
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('k', 32)));
    }

    /**
     * Set as the application's config is loaded, before any provider
     * registers: defineEnvironment() runs too late for what the foundry reads
     * in register(), Passport's middleware among it.
     */
    protected function resolveApplicationConfiguration($app): void
    {
        parent::resolveApplicationConfiguration($app);

        foreach (static::$config as $key => $value) {
            $app['config']->set($key, $value);
        }
    }
}
