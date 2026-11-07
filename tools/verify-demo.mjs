// Controleert een (verse) demo-installatie: homepage, activiteiten, beelden, beheer.
// Gebruik: node tools/verify-demo.mjs <url-van-de-site> [--shot=pad.png]
import { chromium } from 'playwright';

const base = process.argv[2] || 'http://127.0.0.1:9500';
const shot = (process.argv.find((a) => a.startsWith('--shot=')) || '').slice(7);
const browser = await chromium.launch({ args: ['--hide-scrollbars'] });
const page = await (await browser.newContext({ viewport: { width: 1440, height: 900 } })).newPage();
const errors = [];
page.on('pageerror', (e) => errors.push(e.message));
await page.goto(base + '/', { waitUntil: 'networkidle', timeout: 180000 });
const r = await page.evaluate(() => ({
  title: document.title,
  h1: document.querySelector('h1')?.innerText.replace(/\s+/g, ' ').slice(0, 60),
  cards: document.querySelectorAll('.mc-activities .wp-block-post').length,
  images: [...document.images].filter((i) => i.complete && i.naturalWidth > 0).length + '/' + document.images.length,
  menu: [...document.querySelectorAll('.mc-nav-primary .wp-block-navigation-item__label')].map((e) => e.textContent).join(', '),
  lang: document.documentElement.lang,
  adminbar: !!document.getElementById('wpadminbar'),
}));
console.log(r);
if (shot) await page.screenshot({ path: shot });
await page.goto(base + '/wp-admin/edit.php?post_type=activiteit', { waitUntil: 'domcontentloaded', timeout: 180000 });
console.log('beheer:', await page.title(), '·', await page.locator('#the-list tr').count(), 'activiteiten');
console.log(errors.length ? 'JS-fouten: ' + errors.join(' | ') : 'geen JS-fouten');
await browser.close();
