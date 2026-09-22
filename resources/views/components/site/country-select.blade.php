@props(['countries', 'placeholder' => null])

{{--
    A searchable Flux listbox of countries, each with its flag. Every other
    attribute, a label or a wire:model, goes to flux:select.

    @group Forms
    @prop countries Lowercase ISO 3166-1 alpha-2 code, which flux:flag reads, => name, as the site has them in its own language. The code is the option's value.
    @prop placeholder What the empty select shows; without one, `foundry::forms.country`.

    @example Three countries
    <x-site.country-select :countries="['nl' => 'Netherlands', 'gb' => 'United Kingdom', 'us' => 'United States']" label="Country" class="max-w-sm" />
--}}
@php
    $placeholder ??= __('foundry::forms.country');
@endphp

<flux:select variant="listbox" searchable :$placeholder {{ $attributes }}>
    @foreach ($countries as $id => $name)
        <flux:select.option :value="$id">
            <span class="flex items-center gap-2.5">
                <flux:flag :country="$id" size="xs" />
                {{ $name }}
            </span>
        </flux:select.option>
    @endforeach
</flux:select>
