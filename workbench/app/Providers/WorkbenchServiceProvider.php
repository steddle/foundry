<?php

namespace Workbench\App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

use function Orchestra\Testbench\workbench_path;

/**
 * Foundry as an imprint of its own, so foundry:assets renders its README
 * banners, social preview and icons the way it renders every site's, and
 * `testbench serve` shows the lab, a signed-in page under the sidebar at
 * /app, and the documents Printer prints at /print/long and /print/short.
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
        Blade::anonymousComponentPath(workbench_path('resources/views/foundry'), 'foundry');
        Blade::anonymousComponentPath(workbench_path('resources/views/layouts'), 'layouts');
        $this->loadViewsFrom(workbench_path('resources/views'), 'workbench');

        Route::view('app', 'workbench::app')->middleware('web');
        Route::get('print/{document}', fn (string $document) => view("workbench::print.{$document}", [
            'body' => Str::markdown(file_get_contents(workbench_path("resources/markdown/{$document}.md"))),
        ]))->whereIn('document', ['long', 'short'])->middleware('web');
    }
}
