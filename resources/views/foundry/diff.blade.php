@props(['diff'])

{{--
    A unified diff, lines added on the success wash and removed on the danger
    wash. Set in mono, as git prints one.

    @group Elements
    @prop diff The unified diff, as `diff -u` or jfcherng/php-diff writes it.

    @example Two versions of a text
    <foundry:diff diff="@@ -1,3 +1,3 @@
 # Thesis
-The fund closes in March.
+The fund closes in May.
 Two founders." />
--}}
<pre {{ $attributes->class('overflow-x-auto rounded-lg bg-zinc-25 p-5 font-mono text-code slashed-zero tabular-nums dark:bg-zinc-800') }} data-diff>@foreach (explode("\n", $diff) as $line)<span @class([
    'block',
    'bg-success-50 text-success-700 dark:bg-success-300/10 dark:text-success-300' => str_starts_with($line, '+') && ! str_starts_with($line, '+++'),
    'bg-danger-50 text-danger-700 dark:bg-danger-300/10 dark:text-danger-300' => str_starts_with($line, '-') && ! str_starts_with($line, '---'),
    'text-zinc-500' => str_starts_with($line, '@@'),
])>{{ $line === '' ? ' ' : $line }}</span>@endforeach</pre>
