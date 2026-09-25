<foundry:layouts.auth :title="__('foundry::sign-in.title')" :description="__('foundry::sign-in.description', ['name' => config('imprint.name')])" card>
    @if (session('sign_in_email'))
        <flux:icon.envelope variant="outline" class="size-8 text-primary-700 dark:text-primary-300" />

        <div class="flex flex-col gap-3">
            <foundry:heading size="1" level="1">{{ __('foundry::sign-in.sent.heading') }}</foundry:heading>
            <foundry:text tone="muted">
                {!! __(config('imprint.auth.signup') ? 'foundry::sign-in.sent.signup' : 'foundry::sign-in.sent.members', [
                    'email' => '<strong class="font-semibold text-zinc-950 dark:text-zinc-50">'.e(session('sign_in_email')).'</strong>',
                    'minutes' => \Steddle\Foundry\Auth\MagicLink::MINUTES,
                ]) !!}
            </foundry:text>
        </div>

        <a href="{{ route('login') }}" class="text-small link">{{ __('foundry::sign-in.sent.other') }}</a>
    @else
        <div class="flex flex-col gap-3">
            <foundry:heading size="1" level="1">{{ __('foundry::sign-in.heading', ['name' => config('imprint.name')]) }}</foundry:heading>
            <foundry:text tone="muted">{{ __(config('imprint.auth.signup') ? 'foundry::sign-in.lede.signup' : 'foundry::sign-in.lede.members') }}</foundry:text>
        </div>

        <form method="POST" action="{{ route('login.magic.send') }}" class="flex flex-col gap-5">
            @csrf

            {{-- `webauthn` lets the browser offer a saved passkey in the field. --}}
            <flux:input name="email" type="email" :label="__('foundry::sign-in.email')" :value="old('email')" required autofocus autocomplete="email webauthn" />

            <foundry:button type="submit" class="w-full">{{ __('foundry::sign-in.submit') }}</foundry:button>
        </form>

        @if (Route::has('passkey.login'))
            <foundry:passkey-sign-in />
        @endif
    @endif
</foundry:layouts.auth>
