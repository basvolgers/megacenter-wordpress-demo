// Demo-inhoud resetten via de knop in het beheer (Gereedschap → Demo-inhoud). Test meteen die knop.
// Gebruik: node tools/seed-reset.mjs [--base=http://127.0.0.1:9400] [--mode=reset|seed]
import { chromium } from 'playwright';
import { login } from './lib-login.mjs';
const arg = (n, d) => (process.argv.find((a) => a.startsWith(`--${n}=`)) || '').split('=')[1] || d;
const base = arg('base', 'http://127.0.0.1:9400');
const mode = arg('mode', 'reset');
const b = await chromium.launch();
const p = await (await b.newContext()).newPage();
p.on('dialog', (d) => d.accept());
await login(p, base);
await p.goto(base + '/wp-admin/tools.php?page=megacenter-demo', { waitUntil: 'domcontentloaded', timeout: 120000 });
const t0 = Date.now();
await Promise.all([p.waitForURL(/mc_done=/, { timeout: 300000 }), p.click(`button[value="${mode}"]`)]);
console.log((await p.locator('.notice-success').innerText()).trim(), `(${((Date.now() - t0) / 1000).toFixed(1)} s)`);
await b.close();
