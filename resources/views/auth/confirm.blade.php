{{--
    A usable link posts itself, unless another account is signed in, which
    confirms the switch with a button. The POST spends the token: a mail scanner
    fetches the link but runs no script. `$link` is null for an unknown token;
    an imprint's own copy of this view reads its `purpose`.
--}}
<foundry:layouts.auth :title="__($usable ? 'foundry::sign-in.confirm.title' : 'foundry::sign-in.expired.heading')" :description="__('foundry::sign-in.description', ['name' => config('imprint.name')])" card>
    @if ($usable && $switching)
        <div class="flex flex-col gap-3">
            <foundry:heading size="1" level="1">{{ __('foundry::sign-in.confirm.switch.heading') }}</foundry:heading>
            <foundry:text tone="muted">{{ __('foundry::sign-in.confirm.switch.lede', ['current' => auth()->user()->email, 'email' => $link->user->email]) }}</foundry:text>
        </div>

        <form method="POST" action="{{ $action }}" class="w-full">
            @csrf

            <foundry:button type="submit" class="w-full">{{ __('foundry::sign-in.confirm.switch.submit', ['email' => $link->user->email]) }}</foundry:button>
        </form>
    @elseif ($usable)
        <flux:icon.loading class="size-6 text-primary-700 dark:text-primary-300" />

        <div class="flex flex-col gap-3">
            <foundry:heading size="1" level="1">{{ __('foundry::sign-in.confirm.heading') }}</foundry:heading>
            <foundry:text tone="muted">{{ __('foundry::sign-in.confirm.lede') }}</foundry:text>
        </div>

        <form method="POST" action="{{ $action }}" id="foundry-sign-in" class="w-full">
            @csrf

            <noscript>
                <foundry:button type="submit" class="w-full">{{ __('foundry::sign-in.confirm.submit') }}</foundry:button>
            </noscript>
        </form>

        <script>
            document.getElementById('foundry-sign-in').submit();
        </script>
    @else
        <div class="flex flex-col gap-3">
            <foundry:heading size="1" level="1">{{ __('foundry::sign-in.expired.heading') }}</foundry:heading>
            <foundry:text tone="muted">{{ __('foundry::sign-in.expired.lede', ['minutes' => \Steddle\Foundry\Auth\MagicLink::MINUTES]) }}</foundry:text>
        </div>

        <foundry:button :href="route('login')" class="w-full">{{ __('foundry::sign-in.expired.again') }}</foundry:button>
    @endif
</foundry:layouts.auth>
