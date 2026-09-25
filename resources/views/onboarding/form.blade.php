<div class="contents">
    <div class="flex flex-col gap-3">
        <foundry:heading size="1" level="1">{{ __('foundry::onboarding.title') }}</foundry:heading>
        <foundry:text tone="muted">{{ $lead ? $lead.' ' : '' }}{{ __('foundry::onboarding.description', ['name' => config('imprint.name')]) }}</foundry:text>
    </div>

    <form wire:submit="save" class="flex flex-col gap-5">
        <flux:input wire:model="name" :label="__('foundry::onboarding.name')" required autofocus autocomplete="name" maxlength="255" />

        <foundry:button type="submit" class="w-full">{{ __('foundry::onboarding.continue') }}</foundry:button>
    </form>

    <foundry:text variant="small" tone="muted">{{ __('foundry::onboarding.signed_in_as', ['email' => auth()->user()->email]) }}</foundry:text>
</div>
