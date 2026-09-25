<?php

namespace Steddle\Foundry\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Mail\Markdown;

/**
 * `?text` shows the plain-text part.
 */
final class ShowMail
{
    public function __invoke(Request $request, Markdown $markdown): Response
    {
        return $request->has('text')
            ? response((string) $markdown->renderText('foundry::lab.mail'), headers: ['Content-Type' => 'text/plain; charset=UTF-8'])
            : response((string) $markdown->render('foundry::lab.mail'));
    }
}
