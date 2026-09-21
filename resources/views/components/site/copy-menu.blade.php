@use('Illuminate\Support\Js')
@use('Steddle\Foundry\Markdown\MarkdownUrl')

@props(['page' => null, 'all' => null])

{{--
    Copies or opens the page's own markdown, or every page's at once, with the
    token count of each. It fetches both on the first hover or focus.

    @group Actions

    @example Beside a page title
    <x-site.copy-menu />
--}}
@php
    $page ??= MarkdownUrl::of(url()->current());
    $all ??= route('llms.full');
@endphp

{{--
    Fetches each address once, on the visitor's first hover or focus rather
    than on page load, and keeps the text: the token count is that response's
    own `X-Markdown-Tokens` header, and copying needs no second request. A GET
    carries both; a HEAD, though `ProvideMarkdownResponse` does answer it with
    the header, still leaves the text to fetch again at the click that copies it.

    data-markdown-skip goes on the root: RemoveMarkdownSkipPreprocessor reads
    a quoted attribute value whole, so the `=>` in the x-data below does not
    end the tag, whichever side of it the attribute stands.
--}}
<div data-markdown-skip x-data="{
        urls: { page: {{ Js::from($page) }}, all: {{ Js::from($all) }} },
        tokens: { page: null, all: null },
        texts: { page: null, all: null },
        copied: null,
        loaded: false,
        load() {
            if (this.loaded) return;
            this.loaded = true;
            this.fetch('page');
            this.fetch('all');
        },
        async fetch(kind) {
            try {
                const response = await window.fetch(this.urls[kind]);
                this.tokens[kind] = response.headers.get('X-Markdown-Tokens');
                this.texts[kind] = await response.text();
            } catch (e) {}
        },
        format(n) {
            n = parseInt(n, 10);
            if (! n) return '';
            return (n >= 1000 ? '~' + (n / 1000).toFixed(1) + 'K' : '~' + n) + ' ' + {{ Js::from(__('foundry::agents.tokens')) }};
        },
        async copy(kind) {
            if (this.texts[kind] === null) await this.fetch(kind);
            await navigator.clipboard.writeText(this.texts[kind] ?? '');
            this.copied = kind;
            setTimeout(() => { this.copied = null }, 1500);
        },
    }"
    @mouseenter.once="load()"
    @focusin.once="load()"
>
    <flux:dropdown position="bottom" align="end">
        <flux:button icon="clipboard-document" size="sm" variant="ghost" class="border border-zinc-200 text-zinc-950 hover:bg-zinc-100 dark:border-zinc-50/13 dark:text-zinc-50! dark:hover:bg-zinc-50/5!">{{ __('foundry::agents.copy') }}</flux:button>

        {{--
            The label is read through Js::from(), not embedded between quotes
            of its own: `foundry::agents.copy_all` reads "Alle pagina's kopiëren" and
            an apostrophe inside a hand-quoted JS string ends it early.
        --}}
        <flux:menu>
            <flux:menu.item @click="copy('page')">
                <span class="flex-1 text-start" x-text="copied === 'page' ? {{ Js::from(__('foundry::agents.copied')) }} : {{ Js::from(__('foundry::agents.copy_page')) }}"></span>
                <span class="ms-auto ps-6 text-xs text-zinc-600 dark:text-zinc-400" x-text="format(tokens.page)"></span>
            </flux:menu.item>
            <flux:menu.item @click="copy('all')">
                <span class="flex-1 text-start" x-text="copied === 'all' ? {{ Js::from(__('foundry::agents.copied')) }} : {{ Js::from(__('foundry::agents.copy_all')) }}"></span>
                <span class="ms-auto ps-6 text-xs text-zinc-600 dark:text-zinc-400" x-text="format(tokens.all)"></span>
            </flux:menu.item>
            <flux:menu.separator />
            <flux:menu.item href="{{ $page }}" target="_blank">{{ __('foundry::agents.open_page') }}</flux:menu.item>
            <flux:menu.item href="{{ $all }}" target="_blank">{{ __('foundry::agents.open_all') }}</flux:menu.item>
        </flux:menu>
    </flux:dropdown>
</div>
