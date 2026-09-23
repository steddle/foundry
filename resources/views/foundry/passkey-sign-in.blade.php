{{--
    Signs a reader in with a passkey over the browser's credential API, under
    the sign-in form, set off from it by 'or'. It shows only where the browser
    can use one, so the 'or' never stands alone, and leads to where the
    passkey routes answer, else to `fortify.home`. The script is the
    foundry's resources/js/passkeys.js, which the imprint's Vite builds.

    @group Forms

    @example Under a sign-in form
    @code
    <foundry:passkey-sign-in />
--}}

@assets
@vite('vendor/steddle/foundry/resources/js/passkeys.js')
@endassets

<div
    {{ $attributes }}
    x-data="{
        supported: false,
        loading: false,
        error: null,
        updateSupport() {
            this.supported = Boolean(window.Passkeys?.isSupported());
        },
        init() {
            this.updateSupport();

            window.addEventListener('passkeys:ready', () => this.updateSupport(), { once: true });
        },
        async verify() {
            this.loading = true;
            this.error = null;

            try {
                const response = await window.Passkeys.verify({
                    routes: {
                        options: '{{ route('passkey.login-options') }}',
                        submit: '{{ route('passkey.login') }}',
                    },
                });

                window.location.assign(response.redirect || '{{ url(config('fortify.home', '/')) }}');
            } catch (e) {
                if (e.constructor?.name !== 'UserCancelledError') {
                    this.error = e.message;
                }
            } finally {
                this.loading = false;
            }
        },
    }"
>
    <template x-if="supported">
        <div class="flex flex-col gap-6">
            <div class="flex items-center gap-3 text-label text-zinc-600 dark:text-zinc-400">
                <span class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></span>
                {{ __('foundry::passkeys.or') }}
                <span class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></span>
            </div>

            <div class="grid gap-2">
                <foundry:button variant="secondary" icon="finger-print" class="w-full" x-on:click="verify()" x-bind:disabled="loading">
                    <span x-show="! loading">{{ __('foundry::passkeys.sign_in') }}</span>
                    <span x-show="loading" x-cloak>{{ __('foundry::passkeys.signing_in') }}</span>
                </foundry:button>
                <p x-show="error" x-text="error" x-cloak class="text-center text-small text-danger-700 dark:text-danger-300"></p>
            </div>
        </div>
    </template>
</div>
