{{-- /welcome, in the imprint's own layouts::auth, the one its sign-in takes. --}}
<x-layouts::auth :title="__('foundry::onboarding.title')" :description="__('foundry::onboarding.description', ['name' => config('imprint.name')])">
    <livewire:foundry.welcome />
</x-layouts::auth>
