<x-layouts::auth :title="__('foundry::onboarding.title')" :description="__('foundry::onboarding.description', ['name' => config('imprint.name')])">
    <livewire:foundry.welcome />
</x-layouts::auth>
