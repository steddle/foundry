@use('Illuminate\Support\Facades\Blade')
@use('Steddle\Foundry\Brand\Family')

@php
    $name = config('imprint.name');
    $design = config('imprint.design', []);
    $accent = $design['accent'] ?? 'Accent';
    $logo = fn (string $file): ?string => is_file(public_path("brand/logos/{$file}")) ? "/brand/logos/{$file}" : null;

    // A step does the same job in every ramp and every imprint: 300 the colour, 700 text on bone, 50 a wash.
    $ramps = [
        ['zinc', $design['ramps']['zinc'] ?? 'Grey', [25 => 'Cards and panels', 50 => 'Bone: the page; text on ink', 100 => 'Sunken bands, table heads', 200 => 'Rules on bone; body text on ink', 400 => 'Muted text on ink', 500 => 'Control borders in both themes', 600 => 'Muted text on bone', 700 => 'Rules on ink', 800 => 'Body text on bone; cards on ink', 900 => 'Ink: heroes, nav, footer', 950 => 'Headings on bone']],
        ['primary', $design['ramps']['primary'] ?? $accent, [50 => 'Wash on bone', 200 => 'Hover on ink', 300 => 'The fill, and text on ink', 700 => 'Text on bone: links, eyebrows', 800 => 'Hover on bone']],
        ['secondary', 'Lichen, the family', [300 => 'The marker']],
        ['info', 'Slate', [50 => 'Badge ground', 700 => 'Badge text']],
        ['success', 'Verify', [50 => 'Badge ground', 700 => 'Badge text']],
        ['warning', 'Amber', [50 => 'Badge ground', 700 => 'Badge text']],
        ['danger', 'Alarm', [50 => 'Badge ground', 400 => 'Errors on ink', 700 => 'Badge and error text']],
    ];

    // What a component writes out for each job, bone first and ink after `dark:`, and the swatch that shows it.
    $pairs = [
        ['Page', 'bg-zinc-50 dark:bg-zinc-900', 'bg-zinc-50 dark:bg-zinc-900'],
        ['Card', 'bg-zinc-25 dark:bg-zinc-800', 'bg-zinc-25 dark:bg-zinc-800'],
        ['Sunken band', 'bg-zinc-100 dark:bg-zinc-950', 'bg-zinc-100 dark:bg-zinc-950'],
        ['Ink band', 'bg-zinc-900 ink', 'bg-zinc-900'],
        ['Heading', 'text-zinc-950 dark:text-zinc-50', 'bg-zinc-950 dark:bg-zinc-50'],
        ['Running text', 'text-zinc-800 dark:text-zinc-200', 'bg-zinc-800 dark:bg-zinc-200'],
        ['Muted', 'text-zinc-600 dark:text-zinc-400', 'bg-zinc-600 dark:bg-zinc-400'],
        ['Accent and links', 'text-primary-700 dark:text-primary-300', 'bg-primary-700 dark:bg-primary-300'],
        ['Link hover', 'text-primary-800 dark:text-primary-200', 'bg-primary-800 dark:bg-primary-200'],
        ['Rule', 'border-zinc-200 dark:border-zinc-700', 'bg-zinc-200 dark:bg-zinc-700'],
        ['Control border', 'border-zinc-500', 'bg-zinc-500'],
    ];

    $sample = $design['type'] ?? [];
    $type = [
        ['display', 'Spectral 600 | 1.02 | -0.03em', 'font-serif text-display font-semibold text-zinc-950 dark:text-zinc-50', $sample['display'] ?? $name],
        ['heading-1', 'Spectral 600 | 1.04 | -0.03em', 'font-serif text-heading-1 font-semibold text-zinc-950 dark:text-zinc-50', $sample['heading-1'] ?? $name],
        ['heading-2', 'Spectral 600 | 1.15 | -0.02em', 'font-serif text-heading-2 font-semibold text-zinc-950 dark:text-zinc-50', $sample['heading-2'] ?? $name],
        ['heading-3', 'Spectral 600 | 1.25 | -0.01em', 'font-serif text-heading-3 font-semibold text-zinc-950 dark:text-zinc-50', $sample['heading-3'] ?? $name],
        ['lede', 'Chivo 400 | 1.62', 'text-lede text-zinc-800 dark:text-zinc-200', $sample['lede'] ?? $name],
        ['copy', 'Chivo 400 | 1.62', 'text-copy text-zinc-800 dark:text-zinc-200', $sample['copy'] ?? $name],
        ['small', 'Chivo 400 | 1.5', 'text-small text-zinc-600 dark:text-zinc-400', $sample['small'] ?? $name],
        ['label', 'Chivo 500 | sentence case', 'text-label text-primary-700 dark:text-primary-300', $sample['label'] ?? $name],
        ['meta', 'Chivo 500 | tabular', 'text-meta text-zinc-600 dark:text-zinc-400 tabular-nums', $sample['meta'] ?? $name],
        ['code', 'Chivo Mono 400 | 1.7 | what a reader could paste into a terminal', 'font-mono text-code text-zinc-950 dark:text-zinc-50 slashed-zero tabular-nums', $sample['code'] ?? 'php artisan foundry:assets'],
    ];

    $spacing = [
        ['4px', 'p-1', 'Icon to label'],
        ['8px', 'p-2', 'Label to field, tight stacks'],
        ['12px', 'p-3', 'Button gaps'],
        ['16px', 'p-4', 'Phone gutter'],
        ['24px', 'p-6', 'Card padding'],
        ['32px', 'p-8', 'Panel padding'],
        ['48px', 'p-12', 'Desktop gutter'],
        ['72px', 'p-18', 'Heading block to content'],
        ['128px', 'p-32', 'Section rhythm'],
    ];

    $radii = [
        ['radius-sm', '2px', 'rounded-sm', 'Badges, copy buttons'],
        ['radius-md', '3px', 'rounded-md', 'Buttons, cards, fields'],
        ['radius-lg', '5px', 'rounded-lg', 'Panels, the app-icon tile. The ceiling'],
    ];
@endphp

{{--
    One page for every imprint, in one order: the brand, the foundations, the
    imagery, then the controls in use. What is the imprint's own comes from
    config/imprint.php under `design`; a component of its own is shown on
    /components, never here.
--}}
<x-layouts::site :title="'Design | '.$name" :description="'The '.$name.' design system: the brand, the colours, the type and the controls, rendered from the code that ships them.'" flux>
    <x-site.page-title title="Design." lead="The brand, the colours, the type and the controls the site is built from, rendered from the code that ships them." />

    <x-site.numbered-section number="01" name="Brand" :note="$design['mark'] ?? null">
        <div class="flex flex-col gap-14">
            <div class="grid grid-cols-1 gap-px overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-200 dark:bg-zinc-700 sm:grid-cols-3">
                @foreach ([
                    ['mark-ink.svg', 'Ink, on bone', 'bone bg-zinc-50 text-zinc-900'],
                    ['mark-paper.svg', 'Bone, on ink', 'ink bg-zinc-900 text-zinc-50'],
                    ['mark-'.Str::slug($accent).'.svg', $accent.', on ink, as the accent', 'ink bg-zinc-900 text-primary-300'],
                ] as [$file, $label, $ground])
                    <div class="flex flex-col gap-6 p-6 {{ $ground }}">
                        <div class="flex items-end gap-6">
                            <x-site.mark class="size-20" />
                            <x-site.mark class="size-8" />
                            <x-site.mark class="size-4" />
                        </div>
                        <div class="flex items-baseline justify-between gap-4 text-small">
                            <span>{{ $label }}</span>
                            @if ($href = $logo($file))
                                <a href="{{ $href }}" download class="underline underline-offset-3 opacity-80 hover:opacity-100">{{ $file }}</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 gap-px overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-200 dark:bg-zinc-700 lg:grid-cols-2">
                @foreach ([['bone bg-zinc-50 text-zinc-900', 'lockup.svg', 'lockup-endorsed.svg'], ['ink bg-zinc-900 text-zinc-50', 'lockup-reversed.svg', 'lockup-endorsed-reversed.svg']] as [$ground, $plain, $endorsed])
                    <div class="flex flex-col items-start gap-8 p-8 {{ $ground }}">
                        <x-site.lockup class="h-12" />
                        @if ($logo($endorsed))
                            <x-site.lockup endorsed class="h-14" />
                        @endif
                        <p class="flex flex-wrap gap-x-2 text-small">
                            @foreach (array_filter([$plain => $logo($plain), $endorsed => $logo($endorsed)]) as $file => $href)
                                @unless ($loop->first)<span aria-hidden="true">|</span>@endunless
                                <a href="{{ $href }}" download class="underline underline-offset-3">{{ $file }}</a>
                            @endforeach
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
                @foreach (['Do' => ['✓', $design['do'] ?? []], 'Don\'t' => ['✗', $design['dont'] ?? []]] as $heading => [$glyph, $rules])
                    <div class="flex flex-col gap-4">
                        <x-site.heading size="3" level="3">{{ $heading }}</x-site.heading>
                        <ul role="list" class="flex flex-col gap-2 text-copy text-zinc-800 dark:text-zinc-200">
                            @foreach ($rules as $rule)
                                <li>{{ $glyph }} {{ $rule }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </x-site.numbered-section>

    <x-site.numbered-section number="02" name="Family" note="One structure, an ink and an accent each" class="bg-zinc-100 dark:bg-zinc-950">
        <div class="grid grid-cols-1 gap-px overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-200 dark:bg-zinc-700 lg:grid-cols-3">
            @foreach (Family::all() as $imprint)
                <div class="flex flex-col gap-8 p-8" style="background: {{ $imprint['ink'] }}">
                    <p class="flex items-center gap-3 font-serif text-2xl font-bold tracking-[-0.03em] text-zinc-50">
                        <svg viewBox="0 0 32 32" aria-hidden="true" class="size-7" style="color: {{ $imprint['accent'] }}"><path fill="currentColor" d="{{ $imprint['mark'] }}" /></svg>
                        {{ $imprint['name'] }}
                    </p>
                    <div class="flex items-center gap-3">
                        <span class="size-8 rounded-md ring-1 ring-zinc-50/20" style="background: {{ $imprint['ink'] }}"></span>
                        <span class="size-8 rounded-md" style="background: {{ $imprint['accent'] }}"></span>
                        <span class="text-small text-zinc-300">{{ $imprint['palette'] }}</span>
                    </div>
                    <p class="text-meta text-zinc-400">{{ $imprint['host'] }}</p>
                </div>
            @endforeach
        </div>
    </x-site.numbered-section>

    <x-site.numbered-section number="03" name="Colour" note="Seven ramps, 25 to 950">
        <div class="flex flex-col gap-12">
            @foreach ($ramps as [$ramp, $rampName, $jobs])
                <div class="flex flex-col gap-4">
                    <x-site.text variant="label" tone="muted">{{ $rampName }} | {{ $ramp }}</x-site.text>
                    <div class="grid grid-cols-4 gap-2 sm:grid-cols-6 lg:grid-cols-12">
                        @foreach ([25, 50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950] as $step)
                            <div class="flex flex-col gap-2">
                                <div class="h-12 rounded-md border border-zinc-950/12 dark:border-zinc-50/13" style="background: var(--color-{{ $ramp }}-{{ $step }})"></div>
                                <span class="text-meta text-zinc-950 dark:text-zinc-50 tabular-nums">{{ $step }}</span>
                                @isset($jobs[$step])
                                    <span class="text-meta text-zinc-600 dark:text-zinc-400">{{ $jobs[$step] }}</span>
                                @endisset
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="grid grid-cols-1 gap-px overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-200 dark:bg-zinc-700 lg:grid-cols-2">
                @foreach (['Bone' => 'bone bg-zinc-50', 'Ink' => 'ink bg-zinc-900'] as $theme => $ground)
                    <div class="flex flex-col gap-4 p-6 {{ $ground }}">
                        <x-site.text variant="label" tone="muted">{{ $theme }}</x-site.text>
                        <ul role="list" class="flex flex-col gap-3">
                            @foreach ($pairs as [$job, $classes, $swatch])
                                <li class="flex items-center gap-3">
                                    <span class="size-8 shrink-0 rounded-md border border-zinc-950/12 dark:border-zinc-50/13 {{ $swatch }}"></span>
                                    <span class="flex flex-col">
                                        <span class="text-small font-semibold text-zinc-950 dark:text-zinc-50">{{ $job }}</span>
                                        <code class="font-mono text-code text-zinc-600 dark:text-zinc-400 slashed-zero tabular-nums">{{ $classes }}</code>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </x-site.numbered-section>

    <x-site.numbered-section number="04" name="Type" note="Spectral, Chivo and Chivo Mono{{ isset($design['fonts']) ? ', and the imprint\'s own' : '' }}">
        <div class="flex flex-col gap-12">
            <x-site.text variant="lede" class="max-w-[60ch]">The scale is fluid: each size is set for a 390px phone and grows with the window. The size beside each step is what it measures at this width.</x-site.text>

            <div class="flex flex-col border-t border-zinc-200 dark:border-zinc-700">
                @foreach ($type as [$step, $spec, $classes, $text])
                    <div x-data="{ size: '' }" x-init="size = Math.round(parseFloat(getComputedStyle($refs.sample).fontSize)) + 'px'" class="grid gap-3 border-b border-zinc-200 dark:border-zinc-700 py-6 lg:grid-cols-[14rem_minmax(0,1fr)] lg:gap-10">
                        <div class="flex flex-col gap-1">
                            <span class="text-small font-semibold text-zinc-950 dark:text-zinc-50">{{ $step }}</span>
                            <span class="text-meta text-zinc-600 dark:text-zinc-400 tabular-nums"><span x-text="size"></span> | {{ $spec }}</span>
                        </div>
                        <p x-ref="sample" class="{{ $classes }}">{{ $text }}</p>
                    </div>
                @endforeach
                @foreach ($design['fonts'] ?? [] as $font)
                    <div class="grid gap-3 border-b border-zinc-200 dark:border-zinc-700 py-6 lg:grid-cols-[14rem_minmax(0,1fr)] lg:gap-10">
                        <div class="flex flex-col gap-1">
                            <span class="text-small font-semibold text-zinc-950 dark:text-zinc-50">{{ $font['name'] }}</span>
                            <span class="text-meta text-zinc-600 dark:text-zinc-400">{{ $font['spec'] }}</span>
                        </div>
                        <div>{!! Blade::render($font['blade']) !!}</div>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-col gap-4">
                <x-site.text variant="label" tone="muted">Marker: once per viewport, on ink</x-site.text>
                <div class="ink rounded-lg bg-zinc-900 p-8">
                    <p class="font-serif text-heading-1 font-semibold text-zinc-950 dark:text-zinc-50"><x-site.marked :text="$design['marked'][0] ?? $name" :marked="$design['marked'][1] ?? null" /></p>
                </div>
            </div>
        </div>
    </x-site.numbered-section>

    <x-site.numbered-section number="05" name="Space, radius, shadow" note="Radii stop at 5px" class="ink bg-zinc-900">
        <div class="grid grid-cols-1 gap-12 lg:grid-cols-2">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Value</flux:table.column>
                    <flux:table.column>Class</flux:table.column>
                    <flux:table.column>Use</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($spacing as [$value, $class, $use])
                        <flux:table.row>
                            <flux:table.cell class="tabular-nums">
                                <span class="inline-flex items-center gap-3"><span class="h-2 shrink-0 bg-primary-300" style="width: {{ $value }}"></span>{{ $value }}</span>
                            </flux:table.cell>
                            <flux:table.cell><code class="font-mono text-code slashed-zero tabular-nums">{{ $class }}</code></flux:table.cell>
                            <flux:table.cell>{{ $use }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            <div class="flex flex-col gap-10">
                <div class="flex flex-wrap gap-8">
                    @foreach ($radii as [$radius, $px, $class, $use])
                        <div class="flex flex-col items-start gap-2">
                            <span class="size-16 border border-zinc-50/13 bg-zinc-800 {{ $class }}"></span>
                            <span class="text-meta text-zinc-50 tabular-nums">{{ $radius }} | {{ $px }}</span>
                            <span class="max-w-[16ch] text-meta text-zinc-400">{{ $use }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="flex flex-col items-start gap-2">
                    <span class="size-16 rounded-md bg-primary-300 shadow-press"></span>
                    <span class="text-meta text-zinc-50">shadow-press</span>
                    <span class="max-w-[24ch] text-meta text-zinc-400">A button while it is pressed. Hairlines, not shadows, lift everything else.</span>
                </div>
            </div>
        </div>
    </x-site.numbered-section>

    <x-site.numbered-section number="06" name="Scenes" :note="count(config('imprint.scenes', [])).' photos, one per ink band'">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (array_keys(config('imprint.scenes', [])) as $scene)
                <figure class="flex flex-col gap-2">
                    <img src="/{{ $scene }}/home-768.webp" alt="" class="aspect-[1672/941] w-full rounded-md object-cover" loading="lazy">
                    <figcaption class="flex justify-between gap-4 text-meta text-zinc-600 dark:text-zinc-400">
                        <span>{{ $design['scenes'][$scene] ?? $scene }}</span>
                        <span>public/{{ $scene }}</span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </x-site.numbered-section>

    <x-site.numbered-section number="07" name="App icons" note="Ink tile, bone mark at 74%" class="ink bg-zinc-900">
        <x-site.brand-assets group="icon" />
    </x-site.numbered-section>

    <x-site.numbered-section number="08" name="Social images" note="Rendered by foundry:assets">
        <x-site.brand-assets />
    </x-site.numbered-section>

    <x-site.numbered-section number="09" name="Buttons" note="One primary per viewport">
        <div class="grid grid-cols-1 gap-px overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-200 dark:bg-zinc-700 lg:grid-cols-2">
            <div class="flex flex-col gap-4 bg-zinc-25 dark:bg-zinc-800 p-6">
                <x-site.text variant="label" tone="muted">On a card: primary and secondary</x-site.text>
                <x-site.actions>
                    <x-site.button href="#">Primary</x-site.button>
                    <x-site.button href="#" variant="secondary">Secondary</x-site.button>
                    <x-site.button variant="secondary" disabled>Disabled</x-site.button>
                </x-site.actions>
            </div>
            <div class="ink flex flex-col gap-4 bg-zinc-900 p-6">
                <x-site.text variant="label" tone="muted">On ink: primary and ghost</x-site.text>
                <x-site.actions>
                    <x-site.button href="#">Primary</x-site.button>
                    <x-site.button href="#" variant="ghost">Ghost</x-site.button>
                </x-site.actions>
            </div>
        </div>
    </x-site.numbered-section>

    <x-site.numbered-section number="10" name="Forms" note="livewire/flux: every control is Flux, never a native one">
        <div class="grid grid-cols-1 gap-x-8 gap-y-6 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-25 dark:bg-zinc-800 p-6 sm:p-8 md:grid-cols-2">
            <flux:input label="Full name" placeholder="Ada Visser" icon="user" />

            <flux:field>
                <flux:label>Email address</flux:label>
                <flux:input type="email" value="ada@" aria-invalid="true" data-invalid />
                <flux:error message="Add the part after the @ in full, like name@example.com." />
            </flux:field>

            <flux:date-picker label="Date" description:trailing="The day it applied, not the day it was published." placeholder="Pick a day" with-today selectable-header clearable />

            <flux:select variant="combobox" label="Country" placeholder="Start typing a country">
                <flux:select.option value="NL">Netherlands</flux:select.option>
                <flux:select.option value="BE">Belgium</flux:select.option>
                <flux:select.option value="DE">Germany</flux:select.option>
            </flux:select>

            <flux:select variant="listbox" label="How many" placeholder="Choose">
                <flux:select.option>8</flux:select.option>
                <flux:select.option>20</flux:select.option>
                <flux:select.option>50</flux:select.option>
            </flux:select>

            <flux:radio.group variant="segmented" size="sm" label="Sign as">
                <flux:radio value="person" label="A person" checked />
                <flux:radio value="company" label="A company" />
            </flux:radio.group>

            <div class="md:col-span-2">
                <flux:textarea label="Purpose" badge="Optional" rows="2" placeholder="One line" description="A description under the field says what it is for." />
            </div>

            <div class="md:col-span-2">
                <flux:radio.group label="Choose one" variant="cards" class="flex-col">
                    <flux:radio value="one" label="The first choice" description="A line on what it means." checked />
                    <flux:radio value="two" label="The second choice" description="A line on what it means." />
                </flux:radio.group>
            </div>

            <flux:checkbox variant="cards" label="An option" description="A line on what it adds." checked />
            <flux:checkbox variant="cards" label="An option that is not open" description="A line on why it is not." disabled />

            <flux:field variant="inline" class="md:col-span-2">
                <flux:checkbox checked />
                <flux:label>I have read the terms and agree to them.</flux:label>
            </flux:field>
        </div>
    </x-site.numbered-section>

    <x-site.numbered-section number="11" name="Feedback" note="What the site tells a reader">
        <div class="flex flex-col gap-8">
            <div class="flex flex-wrap items-center gap-2.5">
                <x-site.badge>Neutral</x-site.badge>
                <x-site.badge tone="info">Info</x-site.badge>
                <x-site.badge tone="success">Success</x-site.badge>
                <x-site.badge tone="warning">Warning</x-site.badge>
                <x-site.badge tone="danger">Danger</x-site.badge>
                <x-site.badge tone="action" dot>Action</x-site.badge>
            </div>

            <flux:callout icon="information-circle" class="max-w-[66ch]">
                <flux:callout.heading>A callout</flux:callout.heading>
                <flux:callout.text>What a reader should know before going on, in a sentence or two.</flux:callout.text>
            </flux:callout>

            <div>
                <flux:modal.trigger name="design-modal">
                    <x-site.button variant="secondary">Open a modal</x-site.button>
                </flux:modal.trigger>
                <flux:modal name="design-modal" class="max-w-md">
                    <div class="flex flex-col gap-6">
                        <x-site.heading size="2" level="2">A modal</x-site.heading>
                        <x-site.text>One decision, and the two ways out of it.</x-site.text>
                        <x-site.actions>
                            <flux:modal.close>
                                <x-site.button variant="secondary">Cancel</x-site.button>
                            </flux:modal.close>
                            <x-site.button>Confirm</x-site.button>
                        </x-site.actions>
                    </div>
                </flux:modal>
            </div>
        </div>
    </x-site.numbered-section>
</x-layouts::site>
