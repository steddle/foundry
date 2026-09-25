{{-- What the agent may do is the imprint's `may` and `note`. --}}
@php
    $name = \Steddle\Foundry\Mcp\Agents::name($client->name);
    $words = ['client' => $name, 'name' => config('imprint.name')];
@endphp

<foundry:layouts.auth :title="__('foundry::mcp.consent.title', $words)" :description="__('foundry::mcp.consent.description', $words)" card>
    <div class="flex flex-col gap-3">
        <foundry:text variant="label" tone="accent">{{ __('foundry::mcp.consent.eyebrow') }}</foundry:text>
        <foundry:heading size="1" level="1">{{ __('foundry::mcp.consent.heading', $words) }}</foundry:heading>
        <foundry:text tone="muted">{!! __('foundry::mcp.consent.lede', [
            'client' => e($name),
            'name' => e(config('imprint.name')),
            'hosts' => '<strong class="font-semibold text-zinc-950 dark:text-zinc-50">'.e(\Steddle\Foundry\Mcp\Agents::hosts($client->redirect_uris)).'</strong>',
        ]) !!}</foundry:text>
    </div>

    <foundry:account-row :$user />

    @if (config('imprint.mcp.may') || config('imprint.mcp.note'))
        <div class="flex flex-col gap-3">
            @if (config('imprint.mcp.may'))
                <foundry:text variant="label" tone="muted">{{ __('foundry::mcp.consent.may') }}</foundry:text>
                <foundry:checklist>
                    @foreach (config('imprint.mcp.may') as $line)
                        <foundry:checklist.item>{{ __($line) }}</foundry:checklist.item>
                    @endforeach
                </foundry:checklist>
            @endif
            @if (config('imprint.mcp.note'))
                <foundry:text variant="small" tone="muted">{{ __(config('imprint.mcp.note')) }}</foundry:text>
            @endif
        </div>
    @endif

    {{-- The fieldset disables the other button without the disabled attribute, which is what shows Flux's spinner; it leaves the hidden inputs out, as a disabled input is never sent. --}}
    <foundry:actions x-data="{ sent: null }">
        <form method="POST" action="{{ route('passport.authorizations.deny') }}" class="flex-1" x-on:submit="sent = 'deny'">
            @csrf
            @method('DELETE')
            <input type="hidden" name="auth_token" value="{{ $authToken }}">
            <fieldset x-bind:disabled="sent" class="contents">
                <foundry:button type="submit" variant="secondary" class="w-full" x-bind:disabled="sent === 'deny'">{{ __('foundry::mcp.consent.cancel') }}</foundry:button>
            </fieldset>
        </form>

        <form method="POST" action="{{ route('passport.authorizations.approve') }}" class="flex-1" x-on:submit="sent = 'approve'">
            @csrf
            <input type="hidden" name="auth_token" value="{{ $authToken }}">
            <fieldset x-bind:disabled="sent" class="contents">
                <foundry:button type="submit" class="w-full" x-bind:disabled="sent === 'approve'">{{ __('foundry::mcp.consent.approve') }}</foundry:button>
            </fieldset>
        </form>
    </foundry:actions>
</foundry:layouts.auth>
