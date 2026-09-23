@props(['code', 'title' => null, 'lead' => null])

{{--
    An error page, on foundry:layouts.auth: its code, its title and its
    lead, and a way on. The words are `foundry::errors`, which an imprint
    words for itself in lang/vendor/foundry/{locale}/errors.php, and the scene
    is the one config/imprint.php names under `errors.scene`, grain and the
    mark without one. The foundry answers 403, 404, 419, 429, 500 and 503 with
    it; an imprint's own resources/views/errors/{code}.blade.php stands in for
    one whose actions are its own.

    @group Shell
    @prop code The HTTP status.
    @prop title The page's heading; without one, `foundry::errors.{code}.title`.
    @prop lead The line under it; without one, `foundry::errors.{code}.lead`.
    @slot actions The buttons in place of the one the foundry sets: home, or trying again on a 503.

    @example A page that is not there
    @code
    <foundry:layouts.error code="404">
        <x-slot:actions>
            <foundry:button :href="route('search')">Search</foundry:button>
        </x-slot:actions>
    </foundry:layouts.error>
--}}
@php
    $name = config('imprint.name');
    $title ??= __("foundry::errors.{$code}.title", ['name' => $name]);
    $lead ??= __("foundry::errors.{$code}.lead", ['name' => $name]);
@endphp

<foundry:layouts.auth :title="rtrim($title, '.')" :description="$lead" :scene="config('imprint.errors.scene')">
    <foundry:text variant="label" tone="accent">{{ __('foundry::errors.label', ['code' => $code]) }}</foundry:text>
    <foundry:heading size="1" level="1" class="max-w-[20ch]">{{ $title }}</foundry:heading>
    <foundry:text variant="lede" class="max-w-[48ch]">{{ $lead }}</foundry:text>
    <foundry:actions>
        @isset($actions)
            {{ $actions }}
        @elseif ((int) $code === 503)
            <foundry:button :href="url()->full()">{{ __('foundry::errors.again') }}</foundry:button>
        @else
            <foundry:button :href="\Steddle\Foundry\Locales::multilingual() ? localized_route('home') : url('/')">{{ __('foundry::errors.home', ['name' => $name]) }}</foundry:button>
        @endisset
    </foundry:actions>
</foundry:layouts.auth>
