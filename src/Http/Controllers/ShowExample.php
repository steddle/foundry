<?php

namespace Steddle\Foundry\Http\Controllers;

use Illuminate\Contracts\View\View;
use Steddle\Foundry\Catalog\Catalog;

/**
 * One example of a component alone on a page of its own, which /components
 * frames at a viewport's width, so a section meets its breakpoints as it
 * does on a page.
 */
final class ShowExample
{
    public function __invoke(string $component, int $example): View
    {
        $blade = Catalog::all()[$component]['examples'][$example]['blade'] ?? abort(404);

        return view('foundry::catalog.frame', ['blade' => $blade]);
    }
}
