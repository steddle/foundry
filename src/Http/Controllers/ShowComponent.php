<?php

namespace Steddle\Foundry\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFactory;
use Steddle\Foundry\Catalog\Catalog;

final class ShowComponent
{
    public function __invoke(?string $component = null): View
    {
        $entries = Catalog::all();
        $slug = $component ?? array_key_first($entries);
        $entry = $entries[$slug] ?? abort(404);

        $groups = collect(Catalog::groups())->map(fn (array $slugs): array => array_map(fn (string $item): array => [
            'label' => $entries[$item]['name'],
            'href' => route('foundry.components', $item),
            'current' => $item === $slug,
        ], $slugs))->all();

        return view('foundry::catalog.show', [
            'slug' => $slug,
            'entry' => $entry,
            'groups' => $groups,
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
