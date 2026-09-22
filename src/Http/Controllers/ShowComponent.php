<?php

namespace Steddle\Foundry\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View as ViewFactory;
use Illuminate\Support\Str;
use Steddle\Foundry\Catalog\Catalog;

final class ShowComponent
{
    /**
     * Without a component the index, every group with each component's
     * description. `?from=foundry` narrows it to the foundry's components,
     * and `?from=custom` to the imprint's own.
     */
    public function __invoke(Request $request, ?string $component = null): View
    {
        $from = in_array($request->query('from'), ['foundry', 'custom'], true) ? $request->query('from') : null;
        $entries = match ($from) {
            'foundry' => Catalog::shared(),
            'custom' => Catalog::custom(),
            null => Catalog::all(),
        };
        $entry = $component === null ? null : ($entries[$component] ?? abort(404));
        $query = $from ? ['from' => $from] : [];

        $index = Catalog::groups($entries);
        $hasCustom = Catalog::custom() !== [];

        $groups = collect($index)->map(fn (array $slugs): array => array_map(fn (string $item): array => [
            'label' => $entries[$item]['name'],
            'href' => route('foundry.components', [$item, ...$query]),
            'current' => $item === $component,
        ], $slugs))->all();

        if ($entry === null) {
            return view('foundry::catalog.index', [
                'groups' => $groups,
                'index' => $index,
                'entries' => $entries,
                'query' => $query,
                'from' => $from,
                'hasCustom' => $hasCustom,
            ]);
        }

        return view('foundry::catalog.show', [
            'slug' => $component,
            'entry' => $entry,
            'groups' => $groups,
            'from' => $from,
            'hasCustom' => $hasCustom,
            'own' => $entry['from'] === 'foundry' ? $this->own($entry['tag']) : null,
        ]);
    }

    /**
     * How the imprint holds a foundry component: `wraps` where its own file
     * hands content to x-foundry::site.*, `copies` where the file stands in
     * for the foundry's, the thing /components exists to show, and null where
     * it keeps no file of its own.
     */
    private function own(string $tag): ?string
    {
        $name = Str::after($tag, 'x-site.');

        if (! ViewFactory::exists("components.site.{$name}")) {
            return null;
        }

        $source = file_get_contents(ViewFactory::getFinder()->find("components.site.{$name}"));

        return str_contains($source, "x-foundry::site.{$name}") ? 'wraps' : 'copies';
    }
}
