<?php

namespace Steddle\Foundry\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Mail\Markdown;
use Illuminate\Notifications\AnonymousNotifiable;
use Steddle\Foundry\Auth\LoginLink;

/**
 * `?text` shows the plain-text part. `?login` shows the sign-in mail, for
 * the client `imprint.auth.client` answers for this request.
 */
final class ShowMail
{
    public function __invoke(Request $request, Markdown $markdown): Response
    {
        return match (true) {
            $request->has('login') => response((string) (new LoginLink(url('/login/magic'), LoginLink::clientFor($request)))->toMail(new AnonymousNotifiable)->render()),
            $request->has('text') => response((string) $markdown->renderText('foundry::lab.mail'), headers: ['Content-Type' => 'text/plain; charset=UTF-8']),
            default => response((string) $markdown->render('foundry::lab.mail')),
        };
    }
}
