@props(['user'])

{{--
    The signed-in reader's menu: their Flux avatar opens a dropdown with
    their name and address, the imprint's own items and logging out.

    @group Navigation
    @prop user An object with `name` and `email`, and `initials` where the model uses `Steddle\Foundry\Concerns\HasInitials`; without them Flux's avatar draws its own from the name.
    @slot slot The imprint's items, each a `flux:menu.item`, between the name and logging out.

    @example With a settings item
    <div class="flex justify-end">
        <foundry:account-menu :user="(object) ['name' => 'Ada Visser', 'email' => 'ada@example.com']">
            <flux:menu.item href="#" icon="cog-6-tooth">Settings</flux:menu.item>
        </foundry:account-menu>
    </div>
--}}
<flux:dropdown position="bottom" align="end" {{ $attributes }}>
    <flux:avatar as="button" :name="$user->name" :initials="$user->initials ?? null" size="sm" class="cursor-pointer" aria-label="{{ __('foundry::nav.account', ['name' => $user->name]) }}" />

    <flux:menu class="min-w-60">
        <div class="grid px-2 py-1.5">
            <p class="truncate text-small font-semibold text-zinc-950 dark:text-zinc-50">{{ $user->name }}</p>
            <p class="truncate text-small text-zinc-600 dark:text-zinc-400">{{ $user->email }}</p>
        </div>

        @if ($slot->isNotEmpty())
            <flux:menu.separator />
            {{ $slot }}
        @endif

        @if (Route::has('logout'))
            <flux:menu.separator />
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle">{{ __('foundry::nav.log_out') }}</flux:menu.item>
            </form>
        @endif
    </flux:menu>
</flux:dropdown>
