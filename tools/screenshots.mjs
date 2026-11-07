// Full-page screenshots van alle P1- en P2-pagina's (390 en 1440 px, home ook 768 px).
// Gebruik: node tools/screenshots.mjs [uitmap] [--base=http://127.0.0.1:9400] [--only=home,lasergame] [--widths=390,1440]
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const args = process.argv.slice(2);
const opt = (name, def) => (args.find((a) => a.startsWith(`--${name}=`)) || '').split('=')[1] || def;
const outDir = path.resolve(args.find((a) => !a.startsWith('--')) || 'deliverables/screenshots');
const base = opt('base', 'http://127.0.0.1:9400');
const only = opt('only', '').split(',').filter(Boolean);
const widthsOverride = opt('widths', '').split(',').filter(Boolean).map(Number);

export const PAGES = [
  ['home', '/', [390, 768, 1440]],
  ['activiteiten', '/activiteiten/', [390, 1440]],
  ['lasergame', '/activiteiten/lasergame/', [390, 1440]],
  ['sim-racing', '/activiteiten/sim-racing/', [390, 1440]],
  ['kinderfeestjes', '/kinderfeestjes/', [390, 1440]],
  ['contact', '/contact/', [390, 1440]],
  ['reserveren', '/reserveren/?voor=lasergame', [390, 1440]],
  ['groepsuitjes', '/groepsuitjes/', [390, 1440]],
  ['zakelijk', '/zakelijk/', [390, 1440]],
  ['eten-drinken', '/eten-drinken/', [390, 1440]],
  ['privacy', '/privacyverklaring/', [390, 1440]],
  ['404', '/deze-pagina-bestaat-niet/', [390, 1440]],
];

fs.mkdirSync(outDir, { recursive: true });
const browser = await chromium.launch({ args: ['--hide-scrollbars'] });

// Takenlijst opbouwen (genummerd in vaste volgorde), daarna 3 tegelijk uitvoeren.
const jobs = [];
let n = 0;
for (const [name, url, widths] of PAGES) {
  for (const width of widthsOverride.length ? widthsOverride : widths) {
    n++;
    if (only.length && !only.includes(name)) continue;
    jobs.push({ name, url, width, file: path.join(outDir, `${String(n).padStart(2, '0')}-${name}-${width}.png`) });
  }
}

async function shoot({ url, width, file }) {
  const context = await browser.newContext({ viewport: { width, height: width < 600 ? 844 : 900 }, deviceScaleFactor: 1 });
  // Niet automatisch inloggen (Playground --login): bezoekersweergave zonder adminbalk.
  await context.addCookies([{ name: 'playground_auto_login_already_happened', value: '1', url: base }]);
  const page = await context.newPage();
  await page.goto(base + url, { waitUntil: 'networkidle', timeout: 180000 });
  // Scroll door de pagina zodat reveal-animaties afspelen, daarna terug naar boven.
  await page.evaluate(async () => {
    for (let y = 0; y < document.body.scrollHeight; y += 500) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 60)); }
    window.scrollTo(0, 0);
    document.querySelectorAll('.mc-reveal').forEach((el) => el.classList.add('is-visible'));
  });
  // Wacht tot alle beelden echt geladen zijn. (sizes="auto" van lazy beelden kiest bij een
  // full-page capture opnieuw een bron; daarom vooraf vastzetten.)
  await page.evaluate(async () => {
    document.querySelectorAll('img').forEach((img) => {
      img.loading = 'eager';
      if (img.sizes && img.sizes.startsWith('auto')) img.sizes = img.sizes.replace(/^auto,\s*/, '');
    });
    await new Promise((r) => setTimeout(r, 400));
    await Promise.all([...document.images].map((img) => (img.complete ? null : new Promise((r) => { img.onload = img.onerror = r; }))));
    await Promise.all([...document.images].map((img) => img.decode().catch(() => null)));
  });
  // Plakkende elementen (mobiele reserveerbalk) in de begintoestand zetten.
  await page.evaluate(() => document.documentElement.classList.remove('mc-scrolled'));
  await page.waitForTimeout(900);
  await page.screenshot({ path: file, fullPage: true });
  console.log('ok', path.basename(file));
  await context.close();
}

const queue = [...jobs];
await Promise.all(Array.from({ length: 3 }, async () => { while (queue.length) await shoot(queue.shift()); }));
await browser.close();
