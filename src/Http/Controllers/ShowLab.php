<?php

namespace Steddle\Foundry\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFactory;

/**
 * The lab: its index names the design page, the components and the imprint's
 * experiments from `imprint.labs`; an experiment is the imprint's view
 * `labs.{page}`.
 */
final class ShowLab
{
    public function __invoke(?string $page = null): View
    {
        if ($page === null) {
            return view('foundry::lab.index', ['pages' => config('imprint.labs', [])]);
        }

        abort_unless(ViewFactory::exists("labs.{$page}"), 404);

        return view("labs.{$page}");
    }
}
