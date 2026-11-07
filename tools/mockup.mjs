// Presentatiebeeld: de homepage in een desktop- en een mobiel frame naast elkaar.
// Bouwt een HTML-pagina met twee verse screenshots en maakt daar een PNG van (deliverables/mockup.png).
// Gebruik: node tools/mockup.mjs [--base=http://127.0.0.1:9400]
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const root = path.resolve(import.meta.dirname, '..');
const base = (process.argv.find((a) => a.startsWith('--base=')) || '--base=http://127.0.0.1:9400').slice(7);
const browser = await chromium.launch({ args: ['--hide-scrollbars'] });

async function shot(width, height, scale, mobile) {
  const ctx = await browser.newContext({ viewport: { width, height }, deviceScaleFactor: scale, isMobile: mobile, hasTouch: mobile, reducedMotion: 'reduce' });
  await ctx.addCookies([{ name: 'playground_auto_login_already_happened', value: '1', url: base }]);
  const page = await ctx.newPage();
  await page.goto(base + '/', { waitUntil: 'networkidle', timeout: 120000 });
  await page.waitForTimeout(900);
  const buf = await page.screenshot();
  await ctx.close();
  return 'data:image/png;base64,' + buf.toString('base64');
}

const desktop = await shot(1440, 900, 1, false);
const mobile = await shot(390, 844, 2, true);
const logo = fs.readFileSync(path.join(root, 'wp-content/themes/megacenter/assets/img/logo-wordmark-standalone.svg'), 'utf8');
const font = 'data:font/woff2;base64,' + fs.readFileSync(path.join(root, 'wp-content/themes/megacenter/assets/fonts/bricolage-grotesque-latin-wdth.woff2')).toString('base64');
const body = 'data:font/woff2;base64,' + fs.readFileSync(path.join(root, 'wp-content/themes/megacenter/assets/fonts/manrope-latin-wght.woff2')).toString('base64');

const html = `<!doctype html><html lang="nl"><head><meta charset="utf-8"><style>
@font-face{font-family:B;src:url(${font}) format('woff2');font-weight:200 800;font-stretch:75% 100%}
@font-face{font-family:M;src:url(${body}) format('woff2');font-weight:200 800}
*{box-sizing:border-box;margin:0}
body{width:2400px;height:1400px;overflow:hidden;font-family:M,sans-serif;color:#F7F4FF;
background:radial-gradient(60% 70% at 15% 10%,rgba(255,62,165,.35),transparent 70%),radial-gradient(50% 60% at 90% 90%,rgba(62,232,255,.25),transparent 70%),#0E0C20;position:relative}
.grid{position:absolute;inset:auto -10% 0;height:45%;background:repeating-linear-gradient(90deg,rgba(255,62,165,.35) 0 1px,transparent 1px 90px),repeating-linear-gradient(0deg,rgba(62,232,255,.25) 0 1px,transparent 1px 60px);transform:perspective(600px) rotateX(64deg);transform-origin:bottom;-webkit-mask:linear-gradient(to top,#000,transparent 85%);opacity:.6}
.head{position:absolute;left:120px;top:96px;display:flex;align-items:center;gap:40px}
.head svg{width:420px;height:auto}
.tag{font-family:B;font-weight:800;font-stretch:75%;font-size:44px;text-transform:uppercase;color:#C8FF2E;letter-spacing:.01em}
.sub{position:absolute;left:120px;top:190px;font-size:26px;color:#B9B4DA;font-weight:600}
.desk{position:absolute;left:120px;top:262px;width:1600px;border-radius:22px;background:#1F1C3D;padding:0 0 0;box-shadow:0 60px 140px -40px rgba(0,0,0,.8),0 0 0 1px rgba(255,255,255,.08),0 40px 120px -50px rgba(255,62,165,.6);overflow:hidden}
.bar{height:52px;display:flex;align-items:center;gap:12px;padding:0 22px;background:#181532}
.bar i{width:15px;height:15px;border-radius:50%;background:#FF5F57}.bar i:nth-child(2){background:#FEBC2E}.bar i:nth-child(3){background:#28C840}
.url{margin-left:24px;flex:1;max-width:620px;height:32px;border-radius:999px;background:#0E0C20;color:#B9B4DA;font-size:17px;display:flex;align-items:center;padding:0 18px}
.desk img{display:block;width:100%}
.phone{position:absolute;right:150px;top:250px;width:440px;height:920px;border-radius:64px;background:#05040d;padding:16px;box-shadow:0 60px 140px -40px rgba(0,0,0,.9),0 0 0 2px rgba(255,255,255,.12),0 40px 120px -40px rgba(62,232,255,.6);transform:rotate(3deg)}
.phone img{width:100%;height:100%;object-fit:cover;object-position:top;border-radius:50px;display:block}
.notch{position:absolute;top:30px;left:50%;transform:translateX(-50%);width:120px;height:32px;border-radius:20px;background:#05040d}
.sticker{position:absolute;right:120px;top:120px;width:190px;height:190px;border-radius:50%;background:#C8FF2E;color:#0E0C20;display:grid;place-content:center;text-align:center;font-family:B;font-weight:800;font-stretch:78%;text-transform:uppercase;font-size:30px;line-height:1;transform:rotate(10deg);box-shadow:0 20px 50px -15px rgba(0,0,0,.6)}
.sticker small{display:block;font-size:17px;margin-top:6px;letter-spacing:.06em}
</style></head><body><div class="grid"></div>
<div class="head">${logo}<span class="tag">FUN begint hier.</span></div>
<p class="sub">Pitch-demo · WordPress blokthema + eigen plugin · responsive · AVG-proof zonder cookiebanner</p>
<div class="desk"><div class="bar"><i></i><i></i><i></i><span class="url">jullie-domein.nl</span></div><img src="${desktop}" alt=""></div>
<div class="phone"><img src="${mobile}" alt=""><span class="notch"></span></div>
<div class="sticker">Demo<small>klikbaar</small></div>
</body></html>`;

const ctx = await browser.newContext({ viewport: { width: 2400, height: 1400 } });
const page = await ctx.newPage();
await page.setContent(html, { waitUntil: 'load' });
await page.waitForTimeout(500);
await page.screenshot({ path: path.join(root, 'deliverables/mockup.png') });
await browser.close();
console.log('deliverables/mockup.png');
