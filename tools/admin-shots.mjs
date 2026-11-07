// Screenshots van het beheer (CMS-ervaring voor de pitch).
// Gebruik: node tools/admin-shots.mjs [--base=http://127.0.0.1:9400]
import path from 'node:path';
import { chromium } from 'playwright';
import { login } from './lib-login.mjs';

const base = (process.argv.find((a) => a.startsWith('--base=')) || '--base=http://127.0.0.1:9400').slice(7);
const out = path.resolve('deliverables/screenshots');
const browser = await chromium.launch({ args: ['--hide-scrollbars'] });
const ctx = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
const page = await ctx.newPage();
const errors = [];
page.on('pageerror', (e) => errors.push(e.message));
await login(page, base);

const shots = [
  ['30-beheer-dashboard', '/wp-admin/index.php', null],
  ['31-beheer-instellingen', '/wp-admin/admin.php?page=megacenter', null],
  ['32-beheer-aanvragen', '/wp-admin/edit.php?post_type=aanvraag', null],
  ['33-beheer-activiteiten', '/wp-admin/edit.php?post_type=activiteit', null],
];
for (const [name, url] of shots) {
  await page.goto(base + url, { waitUntil: 'networkidle', timeout: 120000 });
  await page.screenshot({ path: path.join(out, `${name}.png`), fullPage: name.includes('instellingen') });
  console.log('ok', name);
}

// Activiteit bewerken: editor + uitgeklapte meta box met de velden.
const res = await ctx.request.get(`${base}/wp-json/wp/v2/activiteit?slug=lasergame&_fields=id`);
const [{ id }] = await res.json();
await page.goto(`${base}/wp-admin/post.php?post=${id}&action=edit`, { waitUntil: 'domcontentloaded', timeout: 180000 });
await page.waitForFunction(() => window.wp?.data?.select('core/block-editor')?.getBlocks()?.length > 0, null, { timeout: 180000 });
await page.waitForTimeout(1500);
// Meta-vakken-paneel openen en groter maken zodat de velden zichtbaar zijn.
await page.evaluate(() => document.querySelector('.edit-post-meta-boxes-main button[aria-expanded="false"]')?.click());
await page.waitForTimeout(800);
await page.evaluate(() => {
  const el = document.querySelector('.edit-post-meta-boxes-main');
  if (el) el.style.height = '700px';
});
await page.waitForTimeout(600);
await page.screenshot({ path: path.join(out, '34-beheer-activiteit-velden.png') });
console.log('ok 34-beheer-activiteit-velden', errors.length ? 'JS-fouten: ' + errors.join(' | ') : '');
await browser.close();
