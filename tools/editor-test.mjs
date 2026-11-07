// Editortest: opent pagina's en activiteiten in de block editor en controleert op ongeldige blokken en JS-fouten.
// Gebruik: node tools/editor-test.mjs [--base=http://127.0.0.1:9400] [--shot=pad.png]
import { chromium } from 'playwright';
import { login } from './lib-login.mjs';

const arg = (n, d) => (process.argv.find((a) => a.startsWith(`--${n}=`)) || '').split('=')[1] || d;
const base = arg('base', 'http://127.0.0.1:9400');
const shot = arg('shot', '');
const targets = [
  ['pages', 'home'], ['pages', 'kinderfeestjes'], ['pages', 'zakelijk'], ['pages', 'contact'], ['pages', 'reserveren'],
  ['activiteit', 'lasergame'], ['activiteit', 'sim-racing'],
];

const browser = await chromium.launch();
const ctx = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
const page = await ctx.newPage();
const errors = [];
page.on('pageerror', (e) => errors.push(e.message));
page.on('console', (m) => { if (m.type() === 'error') errors.push(m.text()); });
await login(page, base);

let bad = 0;
for (const [type, slug] of targets) {
  const res = await ctx.request.get(`${base}/wp-json/wp/v2/${type}?slug=${slug}&_fields=id`);
  const [item] = await res.json();
  if (!item) { console.log('✗ niet gevonden:', type, slug); bad++; continue; }
  errors.length = 0;
  await page.goto(`${base}/wp-admin/post.php?post=${item.id}&action=edit`, { waitUntil: 'domcontentloaded', timeout: 180000 });
  await page.waitForFunction(() => window.wp?.data?.select('core/block-editor')?.getBlocks()?.length > 0, null, { timeout: 180000 });
  await page.waitForTimeout(2500);
  const result = await page.evaluate(() => {
    const invalid = [];
    let total = 0;
    const walk = (blocks) => blocks.forEach((b) => { total++; if (b.isValid === false) invalid.push(b.name); walk(b.innerBlocks || []); });
    walk(wp.data.select('core/block-editor').getBlocks());
    return { total, invalid };
  });
  const warnings = await page.frameLocator('iframe[name="editor-canvas"]').locator('.block-editor-warning').count().catch(() => 0);
  const ok = !result.invalid.length && !warnings;
  if (!ok) bad++;
  console.log(`${ok ? '✓' : '✗'} ${type}/${slug}: ${result.total} blokken, ongeldig: ${result.invalid.length ? result.invalid.join(', ') : 'geen'}, waarschuwingen: ${warnings}${errors.length ? ', JS-fouten: ' + errors.slice(0, 3).join(' | ') : ''}`);
  if (shot && slug === 'lasergame') await page.screenshot({ path: shot.replace('.png', `-${slug}.png`) });
  if (shot && slug === 'kinderfeestjes') await page.screenshot({ path: shot.replace('.png', `-${slug}.png`) });
}
await browser.close();
process.exitCode = bad ? 1 : 0;
