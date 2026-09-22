@props(['user'])

{{--
    The signed-in reader's menu: their initials open a Flux dropdown with
    their name and address, the imprint's own items and logging out.

    @group Navigation
    @prop user An object with `name` and `email`; the initials are the first letters of the name's first two words.
    @slot default The imprint's items, each a `flux:menu.item`, between the name and logging out.

    @example With a settings item
    <div class="flex justify-end">
        <x-site.account-menu :user="(object) ['name' => 'Ada Visser', 'email' => 'ada@example.com']">
            <flux:menu.item href="#" icon="cog-6-tooth">Settings</flux:menu.item>
        </x-site.account-menu>
    </div>
--}}
<flux:dropdown position="bottom" align="end" {{ $attributes }}>
    <button type="button" aria-label="{{ __('foundry::nav.account', ['name' => $user->name]) }}"
        class="flex size-10 shrink-0 cursor-pointer items-center justify-center rounded-md bg-zinc-100 dark:bg-zinc-800 text-small font-semibold text-zinc-950 dark:text-zinc-50 hover:bg-zinc-200 dark:hover:bg-zinc-700">
        {{ \Illuminate\Support\Str::of($user->name)->explode(' ')->filter()->take(2)->map(fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)))->implode('') }}
    </button>

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
