@php
    // Mail clients neither load variables nor mix colours, so the tones between the imprint's ink and paper are mixed here, from six-digit hex.
    $mix = function (string $from, string $to, float $share): string {
        [$a, $b] = array_map(fn (string $hex): array => array_map('hexdec', str_split(ltrim($hex, '#'), 2)), [$from, $to]);

        return '#'.implode('', array_map(fn (int $x, int $y): string => sprintf('%02x', round($x + ($y - $x) * $share)), $a, $b));
    };

    $ink = config('imprint.ink', '#0b231c');
    $paper = config('imprint.paper', '#f1f2ea');
    $accent = config('imprint.mail.accent');

    $text = $mix($ink, $paper, 0.12);
    $muted = $mix($ink, $paper, 0.45);
    $rule = $mix($ink, $paper, 0.82);
    $sheet = $mix($paper, '#ffffff', 0.6);
    $sunken = $mix($paper, $ink, 0.05);

    // foundry.css's success-700 and danger-700, for Laravel's `success` and `error` buttons.
    $success = '#17614a';
    $danger = '#a03348';

    $serif = "Spectral, Georgia, 'Times New Roman', serif";
    $sans = "Chivo, 'Helvetica Neue', Helvetica, Arial, sans-serif";
@endphp
/* The foundry's mail, in the imprint's ink and paper. Spectral and Chivo where the reader has them, their fallbacks elsewhere: mail loads no web fonts. */

body,
body *:not(html):not(style):not(br):not(tr):not(code) {
    box-sizing: border-box;
    font-family: {!! $sans !!};
    position: relative;
}

body {
    -webkit-text-size-adjust: none;
    background-color: {{ $paper }};
    color: {{ $text }};
    height: 100%;
    line-height: 1.5;
    margin: 0;
    padding: 0;
    width: 100% !important;
}

p,
ul,
ol,
blockquote {
    line-height: 1.6;
    text-align: left;
}

a {
    color: {{ $ink }};
    text-decoration: underline;
}

a img {
    border: none;
}

h1 {
    color: {{ $ink }};
    font-family: {!! $serif !!};
    font-size: 24px;
    font-weight: 600;
    letter-spacing: -0.02em;
    line-height: 1.2;
    margin-top: 0;
    text-align: left;
}

h2 {
    color: {{ $ink }};
    font-family: {!! $serif !!};
    font-size: 19px;
    font-weight: 600;
    margin-top: 0;
    text-align: left;
}

h3 {
    color: {{ $ink }};
    font-size: 15px;
    font-weight: 600;
    margin-top: 0;
    text-align: left;
}

p {
    font-size: 16px;
    margin-top: 0;
}

p.sub {
    font-size: 13px;
}

img {
    max-width: 100%;
}

code {
    font-family: 'Chivo Mono', ui-monospace, Menlo, Consolas, monospace;
    font-size: 14px;
}

.preheader {
    display: none !important;
    max-height: 0;
    max-width: 0;
    mso-hide: all;
    opacity: 0;
    overflow: hidden;
    visibility: hidden;
}

.wrapper {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 100%;
    background-color: {{ $paper }};
    margin: 0;
    padding: 0;
    width: 100%;
}

.content {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 100%;
    margin: 0;
    padding: 0;
    width: 100%;
}

.header {
    padding: 32px 0 24px;
    text-align: center;
}

.header a {
    color: {{ $ink }};
    text-decoration: none;
}

/* Without a lockup image, the imprint's name, set as its wordmark is. */
.wordmark {
    color: {{ $ink }};
    font-family: {!! $serif !!};
    font-size: 24px;
    font-weight: 700;
    letter-spacing: -0.03em;
}

.logo {
    border: 0;
    display: inline-block;
}

.body {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 100%;
    background-color: {{ $paper }};
    border-bottom: 1px solid {{ $paper }};
    border-top: 1px solid {{ $paper }};
    margin: 0;
    padding: 0;
    width: 100%;
}

.inner-body {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 570px;
    background-color: {{ $sheet }};
    border: 1px solid {{ $rule }};
    border-radius: 5px;
    margin: 0 auto;
    padding: 0;
    width: 570px;
}

.content-cell {
    max-width: 100vw;
    padding: 32px;
}

.block {
    margin-bottom: 24px;
}

.block-below {
    border-top: 1px solid {{ $rule }};
    margin-top: 24px;
    padding-top: 24px;
}

.subcopy {
    border-top: 1px solid {{ $rule }};
    margin-top: 25px;
    padding-top: 25px;
}

.subcopy p {
    color: {{ $muted }};
    font-size: 14px;
}

.footer {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 570px;
    margin: 0 auto;
    padding: 0;
    text-align: center;
    width: 570px;
}

.footer p {
    color: {{ $muted }};
    font-size: 12px;
    line-height: 1.5;
    text-align: center;
}

.footer a {
    color: {{ $muted }};
    text-decoration: underline;
}

.table table {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 100%;
    border-collapse: collapse;
    margin: 30px auto;
    width: 100%;
}

.table th {
    border-bottom: 1px solid {{ $rule }};
    color: {{ $ink }};
    font-weight: 600;
    margin: 0;
    padding-bottom: 8px;
}

.table td {
    border-bottom: 1px solid {{ $rule }};
    color: {{ $text }};
    font-size: 15px;
    font-variant-numeric: tabular-nums;
    line-height: 18px;
    margin: 0;
    padding: 10px 0;
}

.action {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 100%;
    margin: 30px auto;
    padding: 0;
    text-align: center;
    width: 100%;
}

.button {
    -webkit-text-size-adjust: none;
    border-radius: 3px;
    display: inline-block;
    font-weight: 600;
    overflow: hidden;
    text-decoration: none;
}

/* The accent under ink, as a primary button on the site; without an accent, ink under paper. */
.button-blue,
.button-primary {
    background-color: {{ $accent ?? $ink }};
    border-bottom: 12px solid {{ $accent ?? $ink }};
    border-left: 18px solid {{ $accent ?? $ink }};
    border-right: 18px solid {{ $accent ?? $ink }};
    border-top: 12px solid {{ $accent ?? $ink }};
    color: {{ $accent ? $ink : $paper }};
}

.button-green,
.button-success {
    background-color: {{ $success }};
    border-bottom: 12px solid {{ $success }};
    border-left: 18px solid {{ $success }};
    border-right: 18px solid {{ $success }};
    border-top: 12px solid {{ $success }};
    color: #ffffff;
}

.button-red,
.button-error {
    background-color: {{ $danger }};
    border-bottom: 12px solid {{ $danger }};
    border-left: 18px solid {{ $danger }};
    border-right: 18px solid {{ $danger }};
    border-top: 12px solid {{ $danger }};
    color: #ffffff;
}

/* A sunken band, never a coloured left border. */
.panel {
    margin: 21px 0;
}

.panel-content {
    background-color: {{ $sunken }};
    border-radius: 3px;
    color: {{ $text }};
    padding: 16px;
}

.panel-content p {
    color: {{ $text }};
}

.panel-item {
    padding: 0;
}

.panel-item p:last-of-type {
    margin-bottom: 0;
    padding-bottom: 0;
}

.break-all {
    word-break: break-all;
}
