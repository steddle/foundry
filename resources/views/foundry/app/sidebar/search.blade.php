@props(['placeholder' => null, 'kbd' => null])

{{--
    The button at the top of foundry:app.sidebar that opens the imprint's
    search, over flux:sidebar.search. It opens nothing itself: the imprint
    hands it the action, `x-on:click` or `wire:click`, and binds the shortcut.

    @prop placeholder Its label; without one, `foundry::nav.search`.
    @prop kbd The shortcut shown at its end, such as `⌘K`.
--}}
<flux:sidebar.search :placeholder="$placeholder ?? __('foundry::nav.search')" :$kbd {{ $attributes }} />
