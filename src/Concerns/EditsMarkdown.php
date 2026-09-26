<?php

namespace Steddle\Foundry\Concerns;

use Steddle\Foundry\Markdown\RichText;

/**
 * A Livewire page that edits a markdown text in foundry:markdown-editor: in the rich editor, or as
 * markdown with the switch on. A text that does not survive the editor opens as markdown. A text
 * the editor gave back changed in whitespace alone is kept as it was, so opening and saving
 * changes nothing.
 */
trait EditsMarkdown
{
    public string $html = '';

    public string $markdown = '';

    public bool $source = false;

    /**
     * The text as it is stored, before this edit.
     */
    abstract protected function savedBody(): string;

    protected function startEditing(string $body): void
    {
        $richText = app(RichText::class);

        $this->markdown = $body;
        $this->html = $richText->html($body);
        $this->source = ! $richText->survives($body);
        $this->resetErrorBag();
    }

    public function updatedSource(bool $source): void
    {
        if ($source) {
            $this->markdown = $this->bodyFromEditor();
        } else {
            $this->html = app(RichText::class)->html($this->markdown);
        }
    }

    /**
     * The text as edited, in either mode.
     */
    protected function editedBody(): string
    {
        return $this->source ? str_replace("\r\n", "\n", $this->markdown) : $this->bodyFromEditor();
    }

    private function bodyFromEditor(): string
    {
        $saved = $this->savedBody();
        $converted = app(RichText::class)->markdown($this->html, $saved);

        if (RichText::normalise($converted) === RichText::normalise($saved)) {
            return $saved;
        }

        // The blank lines a text opens on are not the editor's to drop.
        preg_match('/\A\n*/', $saved, $lead);

        return $lead[0].$converted;
    }
}
