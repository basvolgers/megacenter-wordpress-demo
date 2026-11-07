// Zet mobiele full-page screenshots naast elkaar in kolommen (overzichtsbord voor visuele QA).
// Gebruik: node tools/mobile-board.mjs <uit.jpg> <png1> <png2> ... [--h=2600]
import sharp from 'sharp';
const args = process.argv.slice(2);
const out = args.shift();
const H = Number((args.find((a) => a.startsWith('--h=')) || '--h=2600').slice(4));
const files = args.filter((a) => !a.startsWith('--'));
const cols = [];
for (const f of files) {
  const m = await sharp(f).metadata();
  for (let top = 0; top < m.height; top += H) {
    const h = Math.min(H, m.height - top);
    cols.push({ buf: await sharp(f).extract({ left: 0, top, width: m.width, height: h }).toBuffer(), w: m.width, h });
  }
}
const W = cols.reduce((a, c) => a + c.w + 16, 0);
let x = 0;
const comp = cols.map((c) => { const r = { input: c.buf, left: x, top: 0 }; x += c.w + 16; return r; });
const board = await sharp({ create: { width: W, height: H, channels: 3, background: '#555' } }).composite(comp).png().toBuffer();
await sharp(board).resize({ height: 2000 }).jpeg({ quality: 70 }).toFile(out);
console.log(out, cols.length, 'kolommen');
