@props(['user'])

{{--
    Who is signed in, where a page acts as them: their initials, name and address.

    @group Elements
    @prop user An object with `name` and `email`, and `initials` where the model uses `Steddle\Foundry\Concerns\HasInitials`; without them Flux's avatar draws its own from the name.

    @example Above a consent
    <x-site.account-row :user="(object) ['name' => 'Ada Visser', 'email' => 'ada@example.com']" class="max-w-md" />
--}}
<div {{ $attributes->class('flex items-center gap-3 border-y border-zinc-200 dark:border-zinc-700 py-4') }}>
    <flux:avatar :name="$user->name" :initials="$user->initials ?? null" class="shrink-0" />
    <div class="grid min-w-0">
        <p class="truncate font-semibold text-zinc-950 dark:text-zinc-50">{{ $user->name }}</p>
        <p class="truncate text-small text-zinc-600 dark:text-zinc-400">{{ $user->email }}</p>
    </div>
</div>
