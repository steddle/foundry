{{-- One group of the foundry skill's component reference, in markdown. Boost renders the skill's reference file, which renders this. --}}
@php
    $cell = fn (?string $text): string => str_replace(['|', "\n"], ['\|', ' '], (string) $text);
@endphp
# {!! $group !!} components

Every component in the {!! strtolower($group) !!} group that {!! $name !!} renders, the foundry's and its own. `/components` shows each live.
@foreach ($components as $component)

## `{!! $component['tag'] !!}`

@if ($component['from'] === 'custom')
{!! $name !!}'s own, in `{!! $component['file'] !!}`.
@elseif ($component['from'] === 'imprint')
Every imprint draws its own.
@elseif ($component['held'] === 'copies')
✗ {!! $name !!} keeps a copy of this one in place of the foundry's. Wrap `x-foundry::site.{!! \Illuminate\Support\Str::after($component['tag'], 'x-site.') !!}` in it instead.
@elseif ($component['held'] === 'wraps')
The foundry's, wrapped by {!! $name !!}'s own file.
@endif

{!! $component['description'] !!}
@if ($component['props'] !== [])

| Prop | Default | |
|---|---|---|
@foreach ($component['props'] as $prop)
| `{!! $prop['name'] !!}` | {!! match ($prop['default']) { null => 'required', 'passed on' => 'passed on', default => '`'.$cell($prop['default']).'`' } !!} | {!! $cell($prop['description']) !!} |
@endforeach
@endif
@if ($component['slots'] !== [])

@foreach ($component['slots'] as $slot)
- Slot `{!! $slot['name'] !!}`: {!! $slot['description'] !!}
@endforeach
@endif
@if ($component['example'])

```blade
{!! $component['example'] !!}
```
@endif
@endforeach
