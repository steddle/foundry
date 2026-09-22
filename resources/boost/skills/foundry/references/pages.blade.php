@php
    $facts = \Steddle\Foundry\Boost\Skill::facts();
@endphp
# Composing a page

## Sections

A page is a run of sections. The foundry holds the base ones under `x-site.sections.*`: `hero` and `page-title` to open a page, `split` (words beside a `figure` slot), `steps`, `features`, `faq` and `cta` between, and `closing` in the footer's slot.

Every one takes the same words, `eyebrow`, `title` and `lead`, the lede, as props, or `eyebrow` and `lead` as slots where they hold markup; its items as a list of pairs or of rows keyed the same; the buttons in an `actions` slot, which it sets in `x-site.actions`; whatever the default slot holds after the rest; and `id`, and `sunken` or `scene` where the band takes one: `closing` takes its ground from the footer. Every one sets its words through `x-site.section-head`, beside each other or `stacked`, so they read alike on every band.

A section that is a base one filled with copy is written in the page, never kept as a component, and so is a section bound to the page's Livewire state, a form. A section with markup of its own is the imprint's, under `resources/views/components/site/sections/`, built on a base one where its shape allows; a figure a section sets more than once is a component of its own beside it. A base section enters the foundry when a page uses it.

`x-site.section` and `x-site.numbered-section` are the plain bands, each with `sunken` for a band a step below the page.

## Hero and scenes

`x-site.sections.hero` opens a page on ink under the header: `scene`, `align` (`start` or `center`, which chooses the scrim), `tall`, and a `marked` phrase in its `title`; `actions` and then the slot follow the words. `x-site.section` takes `scene` and `align` too, for an ink band further down a page. Both draw the scrim `x-site.scene` holds for `start` or `center`, measured over every imprint's scenes; the footer keeps its own. `x-site.actions` is the row of buttons a band ends on.

A scene is framed once, in `config/imprint.php` under `scenes`, name => its object-position classes, and every `x-site.scene` of it reads that; a site's stylesheet sources `config/imprint.php` so the classes are built. {{ $facts['name'] }}'s scenes: {!! $facts['scenes'] === [] ? 'none yet' : \Steddle\Foundry\Boost\Skill::codes($facts['scenes']) !!}.

The `grain` utility in `foundry.css` goes on ink bands only.

## Header and footer

`x-foundry::site.nav` takes the links, the home address, an `actions` slot and whether a phone folds them into a menu. `x-foundry::site.footer` takes the scene, the links or an `items` slot and whether the lockup is endorsed, lays one scrim over the scene, rules off a closing section set in its slot, and closes on the colophon: the disclaimer from `config/imprint.php` and the copyright. A site's own `x-site.nav` and `x-site.footer` wrap them with its content.

## The page document

`x-site.page` is every page's document: head, nav, content, footer with the `closing` slot, the OG Kit template and Flux unless `flux` is false; a site's `layouts/site` wraps it. `x-site.sections.closing` is the page's last word in the footer's slot, centred or, with `align`, at the start.

`x-site.ink-page` is a page of its own on ink, an error or a sign-in, with `scene` or `card`. It and the footer close on `x-site.service-line`: `imprint.disclaimer`, the copyright, and 'A service by Steddle' where `imprint.endorsed` holds, true unless the imprint is Steddle itself.

What `x-site.head` writes is in `search-and-agents.md`.

## The app shell

`x-site.app-page` is a signed-in page's document, on bone and kept out of search: `x-site.app-nav`, the content in a container, the service line under a rule and `x-site.toasts`, the toast group persisted across `wire:navigate`, which `x-site.ink-page` sets too.

`x-site.app-nav` is its bar, in flow and ruled off: the lockup, the `links` with the one whose address the current one equals or lies under marked, the `actions` slot and `x-site.account-menu`, the reader's Flux avatar (`$user->initials` from `Steddle\Foundry\Concerns\HasInitials`, over Nameable) opening their name and address, the `menu` slot's items and logging out where `logout` is a route.

`x-site.page-head` opens the content: the `breadcrumbs` above it, the title at heading-2 with a `status` beside it, a line under it and the page's `actions`. `x-site.rows` rules a list of `x-site.record-row`s, each a link over its title, status and `meta` with its `actions` outside it; `x-site.empty` stands where a list holds nothing yet; `x-site.field-row` sets one setting's label and description beside its control, the parent ruling the rows.

A step that can't be undone takes three presses, never a dialog: `x-site.confirm-button`, or `x-site.confirm-item` in a menu. The words go from the label to 'Click again' to 'One more time' as the danger ramp fills the control a third at a time, and only the third press runs its `action`, an Alpine expression, never a `wire:click` on the control.

## Long-form text

Long-form text is the `longform` utility in `foundry.css`, and a link inside running text the `link` utility.
