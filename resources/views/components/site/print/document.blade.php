@props(['title', 'paper' => 'A4', 'footer' => null])

{{--
    A page for `Steddle\Foundry\Pdf\Printer`: the whole HTML document, bone
    in both themes, on A4 or Letter. Every page keeps 20 mm at the sides, and
    its foot carries the footer on the left and the page count on the right.
    Chrome sets the foot, so a long document needs no running markup of its
    own. A heading never ends a page, and a paragraph never leaves fewer than
    three lines on either side of a break.

    @prop title The document's title, which a PDF reader shows.
    @prop paper A4 or Letter.
    @prop footer The line at the foot of every page.

    @example A document
    @code
    <x-site.print.document title="Signing certificate" paper="A4" footer="Signing certificate | Mutual NDA">
        <x-site.print.masthead eyebrow="Signing certificate" title="Mutual Non-Disclosure Agreement" />
        <x-site.print.section title="The agreement">…</x-site.print.section>
    </x-site.print.document>
--}}
@php
    // Chrome writes the foot from a CSS string, which a name a reader typed could close, and the <style> with it: everything outside plain text is escaped.
    $escape = fn (?string $text): string => preg_replace_callback('/[^\p{L}\p{N} .,|:()\/-]/u', fn (array $match): string => '\\'.dechex(mb_ord($match[0])).' ', (string) $text);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    @vite(config('imprint.stylesheet'))
    <style>
        @page {
            size: {{ $paper === 'Letter' ? 'letter' : 'A4' }};
            margin: 18mm 0 20mm;

            @bottom-left {
                content: "{{ $escape($footer) }}";
                padding-left: 20mm;
                font: 400 8pt var(--font-sans);
                color: var(--color-zinc-600);
            }

            @bottom-right {
                content: counter(page) " / " counter(pages);
                padding-right: 20mm;
                font: 400 8pt var(--font-sans);
                font-variant-numeric: tabular-nums;
                color: var(--color-zinc-600);
            }
        }

        /* The rem steps the type scale is written in, at the size a page is read. */
        html {
            font-size: 12.5px;
            background: var(--color-white);
        }

        /* Side margins live on the body, not the page, so a band can bleed to the paper's edge. */
        body {
            margin: 0;
            padding: 0 20mm;
            print-color-adjust: exact;
        }

        /* A heading never ends a page: it moves on with the start of what follows it, and a paragraph leaves at least three lines on each side of a break. */
        :is(h1, h2, h3, h4) {
            break-after: avoid-page;
            break-inside: avoid-page;
        }

        :is(h1, h2, h3, h4) + * {
            break-before: avoid-page;
        }

        :is(p, li) {
            orphans: 3;
            widows: 3;
        }

        .print-bleed {
            margin-inline: -20mm;
            padding-inline: 20mm;
        }
    </style>
</head>
<body class="bone bg-white font-sans text-copy text-zinc-800 antialiased">
    {{ $slot }}
</body>
</html>
