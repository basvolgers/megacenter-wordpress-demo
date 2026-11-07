// Technische QA voor de demo-site.
// - JS-console-fouten en mislukte verzoeken op alle pagina's
// - axe-core (WCAG 2.2 AA) – meldt serious/critical
// - horizontale scroll op 360/390/768/1024/1440/1920 px
// - tap-targets kleiner dan 44 px (knoppen, menu, chips)
// - alle interne links: geen 404's
// - aanvraagformulier end-to-end (foutmeldingen + succesvolle aanvraag in het beheer)
// Gebruik: node tools/qa.mjs [--base=http://127.0.0.1:9400] [--skip=form,links]
import { chromium } from 'playwright';
import { AxeBuilder } from '@axe-core/playwright';
import { login } from './lib-login.mjs';

const arg = (n, d) => (process.argv.find((a) => a.startsWith(`--${n}=`)) || '').split('=')[1] || d;
const base = arg('base', 'http://127.0.0.1:9400');
const skip = arg('skip', '').split(',');
const PAGES = ['/', '/activiteiten/', '/activiteiten/soort/voor-kids/', '/activiteiten/lasergame/', '/activiteiten/sim-racing/', '/activiteiten/kids-jungle/', '/kinderfeestjes/', '/groepsuitjes/', '/zakelijk/', '/eten-drinken/', '/contact/', '/reserveren/?voor=bowling', '/privacyverklaring/', '/cookieverklaring/', '/bestaat-niet/'];
const WIDTHS = [360, 390, 768, 1024, 1440, 1920];
const problems = [];
const note = (type, page, msg) => { problems.push({ type, page, msg }); console.log(`  ✗ [${type}] ${page} – ${msg}`); };

const browser = await chromium.launch();
async function visitorContext(width = 1440) {
  const ctx = await browser.newContext({ viewport: { width, height: 900 } });
  await ctx.addCookies([{ name: 'playground_auto_login_already_happened', value: '1', url: base }]);
  return ctx;
}

// 1–4. Per pagina.
console.log('Pagina\'s controleren…');
for (const url of PAGES) {
  const ctx = await visitorContext(1440);
  const page = await ctx.newPage();
  page.on('console', (m) => { if (m.type() === 'error' && !(url.includes('bestaat-niet') && m.text().includes('404'))) note('console', url, m.text().slice(0, 200)); });
  page.on('pageerror', (e) => note('js', url, e.message.slice(0, 200)));
  page.on('requestfailed', (r) => note('request', url, `${r.url()} ${r.failure()?.errorText}`));
  page.on('response', (r) => { if (r.status() >= 400 && !r.url().includes('bestaat-niet')) note('http', url, `${r.status()} ${r.url()}`); });
  const res = await page.goto(base + url, { waitUntil: 'networkidle', timeout: 120000 });
  const expected = url.includes('bestaat-niet') ? 404 : 200;
  if (res.status() !== expected) note('status', url, `status ${res.status()}, verwacht ${expected}`);

  // Externe verzoeken bij laden (AVG): alles moet van 127.0.0.1 komen.
  const external = await page.evaluate(() => performance.getEntriesByType('resource').map((e) => e.name).filter((n) => !n.startsWith(location.origin) && !n.startsWith('data:')));
  external.forEach((e) => note('extern', url, e));

  // Eén H1.
  const h1 = await page.locator('h1').count();
  if (h1 !== 1) note('h1', url, `${h1} h1-elementen`);

  // Reveal-animaties eerst afspelen (anders meet axe contrast van elementen met opacity 0).
  await page.evaluate(() => document.querySelectorAll('.mc-reveal').forEach((el) => el.classList.add('is-visible')));
  await page.waitForTimeout(900);

  // axe-core.
  const axe = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa']).analyze();
  axe.violations.filter((v) => ['serious', 'critical'].includes(v.impact)).forEach((v) => note('axe', url, `${v.id} (${v.impact}): ${v.nodes.length}× – ${v.nodes[0]?.target?.join(' ')}`));
  const minor = axe.violations.filter((v) => !['serious', 'critical'].includes(v.impact));
  if (minor.length) console.log(`  · axe minor/moderate op ${url}: ${minor.map((v) => v.id).join(', ')}`);

  // Horizontale scroll + tap targets op alle breedtes.
  for (const w of WIDTHS) {
    await page.setViewportSize({ width: w, height: 900 });
    await page.waitForTimeout(150);
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
    if (overflow > 1) note('overflow', url, `${overflow}px horizontale scroll op ${w}px`);
    if (w === 390) {
      const small = await page.evaluate(() => {
        const out = [];
        document.querySelectorAll('a, button, summary, input, select, textarea').forEach((el) => {
          if (el.closest('p, label, li:not(.wp-block-navigation-item), .mc-marquee, .mc-form__hp, dd, td') && !el.matches('.wp-block-button__link, .mc-filter__chip')) return; // tekstlinks in lopende tekst (WCAG-uitzondering)
          if (el.matches('.skip-link, .mc-card__title a, .mc-tile__title a')) return; // verborgen skiplink / link over de hele kaart
          const r = el.getBoundingClientRect();
          const s = getComputedStyle(el);
          if (!r.width || s.visibility === 'hidden' || s.display === 'none' || el.closest('[hidden], [aria-hidden="true"]')) return;
          if (el.type === 'checkbox' || el.type === 'hidden') return;
          if (r.height < 44 && r.width < 44) out.push(`${el.tagName.toLowerCase()}.${(el.className || '').toString().split(' ')[0]} "${(el.textContent || el.getAttribute('aria-label') || '').trim().slice(0, 30)}" ${Math.round(r.width)}×${Math.round(r.height)}`);
          else if (r.height < 32) out.push(`${el.tagName.toLowerCase()}.${(el.className || '').toString().split(' ')[0]} "${(el.textContent || '').trim().slice(0, 30)}" ${Math.round(r.width)}×${Math.round(r.height)}`);
        });
        return out;
      });
      small.forEach((s) => note('tap', url, s));
    }
  }
  await ctx.close();
}

// 5. Interne links.
if (!skip.includes('links')) {
  console.log('Interne links controleren…');
  const ctx = await visitorContext();
  const page = await ctx.newPage();
  const seen = new Set();
  for (const url of PAGES.filter((u) => !u.includes('bestaat-niet'))) {
    await page.goto(base + url, { waitUntil: 'domcontentloaded', timeout: 120000 });
    const links = await page.$$eval('a[href]', (as) => as.map((a) => a.href));
    links.filter((l) => l.startsWith(base)).map((l) => l.split('#')[0]).forEach((l) => seen.add(l));
  }
  for (const link of seen) {
    const r = await ctx.request.get(link, { maxRedirects: 3, timeout: 120000 });
    if (r.status() >= 400) note('link', link, `status ${r.status()}`);
  }
  console.log(`  ${seen.size} unieke interne links gecontroleerd`);
  await ctx.close();
}

// 6. Formulier end-to-end.
if (!skip.includes('form')) {
  console.log('Aanvraagformulier testen…');
  const ctx = await visitorContext(1280);
  const page = await ctx.newPage();
  await page.goto(base + '/kinderfeestjes/', { waitUntil: 'networkidle' });
  await page.waitForTimeout(3200); // tijdval van 3 seconden
  await page.fill('#mc-form-kinderfeest-name', 'Test Ouder');
  await page.fill('#mc-form-kinderfeest-email', 'geen-email');
  await Promise.all([page.waitForURL(/mc_form=/), page.click('#aanvraag-formulier button[type=submit]')]);
  const errors = await page.locator('.mc-form__errors li').count();
  if (errors < 3) note('form', '/kinderfeestjes/', `verwachtte foutmeldingen, kreeg ${errors}`);
  else console.log(`  ✓ ${errors} foutmeldingen getoond, ingevulde naam bewaard: ${await page.inputValue('#mc-form-kinderfeest-name')}`);
  const date = new Date(Date.now() + 14 * 864e5).toISOString().slice(0, 10);
  await page.fill('#mc-form-kinderfeest-email', 'test@voorbeeld.nl');
  await page.fill('#mc-form-kinderfeest-date', date);
  await page.fill('#mc-form-kinderfeest-count', '10');
  await page.fill('#mc-form-kinderfeest-age', '8');
  await page.selectOption('#mc-form-kinderfeest-package', 'neon');
  await page.fill('#mc-form-kinderfeest-message', 'QA-test: graag lasergame + glowgolf.');
  await page.check('#mc-form-kinderfeest-consent');
  await page.waitForTimeout(3200);
  await Promise.all([page.waitForURL(/mc_form=bedankt/), page.click('#aanvraag-formulier button[type=submit]')]);
  const ok = await page.locator('.mc-form__success').count();
  if (!ok) note('form', '/kinderfeestjes/', 'geen bedankmelding');
  else console.log('  ✓ bedankmelding getoond');
  await ctx.close();

  const adminCtx = await browser.newContext();
  const admin = await adminCtx.newPage();
  await login(admin, base);
  await admin.goto(base + '/wp-admin/edit.php?post_type=aanvraag', { waitUntil: 'domcontentloaded' });
  const row = await admin.locator('#the-list tr').first().innerText().catch(() => '');
  if (!row.includes('Test Ouder')) note('form', 'beheer', 'aanvraag niet gevonden in het beheer');
  else console.log('  ✓ aanvraag staat in het beheer: ' + row.split('\n')[0]);
  await adminCtx.close();
}

await browser.close();
console.log(problems.length ? `\n${problems.length} aandachtspunt(en).` : '\nGeen problemen gevonden.');
process.exitCode = problems.length ? 1 : 0;
