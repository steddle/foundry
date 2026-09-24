@php
    // Rendered here rather than framed from /foundry/mail, which production may not serve.
    $markdown = app(\Illuminate\Mail\Markdown::class);
    $html = (string) $markdown->render('foundry::lab.mail');
    $text = (string) $markdown->renderText('foundry::lab.mail');
@endphp

{{-- Its number comes from the include, where the page's order is kept. --}}
<foundry:numbered-section :$number name="Mail" note="Every mail and notification, through the foundry's theme">
    <div class="grid gap-8 lg:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
        <figure class="flex flex-col gap-3">
            {{-- As tall as the mail: the frame's document is its own, and the same origin. --}}
            <iframe srcdoc="{{ $html }}" title="The sample mail" loading="lazy" onload="this.style.height = this.contentDocument.documentElement.scrollHeight + 'px'" class="h-[48rem] w-full rounded-md border border-zinc-200 dark:border-zinc-700"></iframe>
            <foundry:text variant="small" tone="muted">As an inbox shows it, light whatever the reader's theme, with the lockup and Steddle's wordmark once php artisan foundry:assets has rendered them.</foundry:text>
        </figure>
        <figure class="flex flex-col gap-3">
            <div class="rounded-md border border-zinc-200 bg-zinc-25 p-5 text-small whitespace-pre-wrap text-zinc-800 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">{{ trim($text) }}</div>
            <foundry:text variant="small" tone="muted">The plain-text part, for a client that shows no HTML.</foundry:text>
        </figure>
    </div>
</foundry:numbered-section>
