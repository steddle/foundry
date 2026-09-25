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
@elseif ($component['held'])
✗ {!! $name !!} keeps a file of its own in place of the foundry's, in `resources/views/foundry/`. Hand the foundry's component what it needs where the site uses it.
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
