@props(['countries', 'placeholder' => null])

@php
    $placeholder ??= __('foundry::forms.country');
@endphp

{{--
    A searchable Flux listbox of countries, each with its flag. `$countries`:
    lowercase ISO 3166-1 alpha-2, which flux:flag reads, => name, as the site
    has them in its own language.
--}}
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
