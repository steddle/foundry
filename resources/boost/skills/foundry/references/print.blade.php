# Printed documents

`Steddle\Foundry\Pdf\Printer` makes a PDF from a page of the imprint's own, through Browsershot, so an imprint that prints has `puppeteer` where Browsershot finds it. `capture()` loads the page, refuses an error response, and pulls its stylesheets and their fonts into the HTML, fetching from the imprint's own host alone; `print()` prints that HTML, so a stored copy and its PDF are one render.

The page is `x-site.print.document` (`title`, `paper` A4 or Letter, `footer`): bone, 20 mm at the sides, the footer and the page count at the foot of every page, no heading left at the foot of a page and no paragraph broken with fewer than three lines on either side. It opens on `x-site.print.masthead`, ink bled to the paper's edge, and runs as `x-site.print.section`s holding `x-site.print.facts`; a section with `new-page`, a signature page, starts on a page of its own. The type scale is the site's, at a 12.5px rem.

The props of each are in `components/print.md`.
