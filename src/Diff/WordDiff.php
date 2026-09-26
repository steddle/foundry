<?php

namespace Steddle\Foundry\Diff;

/**
 * Two texts compared word by word: runs of words that stayed, went and came, as foundry:redline
 * draws them. Newlines are separate tokens so paragraph breaks survive comparison.
 */
class WordDiff
{
    /**
     * The cell cap prevents memory exhaustion on unusually long pairs.
     */
    private const int CELLS = 400_000;

    /**
     * Exclude empty paragraphs caused by a break on both sides.
     *
     * @return list<list<array{0: 'same'|'removed'|'added', 1: string}>>
     */
    public static function paragraphs(string $before, string $after): array
    {
        $paragraphs = [[]];

        foreach (self::between($before, $after) as $run) {
            if ($run[0] === 'break') {
                $paragraphs[] = [];
            } else {
                $paragraphs[array_key_last($paragraphs)][] = $run;
            }
        }

        return array_values(array_filter($paragraphs));
    }

    /**
     * @return list<array{0: 'same'|'removed'|'added'|'break', 1: string}>
     */
    public static function between(string $before, string $after): array
    {
        $old = self::tokens($before);
        $new = self::tokens($after);

        // Shared prefixes and suffixes need no alignment.
        $head = 0;
        while ($head < count($old) && $head < count($new) && $old[$head] === $new[$head]) {
            $head++;
        }

        $tail = 0;
        while ($tail < count($old) - $head && $tail < count($new) - $head && $old[count($old) - 1 - $tail] === $new[count($new) - 1 - $tail]) {
            $tail++;
        }

        $middle = self::align(
            array_slice($old, $head, count($old) - $head - $tail),
            array_slice($new, $head, count($new) - $head - $tail),
        );

        $ops = [
            ...array_map(fn (string $token): array => ['same', $token], array_slice($old, 0, $head)),
            ...$middle,
            ...array_map(fn (string $token): array => ['same', $token], array_slice($old, count($old) - $tail)),
        ];

        return self::merge($ops);
    }

    /** @return list<string> */
    private static function tokens(string $text): array
    {
        preg_match_all('/\n|[^\s]+/u', $text, $matches);

        return $matches[0];
    }

    /**
     * @param  list<string>  $old
     * @param  list<string>  $new
     * @return list<array{0: 'same'|'removed'|'added', 1: string}>
     */
    private static function align(array $old, array $new): array
    {
        $n = count($old);
        $m = count($new);

        if ($n * $m > self::CELLS) {
            return [
                ...array_map(fn (string $token): array => ['removed', $token], $old),
                ...array_map(fn (string $token): array => ['added', $token], $new),
            ];
        }

        $lengths = array_fill(0, $n + 1, array_fill(0, $m + 1, 0));

        for ($i = $n - 1; $i >= 0; $i--) {
            for ($j = $m - 1; $j >= 0; $j--) {
                $lengths[$i][$j] = $old[$i] === $new[$j]
                    ? $lengths[$i + 1][$j + 1] + 1
                    : max($lengths[$i + 1][$j], $lengths[$i][$j + 1]);
            }
        }

        $ops = [];
        [$i, $j] = [0, 0];

        while ($i < $n && $j < $m) {
            if ($old[$i] === $new[$j]) {
                $ops[] = ['same', $old[$i++]];
                $j++;
            } elseif ($lengths[$i + 1][$j] >= $lengths[$i][$j + 1]) {
                $ops[] = ['removed', $old[$i++]];
            } else {
                $ops[] = ['added', $new[$j++]];
            }
        }

        while ($i < $n) {
            $ops[] = ['removed', $old[$i++]];
        }

        while ($j < $m) {
            $ops[] = ['added', $new[$j++]];
        }

        return $ops;
    }

    /**
     * Newlines end runs so paragraph breaks are never struck through.
     *
     * @param  list<array{0: 'same'|'removed'|'added', 1: string}>  $ops
     * @return list<array{0: 'same'|'removed'|'added'|'break', 1: string}>
     */
    private static function merge(array $ops): array
    {
        $runs = [];

        foreach ($ops as [$type, $token]) {
            $last = array_key_last($runs);

            if ($token === "\n") {
                // A newline on either side must still produce a paragraph break.
                $runs[] = ['break', "\n"];
            } elseif ($last !== null && $runs[$last][0] === $type) {
                $runs[$last][1] .= ' '.$token;
            } else {
                $runs[] = [$type, $token];
            }
        }

        return $runs;
    }
}
