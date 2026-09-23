{{--
    The bar over every page, the site's and the signed-in reader's alike,
    laid over the ink the page opens on. An imprint writes its own under
    resources/views/foundry/bar.blade.php: foundry:header with its links, its
    actions and, for a signed-in reader, the account menu and its items.
    foundry:layouts.site and foundry:layouts.app set it; the foundry's own is
    foundry:header without links.

    @group Shell

    @example As this imprint sets it
    @ground bare
    <div class="relative h-20 bg-zinc-900 ink">
        <foundry:bar />
    </div>
--}}
<foundry:header />
