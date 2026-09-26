<?php

namespace Steddle\Foundry\Annotations;

/**
 * A passage of text known by what it says, with a little of what stands on either side, as the
 * W3C Web Annotation TextQuoteSelector knows it: found again after the text around it changed,
 * and not found once the passage itself is gone.
 *
 * Offsets count UTF-16 code units, as JavaScript's do, so foundry:annotated paints the passage the
 * server found: a byte offset would shift after every accent or emoji.
 */
final readonly class TextQuote
{
    public const CONTEXT = 32;

    public function __construct(
        public string $quote,
        public string $prefix = '',
        public string $suffix = '',
    ) {}

    /**
     * The passage between two offsets of a text, with its context.
     */
    public static function between(string $text, int $start, int $end): self
    {
        $units = self::units($text);

        return new self(
            self::slice($units, $start, $end - $start),
            self::slice($units, max(0, $start - self::CONTEXT), $start - max(0, $start - self::CONTEXT)),
            self::slice($units, $end, self::CONTEXT),
        );
    }

    /**
     * The passage a quote names in a text, when the text holds it once.
     */
    public static function of(string $text, string $quote): ?self
    {
        $quote = trim($quote);

        if ($quote === '' || substr_count($text, $quote) !== 1) {
            return null;
        }

        [$start, $end] = self::range($text, strpos($text, $quote), strlen($quote));

        return self::between($text, $start, $end);
    }

    /**
     * Where the passage stands in a text, as [start, end] in UTF-16 code units: the quote with its
     * context, then the quote alone where the text holds it once, then the occurrence whose
     * surroundings match its context best. Null when the text no longer holds it, or holds it more
     * than once with nothing to tell them apart.
     *
     * @return array{0: int, 1: int}|null
     */
    public function locateIn(string $text): ?array
    {
        $bytes = $this->bytesIn($text);

        return $bytes === null ? null : self::range($text, $bytes[0], $bytes[1] - $bytes[0]);
    }

    /**
     * The same, as a byte range of the UTF-8 text, for a server that changes the passage.
     *
     * @return array{0: int, 1: int}|null
     */
    public function bytesIn(string $text): ?array
    {
        if ($this->quote === '') {
            return null;
        }

        $whole = $this->prefix.$this->suffix === '' ? false : strpos($text, $this->prefix.$this->quote.$this->suffix);

        if ($whole !== false) {
            return [$whole + strlen($this->prefix), $whole + strlen($this->prefix) + strlen($this->quote)];
        }

        $found = [];

        for ($at = strpos($text, $this->quote); $at !== false; $at = strpos($text, $this->quote, $at + 1)) {
            $found[$at] = $this->score($text, $at);
        }

        if ($found === []) {
            return null;
        }

        arsort($found);
        $best = array_key_first($found);

        if (count($found) > 1 && ($found[$best] === 0 || array_values($found)[1] === $found[$best])) {
            return null;
        }

        return [$best, $best + strlen($this->quote)];
    }

    /**
     * How many characters of the context agree with the text on either side of an occurrence.
     */
    private function score(string $text, int $at): int
    {
        $before = substr($text, max(0, $at - strlen($this->prefix)), min($at, strlen($this->prefix)));
        $after = substr($text, $at + strlen($this->quote), strlen($this->suffix));
        $score = 0;

        for ($i = 1; $i <= min(strlen($before), strlen($this->prefix)) && $before[strlen($before) - $i] === $this->prefix[strlen($this->prefix) - $i]; $i++) {
            $score++;
        }

        for ($i = 0; $i < min(strlen($after), strlen($this->suffix)) && $after[$i] === $this->suffix[$i]; $i++) {
            $score++;
        }

        return $score;
    }

    /**
     * A byte range of a UTF-8 text as a range of UTF-16 code units.
     *
     * @return array{0: int, 1: int}
     */
    private static function range(string $text, int $byte, int $length): array
    {
        $start = strlen(mb_convert_encoding(substr($text, 0, $byte), 'UTF-16LE', 'UTF-8')) / 2;

        return [$start, $start + strlen(mb_convert_encoding(substr($text, $byte, $length), 'UTF-16LE', 'UTF-8')) / 2];
    }

    private static function units(string $text): string
    {
        return mb_convert_encoding($text, 'UTF-16LE', 'UTF-8');
    }

    private static function slice(string $units, int $start, int $length): string
    {
        return mb_convert_encoding(substr($units, $start * 2, max(0, $length) * 2), 'UTF-8', 'UTF-16LE');
    }
}
