<?php

namespace Steddle\Foundry\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Steddle\Foundry\Catalog\Catalog;

final class ShowComponent
{
    public function __invoke(Request $request, ?string $component = null): View
    {
        $from = in_array($request->query('from'), ['foundry', 'custom'], true) ? $request->query('from') : null;
        $entries = array_map(self::plain(...), match ($from) {
            'foundry' => Catalog::shared(),
            'custom' => Catalog::custom(),
            null => Catalog::all(),
        });
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
            'own' => $entry['from'] === 'foundry' && Catalog::held($entry['tag']),
        ]);
    }

    /**
     * The foundry skill renders the same prose as markdown, where backticks
     * mark code, so the catalogue keeps them and the page strips them.
     */
    private static function plain(array $entry): array
    {
        $strip = fn (array $row): array => [...$row, 'description' => str_replace('`', '', $row['description'])];

        return [...$strip($entry), 'props' => array_map($strip, $entry['props']), 'slots' => array_map($strip, $entry['slots'])];
    }
}
