@php
    $facts = \Steddle\Foundry\Boost\Skill::facts();
@endphp
# Composing a page

## Sections

A page is a run of sections. The foundry holds the base ones under `foundry:sections.*`: `hero` and `page-title` to open a page, `split` (words beside a `figure` slot), `steps`, `features`, `faq` and `cta` between, and `closing` in the footer's slot.

Every one takes the same words, `eyebrow`, `title` and `lead`, the lede, as props, or `eyebrow` and `lead` as slots where they hold markup; its items as a list of pairs or of rows keyed the same; the buttons in an `actions` slot, which it sets in `foundry:actions`; whatever the default slot holds after the rest; and `id`, and `sunken` or `scene` where the band takes one: `closing` takes its ground from the footer. Every one sets its words through `foundry:section-head`, beside each other or `stacked`, so they read alike on every band.

A section that is a base one filled with copy is written in the page, never kept as a component, and so is a section bound to the page's Livewire state, a form. A section with markup of its own is the imprint's, under `resources/views/components/site/sections/`, built on a base one where its shape allows; a figure a section sets more than once is a component of its own beside it. A base section enters the foundry when a page uses it.

`foundry:section` and `foundry:numbered-section` are the plain bands, each with `sunken` for a band a step below the page.

## Hero and scenes

`foundry:sections.hero` opens a page on ink under the header: `scene`, `align` (`start` or `center`, which chooses the scrim), `tall`, and a `marked` phrase in its `title`; `actions` and then the slot follow the words. `foundry:section` takes `scene` and `align` too, for an ink band further down a page. Both draw the scrim `foundry:scene` holds for `start` or `center`, measured over every imprint's scenes; the footer keeps its own. `foundry:actions` is the row of buttons a band ends on.

A scene is framed once, in `config/imprint.php` under `scenes`, name => its object-position classes, and every `foundry:scene` of it reads that; a site's stylesheet sources `config/imprint.php` so the classes are built. {{ $facts['name'] }}'s scenes: {!! $facts['scenes'] === [] ? 'none yet' : \Steddle\Foundry\Boost\Skill::codes($facts['scenes']) !!}.

The `grain` utility in `foundry.css` goes on ink bands only.

## The bar and the footer

`foundry:bar` is the bar over every page, the site's and the signed-in reader's alike. The imprint writes its own in `resources/views/foundry/bar.blade.php`, as it writes its lockup and mark: `foundry:header` with its links, its home address, the `actions` slot, whether a phone folds them into a menu and, for a signed-in reader, `user` and the `account` slot's items in the account menu. `foundry:header` marks the link whose address the current one equals or lies under. Where a signed-in reader reaches a page of their own, such as settings, it is an item in the account menu, not a link in the bar.

`foundry:footer` takes the scene, the links or an `items` slot and whether the lockup is endorsed, lays one scrim over the scene, rules off a closing section set in its slot, and closes on the colophon: the disclaimer from `config/imprint.php` and the copyright. The site hands it its links in `layouts/site`, through the page's `footer` slot.

## The page document

`foundry:layouts.site` is every page's document: head, `foundry:bar`, content, footer with the `closing` slot, the OG Kit template and Flux unless `flux` is false; a site's `layouts/site` wraps it and hands it the footer through the `footer` slot. `foundry:sections.closing` is the page's last word in the footer's slot, centred or, with `align`, at the start.

`foundry:layouts.auth` is a page for signing in, a second step or a consent, without the site's navigation; an error page stands on it too. On ink, with `scene` or `card`. It and the footer close on `foundry:service-line`: `imprint.disclaimer`, the copyright, and 'A service by Steddle' where `imprint.endorsed` holds, true unless the imprint is Steddle itself.

The foundry answers 403, 404, 419, 429, 500 and 503 with `foundry:layouts.error`, on `foundry:layouts.auth`: the code, a title and a lead from `foundry::errors`, and a button home, or trying again on a 503. The imprint words them in `lang/vendor/foundry/{locale}/errors.php`, only the lines it words differently, and names their scene under `errors.scene` in `config/imprint.php`. A page whose actions are the imprint's own, a search on its 404, is `resources/views/errors/{code}.blade.php` setting `foundry:layouts.error` with an `actions` slot.

What `foundry:head` writes is in `search-and-agents.md`.

## The app shell

`foundry:layouts.app` is a signed-in page's document, kept out of search: `foundry:bar`, the content edge to edge, the service line under a rule and `foundry:toasts`, the toast group persisted across `wire:navigate`, which `foundry:layouts.auth` sets too. Its `nav` slot takes a bar in place of `foundry:bar`, for a page that shows its reader less than the rest.

Every signed-in page opens on `foundry:app.band`, the ink the bar lies on, with an `foundry:page-head` in it, and sets the rest in a `foundry:container` with `py-10` under it. `foundry:account-menu` is the reader's Flux avatar (`$user->initials` from `Steddle\Foundry\Concerns\HasInitials`, over Nameable) opening their name and address, the imprint's items and logging out where `logout` is a route.

`foundry:panel` is one part of a page on a sheet lifted off it, its `title`, its `lead` and what it holds; a settings page is a run of them in one column of `max-w-3xl`, and one that submits as a whole is `as="form"`. `foundry:page-head` opens the content: the `breadcrumbs` above it, the title at heading-2 with a `status` beside it, a line under it and the page's `actions`. `foundry:rows` rules a list of `foundry:record-row`s, each a link over its title, status and `meta` with its `actions` outside it; `foundry:empty` stands where a list holds nothing yet; `foundry:field-row` sets one setting's label and description beside its control, the parent ruling the rows.

A step that can't be undone takes three presses, never a dialog: `foundry:confirm-button`, or `foundry:confirm-item` in a menu. The words go from the label to 'Click again' to 'One more time' as the danger ramp fills the control a third at a time, and only the third press runs its `action`, an Alpine expression, never a `wire:click` on the control.

An imprint with laravel/passkeys lists the reader's passkeys with `foundry:passkeys`, on a page whose Livewire component uses `Steddle\Foundry\Concerns\ManagesPasskeys`, and signs them in with `foundry:passkey-sign-in` under its sign-in form. Both load `vendor/steddle/foundry/resources/js/passkeys.js`, which stands among the imprint's Vite inputs.

## Long-form text

Long-form text is the `longform` utility in `foundry.css`, and a link inside running text the `link` utility.
