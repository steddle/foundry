@props(['heading', 'expanded' => true, 'remember' => null, 'open' => false])

{{--
    Links in foundry:app.sidebar that fold under a heading, over
    flux:sidebar.group. With `remember`, the group opens as the reader last
    left it: each toggle writes its state to the `foundry_sidebar` cookie,
    which the page reads before it renders, so it paints folded or open
    without a flash, and across wire:navigate.

    @prop heading What the group is called, and the button that folds it.
    @prop expanded Whether it opens unfolded, where the reader has not folded or opened it before.
    @prop remember A key for the group, such as `company`, under which the reader's choice is kept; without one the group opens as `expanded` says every time.
    @prop open Opens the group whatever the reader chose, for the group that holds the current page.
--}}
@php
    // The cookie is the browser's to write, so it is read as JSON of key => bool and anything else is left out.
    $state = $remember === null ? null : json_decode((string) request()->cookie('foundry_sidebar'), true);
    $remembered = is_array($state) ? ($state[$remember] ?? null) : null;

    $expanded = $open || (is_bool($remembered) ? $remembered : $expanded);

    // Flux fires the change before it sets data-open, so the hook reads the state from the element's value.
    if ($remember !== null) {
        $attributes = $attributes->merge(['x-on:lofi-disclosable-change.self' => implode(' ', [
            'let state = {};',
            "try { state = JSON.parse(decodeURIComponent(document.cookie.match(/(?:^|; )foundry_sidebar=([^;]*)/)?.[1] ?? '{}')) } catch {}",
            "if (state === null || typeof state !== 'object' || Array.isArray(state)) state = {};",
            'state['.\Illuminate\Support\Js::from($remember).'] = $el.value;',
            "document.cookie = 'foundry_sidebar=' + encodeURIComponent(JSON.stringify(state)) + '; path=/; max-age=31536000; samesite=lax';",
        ])]);
    }
@endphp

<flux:sidebar.group expandable :$heading :$expanded {{ $attributes->class('grid sidebar-fold') }}>
    {{ $slot }}
</flux:sidebar.group>
