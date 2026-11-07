// Controleert de live demolinks op playground.wordpress.net (de site draait daar in geneste iframes).
// Gebruik: node tools/verify-live.mjs <github-gebruiker>/<repo> [branch=main] [--shots=map] [--met-werkbalk]
import path from 'node:path';
import { chromium } from 'playwright';

const args = process.argv.slice(2).filter((a) => !a.startsWith('--'));
const [repo = 'basvolgers/megacenter-wordpress-demo', branch = 'main'] = args;
const shots = (process.argv.find((a) => a.startsWith('--shots=')) || '').slice(8);
const mode = process.argv.includes('--met-werkbalk') ? '' : 'mode=seamless&';
const link = (f) => `https://playground.wordpress.net/?${mode}blueprint-url=https://raw.githubusercontent.com/${repo}/${branch}/playground/${f}`;

const browser = await chromium.launch({ args: ['--hide-scrollbars'] });
let ok = true;

// Wacht tot de WordPress-iframe (URL met /scope:) geladen is en aan de voorwaarde voldoet.
async function wpFrame(page, test, timeout = 240000) {
  const end = Date.now() + timeout;
  while (Date.now() < end) {
    for (const f of page.frames()) {
      if (!f.url().includes('/scope:') || f.url().includes('remote.html')) continue;
      try {
        if (await f.evaluate(test)) return f;
      } catch { /* frame navigeert nog */ }
    }
    await page.waitForTimeout(1000);
  }
  throw new Error('WordPress-frame niet op tijd geladen');
}

async function check(name, file, test, inspect) {
  const page = await (await browser.newContext({ viewport: { width: 1440, height: 900 } })).newPage();
  const errors = [];
  page.on('pageerror', (e) => errors.push(e.message));
  page.on('console', (m) => { if (m.type() === 'error' && m.location().url.includes('/scope:')) errors.push(m.text()); });
  const t0 = Date.now();
  await page.goto(link(file), { waitUntil: 'domcontentloaded', timeout: 120000 });
  const frame = await wpFrame(page, test);
  await page.waitForTimeout(2500);
  const info = await inspect(frame);
  const secs = ((Date.now() - t0) / 1000).toFixed(0);
  console.log(`\n== ${name} (${secs} s tot geladen)\n${link(file)}`);
  console.log(info);
  console.log(errors.length ? 'JS-fouten in de site: ' + errors.join(' | ') : 'geen JS-fouten in de site');
  if (errors.length) ok = false;
  if (shots) await page.screenshot({ path: path.join(shots, `live-${name}.png`) });
  return { page, frame, info };
}

// 1. Website
const site = await check('website', 'blueprint-demo.json',
  () => document.readyState === 'complete' && document.querySelectorAll('.mc-activities .wp-block-post').length > 0,
  (f) => f.evaluate(() => ({
    url: location.pathname,
    title: document.title,
    lang: document.documentElement.lang,
    h1: document.querySelector('h1')?.innerText.replace(/\s+/g, ' '),
    kaarten: document.querySelectorAll('.mc-activities .wp-block-post').length,
    beelden: [...document.images].filter((i) => i.complete && i.naturalWidth > 0).length + '/' + document.images.length,
    menu: [...document.querySelectorAll('.mc-nav-primary .wp-block-navigation-item__label')].map((e) => e.textContent).join(', '),
    ingelogd: !!document.getElementById('wpadminbar'),
  })));
if (site.info.kaarten < 8 || site.info.lang !== 'nl-NL') ok = false;

// Eten & drinken: het arrangementenbeeld moet de stockfoto zijn (82 KB), niet die van de opdrachtgever.
const scope = new URL(site.frame.url());
const scopeBase = scope.origin + scope.pathname.match(/^\/scope:[^/]+/)[0];
await site.frame.goto(scopeBase + '/eten-drinken/', { waitUntil: 'load', timeout: 120000 });
const food = await site.frame.evaluate(async () => {
  const img = [...document.images].find((i) => i.src.includes('hittesteen'));
  if (!img) return { gevonden: false };
  const full = img.src.replace(/-\d+(\.webp)$/, '$1');
  const bytes = (await (await fetch(full)).arrayBuffer()).byteLength;
  return { gevonden: true, h1: document.querySelector('h1')?.innerText, bytes, alt: img.alt };
});
console.log('eten & drinken:', food);
if (!food.gevonden || food.bytes === 62582) ok = false;

// 2. Beheer, direct bij de activiteiten
const cms = await check('beheer', 'blueprint-demo-cms.json',
  () => document.readyState === 'complete' && location.search.includes('post_type=activiteit') && !!document.querySelector('#the-list'),
  (f) => f.evaluate(() => ({
    url: location.pathname + location.search,
    title: document.title,
    activiteiten: document.querySelectorAll('#the-list tr').length,
    volgorde: [...document.querySelectorAll('#the-list .row-title')].map((e) => e.textContent).join(', '),
    menu: [...document.querySelectorAll('#adminmenu > li > a .wp-menu-name')].map((e) => e.textContent.trim()).filter(Boolean).slice(0, 14).join(', '),
  })));
if (cms.info.activiteiten < 8) ok = false;

await browser.close();
console.log(ok ? '\nRESULTAAT: beide links werken' : '\nRESULTAAT: er gaat iets mis, zie hierboven');
process.exit(ok ? 0 : 1);
