// Maakt de thema-screenshot (1200×900, Weergave → Thema's) en het standaard deelbeeld (Open Graph, 1200×630).
// Gebruik: node tools/brand-assets.mjs [--base=http://127.0.0.1:9400]
import fs from 'node:fs';
import path from 'node:path';
import sharp from 'sharp';
import { chromium } from 'playwright';

const root = path.resolve(import.meta.dirname, '..');
const base = (process.argv.find((a) => a.startsWith('--base=')) || '--base=http://127.0.0.1:9400').slice(7);
const browser = await chromium.launch({ args: ['--hide-scrollbars'] });

async function capture(width, height) {
  const ctx = await browser.newContext({ viewport: { width, height }, reducedMotion: 'reduce' });
  await ctx.addCookies([{ name: 'playground_auto_login_already_happened', value: '1', url: base }]);
  const page = await ctx.newPage();
  await page.goto(base + '/', { waitUntil: 'networkidle', timeout: 120000 });
  await page.addStyleTag({ content: '.mc-demobar{display:none!important}' });
  await page.waitForTimeout(800);
  const buf = await page.screenshot();
  await ctx.close();
  return buf;
}

// Thema-screenshot.
const shot = await capture(1200, 900);
await sharp(shot).png({ compressionLevel: 9, palette: true, quality: 90 }).toFile(path.join(root, 'wp-content/themes/megacenter/screenshot.png'));

// Open Graph-beeld + registratie in de demo-manifest (de seeder importeert het als 'og-default').
const og = await capture(1200, 630);
const imgDir = path.join(root, 'wp-content/plugins/megacenter-core/demo-content/images');
const info = await sharp(og).webp({ quality: 80 }).toFile(path.join(imgDir, 'og-default.webp'));
const manifestFile = path.join(imgDir, 'manifest.json');
const manifest = JSON.parse(fs.readFileSync(manifestFile, 'utf8')).filter((m) => m.key !== 'og-default');
manifest.push({ key: 'og-default', file: 'og-default.webp', width: 1200, height: 630, alt: 'Megacenter Rijssen – FUN begint hier', credit: 'Eigen ontwerp (screenshot van de demo)', client: false, sizes: [] });
fs.writeFileSync(manifestFile, JSON.stringify(manifest, null, 2) + '\n');
console.log('screenshot.png en og-default.webp gemaakt', Math.round(info.size / 1024), 'KB');
await browser.close();
