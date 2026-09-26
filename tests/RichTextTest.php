<?php

use Steddle\Foundry\Markdown\RichText;

it('gives back the markdown it was given', function (string $markdown) {
    $richText = app(RichText::class);

    expect($richText->markdown($richText->html($markdown)))->toBe($markdown);
})->with([
    'headings, emphasis and lists' => "# Thesis\n\nClaims are **slow** and *opaque*.\n\n- One\n- Two\n\n1. First\n2. Second\n",
    'a table with its alignment' => "| # | Risk | Share |\n|---:|---|---|\n| 1 | Funding | 50% |\n",
    'a task list' => "- [x] Structure agreed\n- [ ] Notary chosen\n",
    'a link with spaces, and a relative link' => "- **Transcript:** [01](<transcripts/01 2026-04-22 Call.md>)\n\nSee [the memo](../decisions/008-cap-table.md).\n",
    'bare addresses' => "Mail mischa@sigtermans.me or open http://localhost:8000 and www.example.com.\n",
    'a hard break' => "**Holly Thackwray**\\\nCEO\n",
    'brackets, ampersands and underscores' => "**[00:00:01] Mischa:** R&B's upstream_hash_check.\n",
]);

it('reads a table from the editor, which wraps each cell in a paragraph', function () {
    expect(app(RichText::class)->markdown('<table><tbody><tr><th><p>Who</p></th><th><p>Share</p></th></tr><tr><td><p>Holly</p></td><td><p>50%</p></td></tr></tbody></table>'))
        ->toBe("| Who | Share |\n|---|---|\n| Holly | 50% |\n");
});

it('opens a text the editor would change as markdown', function () {
    expect(app(RichText::class)->survives("**Run `php artisan`** first.\n"))->toBeFalse()
        ->and(app(RichText::class)->survives("# Plan\n\nPlain text.\n"))->toBeTrue();
});
