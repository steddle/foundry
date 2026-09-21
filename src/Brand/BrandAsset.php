<?php

namespace Steddle\Foundry\Brand;

use Illuminate\Support\Facades\Blade;
use Illuminate\View\ComponentAttributeBag;

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
        return Blade::render('<x-dynamic-component :component="$component" {{ $attributes }} />', [
            'component' => $this->component,
            'attributes' => new ComponentAttributeBag($this->data),
        ]);
    }

    public function digest(): string
    {
        return hash('sha256', $this->markup());
    }
}
