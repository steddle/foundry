<?php

namespace Steddle\Foundry\Http\Controllers;

use Illuminate\Contracts\View\View;
use Steddle\Foundry\Brand\BrandAssets;

final class RenderBrandAsset
{
    public function __invoke(string $asset): View
    {
        $brandAsset = BrandAssets::find($asset) ?? abort(404);

        return view('foundry::brand.render', [
            'asset' => $brandAsset,
            'stylesheet' => config('imprint.stylesheet'),
        ]);
    }
}
