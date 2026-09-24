<?php

namespace Steddle\Foundry\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * An account names itself once, on /welcome, and goes on where it was
 * headed: the page it asked for, or `imprint.onboarding.home`. Where
 * `imprint.onboarding.next` names a route, that step comes first, and ends
 * where this one would have.
 */
final class Welcome extends Component
{
    public string $name = '';

    public function mount(): void
    {
        $user = Auth::user();

        if ($user->hasOnboarded()) {
            $this->proceed();

            return;
        }

        session()->reflash();

        // An invokable class of the imprint's own, handed the account: the name it already knows, such as from an NDA sent to it.
        $prefill = config('imprint.onboarding.prefill');

        $this->name = ($prefill ? app($prefill)($user) : null) ?? ($user->hasPlaceholderName() ? '' : (string) $user->name);
    }

    public function save(): void
    {
        session()->reflash();

        $validated = $this->validate(['name' => ['required', 'string', 'max:255']]);

        Auth::user()->forceFill([
            'name' => trim($validated['name']),
            'onboarded_at' => now(),
        ])->save();

        $this->proceed();
    }

    private function proceed(): void
    {
        if ($next = config('imprint.onboarding.next')) {
            $this->redirectRoute($next);

            return;
        }

        $this->redirectIntended(url(config('imprint.onboarding.home', '/')));
    }

    public function render(): View
    {
        return view('foundry::onboarding.form');
    }
}
