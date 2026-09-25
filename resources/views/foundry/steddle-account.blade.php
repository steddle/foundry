@props(['href' => null])

{{--
    The settings page's way to the Steddle account, on a foundry:panel: the
    name, the address, the passkeys, leaving the imprint, signing out
    everywhere and deleting the account all live there.

    @group Forms
    @prop href The account's page; without one, `imprint.account.server` + `/account`, https://account.steddle.com by default.

    @example On a settings page
    <foundry:steddle-account href="https://account.steddle.com/account" class="max-w-3xl" />
--}}
@php
    // Resolved here, not in @props: the catalogue and the skill read a default in @props as the machine's live URL.
    $href ??= \Steddle\Foundry\Account::server('account');
@endphp
<foundry:panel :title="__('foundry::account.settings.title')" :lead="__('foundry::account.settings.lead', ['name' => config('imprint.name')])" {{ $attributes->class('items-start') }}>
    <foundry:button :$href variant="secondary" icon="arrow-up-right">{{ __('foundry::account.settings.manage') }}</foundry:button>
</foundry:panel>
