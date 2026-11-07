// Maakt een contactsheet van alle beelden in een map (om beeldkeuze te beoordelen).
// Gebruik: node tools/contact-sheet.mjs <map> <uit.jpg> [kolommen]
import sharp from 'sharp';
import fs from 'node:fs'; import path from 'node:path';
const [dir, out, colsArg] = process.argv.slice(2);
const cols = Number(colsArg || 3), W = 480, H = 330;
const files = fs.readdirSync(dir).filter(f => /\.(jpe?g|png|webp)$/i.test(f));
const tiles = [];
for (const [i, f] of files.entries()) {
  try {
    const buf = await sharp(path.join(dir, f)).resize(W, H - 30, { fit: 'cover' }).toBuffer();
    const label = Buffer.from(`<svg width="${W}" height="30"><rect width="100%" height="100%" fill="#111"/><text x="6" y="20" font-size="15" fill="#fff" font-family="Arial">${i}: ${f.slice(0, 55).replace(/&/g,'&amp;')}</text></svg>`);
    tiles.push({ input: buf, left: (i % cols) * W, top: Math.floor(i / cols) * H });
    tiles.push({ input: label, left: (i % cols) * W, top: Math.floor(i / cols) * H + H - 30 });
  } catch (e) { console.log('skip', f, e.message); }
}
const rows = Math.ceil(files.length / cols);
await sharp({ create: { width: cols * W, height: rows * H, channels: 3, background: '#222' } }).composite(tiles).jpeg({ quality: 70 }).toFile(out);
console.log(files.map((f, i) => i + ': ' + f).join('\n'));
