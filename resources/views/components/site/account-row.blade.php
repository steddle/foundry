@props(['user'])

{{--
    Who is signed in, where a page acts as them: their initials, name and address.

    @group Elements

    @example Above a consent
    <x-site.account-row :user="(object) ['name' => 'Ada Visser', 'email' => 'ada@example.com']" class="max-w-md" />
--}}
<div {{ $attributes->class('flex items-center gap-3 border-y border-zinc-200 dark:border-zinc-700 py-4') }}>
    <div class="flex size-10 shrink-0 items-center justify-center rounded-md bg-zinc-100 dark:bg-zinc-950 text-small font-semibold text-zinc-950 dark:text-zinc-50">
        {{ \Illuminate\Support\Str::of($user->name)->explode(' ')->filter()->take(2)->map(fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)))->implode('') }}
    </div>
    <div class="grid min-w-0">
        <p class="truncate font-semibold text-zinc-950 dark:text-zinc-50">{{ $user->name }}</p>
        <p class="truncate text-small text-zinc-600 dark:text-zinc-400">{{ $user->email }}</p>
    </div>
</div>
