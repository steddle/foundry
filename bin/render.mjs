// Renders each job's page to a PNG in one Chromium: Playwright's own, never
// the reader's browser. `playwright` resolves from the site's node_modules,
// a parent of the vendor/ directory this script sits in.
import { readFileSync } from 'node:fs';
import { chromium } from 'playwright';

const jobs = JSON.parse(readFileSync(process.argv[2], 'utf8'));
const browser = await chromium.launch();

try {
    for (const job of jobs) {
        const page = await browser.newPage({ viewport: { width: job.width, height: job.height }, ignoreHTTPSErrors: true });
        await page.goto(job.url, { waitUntil: 'networkidle' });
        await page.evaluate(() => document.fonts.ready);
        await page.screenshot({ path: job.path, omitBackground: job.transparent });
        await page.close();
    }
} finally {
    await browser.close();
}
