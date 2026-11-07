// Zoekt kandidaat-beelden via de Openverse API (alleen commercieel bruikbare licenties)
// en maakt per zoekterm een contactsheet om de beeldkeuze te beoordelen.
// Gebruik: node tools/image-discovery.mjs <uitmap> "zoekterm|bronnen" ...
//   bronnen bijv. stocksnap,wordpress,nappy (standaard) of flickr
import fs from 'node:fs';
import path from 'node:path';
import sharp from 'sharp';

const [outDir, ...queries] = process.argv.slice(2);
fs.mkdirSync(outDir, { recursive: true });
const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

for (const entry of queries) {
  const [q, sources = 'stocksnap,wordpress,nappy'] = entry.split('|');
  const slug = q.replace(/\W+/g, '_') + '__' + sources.replace(/,/g, '-');
  const api = `https://api.openverse.org/v1/images/?q=${encodeURIComponent(q)}&license_type=commercial&source=${sources}&page_size=20&mature=false`;
  const res = await fetch(api, { headers: { 'User-Agent': 'megacenter-demo-image-discovery' } });
  if (!res.ok) { console.log('FAIL', q, res.status); continue; }
  const data = await res.json();
  fs.writeFileSync(path.join(outDir, slug + '.json'), JSON.stringify(data.results, null, 1));
  const W = 360, H = 260, cols = 5, tiles = [];
  let i = 0;
  for (const r of data.results) {
    let url = r.url;
    if (r.source === 'wordpress') url = url.replace(/-\d+x\d+(\.\w+)$/, '-1024x' + '$1').replace(/-1024x(\.\w+)$/, '$1');
    try {
      const img = await fetch(r.source === 'flickr' ? url.replace(/_b\.jpg$/, '_n.jpg') : url);
      const buf = Buffer.from(await img.arrayBuffer());
      const t = await sharp(buf).resize(W, H - 24, { fit: 'cover' }).toBuffer();
      const label = Buffer.from(`<svg width="${W}" height="24"><rect width="100%" height="100%" fill="#111"/><text x="4" y="17" font-size="13" fill="#fff" font-family="Arial">${i} ${r.source} ${r.license} ${r.width}x${r.height}</text></svg>`);
      tiles.push({ input: t, left: (i % cols) * W, top: Math.floor(i / cols) * H }, { input: label, left: (i % cols) * W, top: Math.floor(i / cols) * H + H - 24 });
    } catch (e) { /* overslaan */ }
    i++;
  }
  if (tiles.length) {
    await sharp({ create: { width: cols * W, height: Math.ceil(i / cols) * H, channels: 3, background: '#222' } })
      .composite(tiles).jpeg({ quality: 62 }).toFile(path.join(outDir, slug + '.jpg'));
  }
  console.log('ok', q, sources, data.result_count);
  await sleep(1500);
}
