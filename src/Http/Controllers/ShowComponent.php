<?php

namespace Steddle\Foundry\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View as ViewFactory;
use Steddle\Foundry\Catalog\Catalog;

final class ShowComponent
{
    /**
     * `?custom` narrows the index to the imprint's own components.
     */
    public function __invoke(Request $request, ?string $component = null): View
    {
        $custom = $request->boolean('custom');
        $entries = $custom ? Catalog::custom() : Catalog::all();
        $slug = $component ?? array_key_first($entries) ?? abort(404);
        $entry = $entries[$slug] ?? abort(404);
        $query = $custom ? ['custom' => 1] : [];

        $groups = collect(Catalog::groups($entries))->map(fn (array $slugs): array => array_map(fn (string $item): array => [
            'label' => $entries[$item]['name'],
            'href' => route('foundry.components', [$item, ...$query]),
            'current' => $item === $slug,
        ], $slugs))->all();

        return view('foundry::catalog.show', [
            'slug' => $slug,
            'entry' => $entry,
            'groups' => $groups,
            'custom' => $custom,
            'hasCustom' => Catalog::custom() !== [],
            'own' => $entry['from'] === 'foundry' ? $this->own($slug) : null,
        ]);
    }

    /**
     * How the imprint holds a foundry component: `wraps` where its own file
     * hands content to x-foundry::site.*, `copies` where the file stands in
     * for the foundry's, the thing /components exists to show, and null where
     * it keeps no file of its own.
     */
    private function own(string $slug): ?string
    {
        if (! ViewFactory::exists("components.site.{$slug}")) {
            return null;
        }

        $source = file_get_contents(ViewFactory::getFinder()->find("components.site.{$slug}"));

        return str_contains($source, "x-foundry::site.{$slug}") ? 'wraps' : 'copies';
    }
}
