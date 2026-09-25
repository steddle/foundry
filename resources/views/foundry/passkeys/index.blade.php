@props(['passkeys'])

{{--
    The signed-in reader's passkeys on an foundry:panel: each one with the
    authenticator that holds it, when it was added and last used, and a
    button to remove it, then a way to add one. The page's Livewire
    component uses Steddle\Foundry\Concerns\ManagesPasskeys, which holds
    $passkeys and answers loadPasskeys and deletePasskey. The script is
    the foundry's resources/js/passkeys.js, which the imprint's Vite builds.

    @group Forms
    @prop passkeys The component's `$passkeys`.

    @example On a settings page
    @code
    <foundry:passkeys :$passkeys />
--}}
<foundry:panel :title="__('foundry::passkeys.title')" :lead="__('foundry::passkeys.lead')" {{ $attributes }}>
    @if ($passkeys === [])
        <foundry:text variant="small" tone="muted">{{ __('foundry::passkeys.none') }}</foundry:text>
    @else
        <foundry:rows>
            @foreach ($passkeys as $passkey)
                <foundry:record-row wire:key="passkey-{{ $passkey['id'] }}" :title="$passkey['name']"
                    :meta="__('foundry::passkeys.added', ['time' => $passkey['created_at']]).($passkey['last_used_at'] ? ' | '.__('foundry::passkeys.used', ['time' => $passkey['last_used_at']]) : '')">
                    @if ($passkey['authenticator'])
                        <x-slot:status>
                            <foundry:badge>{{ $passkey['authenticator'] }}</foundry:badge>
                        </x-slot:status>
                    @endif

                    <x-slot:actions>
                        <foundry:confirm-button :label="__('foundry::passkeys.remove')" size="sm" action="$wire.deletePasskey({{ $passkey['id'] }})" />
                    </x-slot:actions>
                </foundry:record-row>
            @endforeach
        </foundry:rows>
    @endif

    <foundry:passkeys.register />
</foundry:panel>
