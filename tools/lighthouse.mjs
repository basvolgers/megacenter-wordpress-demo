// Lighthouse (mobiel) op home en een activiteitpagina, met de Chromium van Playwright.
// De demo staat bewust op "niet indexeren" (blog_public = 0); de audit 'is-crawlable' wordt daarom
// overgeslagen zodat de SEO-score de techniek meet en niet die bewuste keuze.
// Let op: Playground draait PHP in WebAssembly en heeft een trage serverrespons (TTFB); beoordeel
// performance vooral op LCP-oorzaken, CLS en bestandsgroottes.
// Gebruik: node tools/lighthouse.mjs [--base=http://127.0.0.1:9400]
import fs from 'node:fs';
import path from 'node:path';
import os from 'node:os';
import lighthouse from 'lighthouse';
import * as chromeLauncher from 'chrome-launcher';
import { chromium } from 'playwright';

const arg = (n, d) => (process.argv.find((a) => a.startsWith(`--${n}=`)) || '').split('=')[1] || d;
const base = arg('base', 'http://127.0.0.1:9400');
const out = path.resolve('deliverables/lighthouse');
fs.mkdirSync(out, { recursive: true });

// Chrome-keuze: CHROME_PATH, anders de geïnstalleerde Chrome, anders de Chromium van Playwright.
// (Er wordt altijd een tijdelijk, apart profiel gebruikt; je eigen Chrome-profiel blijft onaangeroerd.)
const candidates = [process.env.CHROME_PATH, 'C:/Program Files/Google/Chrome/Application/chrome.exe', '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome', '/usr/bin/google-chrome', chromium.executablePath()];
const chromePath = candidates.find((p) => p && fs.existsSync(p));
const chrome = await chromeLauncher.launch({
  chromePath,
  chromeFlags: ['--headless=new', '--no-sandbox', `--user-data-dir=${fs.mkdtempSync(path.join(os.tmpdir(), 'lh-'))}`],
});
const summary = [];
for (const [name, url] of [['home', '/'], ['lasergame', '/activiteiten/lasergame/']]) {
  const result = await lighthouse(base + url, {
    port: chrome.port,
    output: ['html', 'json'],
    logLevel: 'error',
    locale: 'nl',
    extraHeaders: { Cookie: 'playground_auto_login_already_happened=1' },
  }, {
    extends: 'lighthouse:default',
    settings: { skipAudits: ['is-crawlable'], formFactor: 'mobile' },
  });
  const [html, json] = result.report;
  fs.writeFileSync(path.join(out, `${name}.html`), html);
  const lhr = JSON.parse(json);
  fs.writeFileSync(path.join(out, `${name}.json`), json);
  const s = Object.fromEntries(Object.entries(lhr.categories).map(([k, v]) => [k, Math.round(v.score * 100)]));
  const a = lhr.audits;
  const row = {
    pagina: name, ...s,
    LCP: a['largest-contentful-paint'].displayValue, CLS: a['cumulative-layout-shift'].displayValue,
    TBT: a['total-blocking-time'].displayValue, TTFB: a['server-response-time']?.displayValue,
    'LCP-element': a['largest-contentful-paint-element']?.details?.items?.[0]?.items?.[0]?.node?.snippet?.slice(0, 90) || '',
  };
  summary.push(row);
  console.log(row);
  const failed = Object.values(a).filter((x) => x.score !== null && x.score < 0.9 && x.scoreDisplayMode !== 'informative' && x.scoreDisplayMode !== 'notApplicable').map((x) => `${x.id} (${x.displayValue || x.score})`);
  console.log('  aandachtspunten:', failed.slice(0, 14).join(', ') || 'geen');
}
fs.writeFileSync(path.join(out, 'samenvatting.json'), JSON.stringify(summary, null, 2));
await chrome.kill();
