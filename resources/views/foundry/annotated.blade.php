@use('Illuminate\Support\Js')

@props(['anchors' => []])

{{--
    Prose a reader comments on. It paints each anchor, a range of the slot's
    `textContent` in UTF-16 offsets as TextQuote::locateIn gives it on
    PlainText::fromHtml of the same HTML, trimmed as a slot is, with the
    CSS Custom Highlight API, so the DOM stays as the server rendered it. A
    selection inside the text shows the actions slot beside it, with the
    passage in `selection`: its quote, prefix and suffix, and `lines`, the
    first and last line of the source its blocks carry in `data-lines`.
    Clicking a highlight dispatches `foundry-annotation` with its id; a
    `foundry-annotation-focus` event with an id scrolls to that passage and
    marks it.

    @group Elements
    @prop anchors The passages to paint: `[{id, start, end, state}]`, where state is `comment` or `suggestion`.
    @slot actions Buttons shown beside a selection; `selection` holds the passage.

    @example A passage commented on
    <foundry:annotated :anchors="[['id' => 1, 'start' => 9, 'end' => 24, 'state' => 'comment']]">
        <p>The fund closes in March, with two founders.</p>
        <x-slot:actions><foundry:button size="sm" variant="secondary" icon="chat-bubble-left">Comment</foundry:button></x-slot:actions>
    </foundry:annotated>
--}}
<div
    {{ $attributes->class('relative') }}
    wire:key="annotated-{{ md5(json_encode($anchors).$slot) }}"
    data-annotated
    x-data="{
        anchors: {{ Js::from(array_values($anchors)) }},
        selection: null,
        spans() {
            const walker = document.createTreeWalker(this.$refs.text, NodeFilter.SHOW_TEXT);
            const spans = [];
            let at = 0;
            for (let node = walker.nextNode(); node; node = walker.nextNode()) {
                spans.push({ node, start: at, end: at + node.data.length });
                at += node.data.length;
            }
            return spans;
        },
        range(start, end) {
            const spans = this.spans();
            const first = spans.find((span) => start >= span.start && start < span.end);
            const last = spans.find((span) => end > span.start && end <= span.end);
            if (! first || ! last) return null;
            const range = document.createRange();
            range.setStart(first.node, start - first.start);
            range.setEnd(last.node, end - last.start);
            return range;
        },
        offset(node, offset) {
            const span = this.spans().find((span) => span.node === node);
            if (span) return span.start + offset;
            const probe = document.createRange();
            probe.selectNodeContents(this.$refs.text);
            probe.setEnd(node, offset);
            return probe.toString().length;
        },
        paint() {
            if (! window.CSS?.highlights) return;
            ['comment', 'suggestion'].forEach((state) => CSS.highlights.set('foundry-' + state, new Highlight(
                ...this.anchors.filter((anchor) => anchor.state === state).map((anchor) => this.range(anchor.start, anchor.end)).filter(Boolean)
            )));
        },
        focus(id) {
            const anchor = this.anchors.find((anchor) => anchor.id === id);
            const range = anchor && this.range(anchor.start, anchor.end);
            if (! range || ! window.CSS?.highlights) return;
            CSS.highlights.set('foundry-focus', new Highlight(range));
            const box = range.getBoundingClientRect();
            window.scrollBy({ top: box.top - window.innerHeight / 3, behavior: 'smooth' });
        },
        select() {
            const chosen = window.getSelection();
            if (! chosen || chosen.isCollapsed || ! this.$refs.text.contains(chosen.anchorNode) || ! this.$refs.text.contains(chosen.focusNode)) {
                this.selection = null;
                return;
            }
            const picked = chosen.getRangeAt(0);
            const text = this.$refs.text.textContent;
            let start = this.offset(picked.startContainer, picked.startOffset);
            let end = this.offset(picked.endContainer, picked.endOffset);
            while (start < end && /\s/.test(text[start])) start++;
            while (end > start && /\s/.test(text[end - 1])) end--;
            if (start === end) {
                this.selection = null;
                return;
            }
            const blocks = [picked.startContainer, picked.endContainer]
                .map((node) => (node.nodeType === 1 ? node : node.parentElement)?.closest('[data-lines]')?.dataset.lines)
                .filter(Boolean)
                .map((lines) => lines.split('-').map(Number));
            const box = picked.getBoundingClientRect();
            const frame = this.$el.getBoundingClientRect();
            this.selection = {
                quote: text.slice(start, end),
                prefix: text.slice(Math.max(0, start - 32), start),
                suffix: text.slice(end, end + 32),
                lines: blocks.length ? Math.min(...blocks.map((block) => block[0])) + '-' + Math.max(...blocks.map((block) => block[1])) : null,
                top: box.bottom - frame.top + 8,
                left: Math.max(0, Math.min(box.left - frame.left, frame.width - 240)),
            };
        },
        open(event) {
            if (! window.getSelection()?.isCollapsed) return;
            const caret = document.caretPositionFromPoint?.(event.clientX, event.clientY);
            const point = caret ? [caret.offsetNode, caret.offset] : (() => { const range = document.caretRangeFromPoint?.(event.clientX, event.clientY); return range ? [range.startContainer, range.startOffset] : null; })();
            if (! point || ! this.$refs.text.contains(point[0])) return;
            const at = this.offset(point[0], point[1]);
            const anchor = this.anchors.find((anchor) => at >= anchor.start && at < anchor.end);
            if (anchor) {
                this.focus(anchor.id);
                this.$dispatch('foundry-annotation', { id: anchor.id });
            }
        },
        destroy() {
            ['comment', 'suggestion', 'focus'].forEach((state) => window.CSS?.highlights?.delete('foundry-' + state));
        },
    }"
    x-init="paint()"
    x-on:selectionchange.document.debounce.150ms="select()"
    x-on:foundry-annotation-focus.window="focus($event.detail.id)"
    x-on:keydown.escape.window="selection = null"
>
    {{-- No whitespace around the slot: the offsets count every character of the text. --}}
    <div x-ref="text" x-on:click="open($event)">{{ $slot }}</div>

    @isset($actions)
        <div
            x-cloak
            x-show="selection"
            x-bind:style="selection && { top: selection.top + 'px', left: selection.left + 'px' }"
            class="absolute z-10 flex gap-2 rounded-lg bg-white p-1.5 shadow-lg ring-1 ring-zinc-950/10 dark:bg-zinc-800 dark:shadow-none dark:ring-white/10"
            data-annotate-actions
        >
            {{ $actions }}
        </div>
    @endisset
</div>
