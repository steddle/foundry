<foundry:layouts.auth :title="__('foundry::account.error.heading')" :description="__('foundry::account.error.lede', ['name' => config('imprint.name')])" card>
    <div class="flex flex-col gap-3">
        <foundry:heading size="1" level="1">{{ __('foundry::account.error.heading') }}</foundry:heading>
        <foundry:text tone="muted">{{ __('foundry::account.error.lede', ['name' => config('imprint.name')]) }}</foundry:text>
    </div>

    <foundry:button :href="route('login')" class="w-full">{{ __('foundry::account.error.again') }}</foundry:button>
</foundry:layouts.auth>
