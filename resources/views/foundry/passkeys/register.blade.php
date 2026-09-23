{{--
    Adds a passkey for the signed-in reader over the browser's credential
    API, named after the browser and the device it is made on, then asks the
    page to load its passkeys again. Where the browser has none, a line says
    so.
--}}

@assets
@vite('vendor/steddle/foundry/resources/js/passkeys.js')
@endassets

<div
    x-data="{
        supported: false,
        open: false,
        name: '',
        loading: false,
        error: null,
        updateSupport() {
            this.supported = Boolean(window.Passkeys?.isSupported());
        },
        deviceName() {
            const ua = navigator.userAgent;

            const browser = [
                { pattern: /Edg|Edge/, name: 'Edge' },
                { pattern: /OPR|Opera|OPiOS/, name: 'Opera' },
                { pattern: /Firefox|FxiOS/, name: 'Firefox' },
                { pattern: /Chrome|CriOS/, name: 'Chrome' },
                { pattern: /Safari/, name: 'Safari' },
            ].find(({ pattern }) => pattern.test(ua))?.name;

            const os = [
                { pattern: /iPhone/, name: 'iPhone' },
                { pattern: /iPad|Macintosh(?=.*Mobile)/, name: 'iPad' },
                { pattern: /Android/, name: 'Android' },
                { pattern: /Mac/, name: 'Mac' },
                { pattern: /Windows/, name: 'Windows' },
            ].find(({ pattern }) => pattern.test(ua))?.name;

            return browser && os ? @js(__('foundry::passkeys.device')).replace(':browser', browser).replace(':os', os) : (browser || os || '');
        },
        init() {
            this.updateSupport();

            window.addEventListener('passkeys:ready', () => this.updateSupport(), { once: true });
        },
        start() {
            this.name = this.deviceName();
            this.open = true;
        },
        async register() {
            if (! this.name.trim()) return;

            this.loading = true;
            this.error = null;

            try {
                await window.Passkeys.register({ name: this.name });
                this.cancel();
                await $wire.loadPasskeys();
            } catch (e) {
                if (e.constructor?.name !== 'UserCancelledError') {
                    this.error = e.message;
                }
            } finally {
                this.loading = false;
            }
        },
        cancel() {
            this.open = false;
            this.name = '';
            this.error = null;
        },
    }"
>
    <template x-if="! supported">
        <foundry:text variant="small" tone="muted">{{ __('foundry::passkeys.unsupported') }}</foundry:text>
    </template>

    <template x-if="supported && ! open">
        <div>
            <foundry:button variant="secondary" icon="plus" x-on:click="start()">{{ __('foundry::passkeys.add') }}</foundry:button>
        </div>
    </template>

    <template x-if="supported && open">
        <div class="flex flex-col gap-4 rounded-md border border-zinc-200 dark:border-zinc-700 p-4">
            <flux:input
                :label="__('foundry::passkeys.name')"
                :description:trailing="__('foundry::passkeys.name_hint')"
                x-model="name"
                x-on:keydown.enter.prevent="register()"
                x-ref="name"
                x-init="$nextTick(() => $refs.name?.focus())"
            />

            <p x-show="error" x-text="error" x-cloak class="text-small text-danger-700 dark:text-danger-300"></p>

            <foundry:actions>
                <foundry:button x-on:click="register()" x-bind:disabled="loading || ! name.trim()">
                    <span x-show="! loading">{{ __('foundry::passkeys.save') }}</span>
                    <span x-show="loading" x-cloak>{{ __('foundry::passkeys.saving') }}</span>
                </foundry:button>
                <foundry:button variant="ghost" x-on:click="cancel()">{{ __('foundry::passkeys.cancel') }}</foundry:button>
            </foundry:actions>
        </div>
    </template>
</div>
