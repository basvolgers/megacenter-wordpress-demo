// Neemt een rustige scroll door de homepage op (desktop 1440×900 en mobiel 390×844), met een klik
// naar een activiteit, en zet de opnames om naar mp4 (ffmpeg-static) zodat je ze makkelijk kunt delen.
// Gebruik: node tools/video.mjs [--base=http://127.0.0.1:9400]
import fs from 'node:fs';
import path from 'node:path';
import { execFileSync } from 'node:child_process';
import ffmpeg from 'ffmpeg-static';
import { chromium } from 'playwright';

const root = path.resolve(import.meta.dirname, '..');
const base = (process.argv.find((a) => a.startsWith('--base=')) || '--base=http://127.0.0.1:9400').slice(7);
const out = path.join(root, 'deliverables/video');
const tmp = path.join(out, '.raw');
fs.mkdirSync(tmp, { recursive: true });

const browser = await chromium.launch({ args: ['--hide-scrollbars'] });

async function record(name, viewport, isMobile) {
  // Eerst "opwarmen" zodat de (trage) Playground-server alles in de cache heeft.
  const warm = await browser.newContext({ viewport });
  await warm.addCookies([{ name: 'playground_auto_login_already_happened', value: '1', url: base }]);
  const wp = await warm.newPage();
  for (const u of ['/', '/activiteiten/lasergame/']) await wp.goto(base + u, { waitUntil: 'networkidle', timeout: 120000 });
  await warm.close();

  const t0 = Date.now();
  const ctx = await browser.newContext({ viewport, isMobile, hasTouch: isMobile, deviceScaleFactor: 1, recordVideo: { dir: tmp, size: viewport } });
  await ctx.addCookies([{ name: 'playground_auto_login_already_happened', value: '1', url: base }]);
  const page = await ctx.newPage();
  await page.goto(base + '/', { waitUntil: 'networkidle', timeout: 120000 });
  await page.waitForTimeout(700);
  const start = (Date.now() - t0) / 1000; // vanaf hier is de pagina geladen

  await page.waitForTimeout(2600); // roterende kop laten zien
  // Rustig scrollen tot net na de arrangementen.
  const smooth = (distance, ms) => page.evaluate(async ([d, t]) => {
    const from = window.scrollY; const begin = performance.now();
    await new Promise((resolve) => {
      const step = (now) => {
        const p = Math.min(1, (now - begin) / t);
        const e = p < 0.5 ? 2 * p * p : 1 - Math.pow(-2 * p + 2, 2) / 2;
        window.scrollTo(0, from + d * e);
        p < 1 ? requestAnimationFrame(step) : resolve();
      };
      requestAnimationFrame(step);
    });
  }, [distance, ms]);
  const vh = viewport.height;
  for (let i = 0; i < (isMobile ? 7 : 5); i++) { await smooth(vh * 0.8, 1600); await page.waitForTimeout(900); }
  await smooth(-(await page.evaluate(() => window.scrollY)) + (await page.locator('.mc-activities').evaluate((el) => el.getBoundingClientRect().top + window.scrollY - 80)), 1500);
  await page.waitForTimeout(800);
  // Klik naar Lasergame.
  const card = page.locator('.mc-activities .mc-card__title a').first();
  await card.hover().catch(() => {});
  await page.waitForTimeout(700);
  await Promise.all([page.waitForURL(/lasergame/, { timeout: 120000 }), card.click()]);
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(1800);
  for (let i = 0; i < 3; i++) { await smooth(vh * 0.7, 1500); await page.waitForTimeout(800); }
  await page.waitForTimeout(600);
  const video = page.video();
  await ctx.close();
  const raw = await video.path();
  const mp4 = path.join(out, `${name}.mp4`);
  execFileSync(ffmpeg, ['-y', '-loglevel', 'error', '-ss', start.toFixed(2), '-i', raw, '-c:v', 'libx264', '-preset', 'slow', '-crf', '24', '-pix_fmt', 'yuv420p', '-movflags', '+faststart', '-an', mp4]);
  console.log(`${path.relative(root, mp4)}  ${(fs.statSync(mp4).size / 1024 / 1024).toFixed(1)} MB`);
}

await record('megacenter-desktop-1440', { width: 1440, height: 900 }, false);
await record('megacenter-mobiel-390', { width: 390, height: 844 }, true);
await browser.close();
fs.rmSync(tmp, { recursive: true, force: true });
