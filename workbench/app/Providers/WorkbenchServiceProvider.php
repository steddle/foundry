<?php

namespace Workbench\App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

use function Orchestra\Testbench\workbench_path;

/**
 * Foundry as an imprint of its own, so foundry:assets renders its README
 * banners, social preview and icons the way it renders every site's.
 */
class WorkbenchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->usePublicPath(workbench_path('public'));

        config(['imprint' => require workbench_path('config/imprint.php')]);
    }

    public function boot(): void
    {
        Blade::anonymousComponentPath(workbench_path('resources/views/components'));
    }
}
