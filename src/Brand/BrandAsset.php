<?php

namespace Steddle\Foundry\Brand;

use Illuminate\Support\Facades\Blade;

/**
 * One image an imprint publishes, rendered from a foundry component at a fixed
 * pixel size into `public/`.
 */
final readonly class BrandAsset
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public string $name,
        public string $path,
        public int $width,
        public int $height,
        public string $use,
        public string $component,
        public array $data,
        public string $group = 'social',
        public bool $transparent = false,
    ) {}

    public function markup(): string
    {
        // Each value is bound to a variable of its own, never written into the
        // markup: an attribute bag printed there escapes it once and the
        // component escapes it again, so an apostrophe would reach the image
        // as `&#039;`.
        $props = collect(array_keys($this->data))
            ->map(fn (string $name): string => ':'.$name.'="$'.$name.'"')
            ->implode(' ');

        return Blade::render('<x-dynamic-component :component="$component" '.$props.' />', [
            'component' => $this->component,
            ...$this->data,
        ]);
    }

    public function digest(): string
    {
        return hash('sha256', $this->markup());
    }
}
