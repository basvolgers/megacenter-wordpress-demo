// Knipt een lange full-page screenshot in behapbare stukken (voor visuele beoordeling).
// Gebruik: node tools/preview-slices.mjs <bestand.png> <uitmap> [hoogte=1800] [breedte=1100]
import path from 'node:path';
import fs from 'node:fs';
import sharp from 'sharp';

const [file, outDir, h = '1800', w = '1100'] = process.argv.slice(2);
fs.mkdirSync(outDir, { recursive: true });
const meta = await sharp(file).metadata();
const sliceH = Number(h);
const base = path.basename(file, path.extname(file));
let i = 0;
for (let top = 0; top < meta.height; top += sliceH) {
  const height = Math.min(sliceH, meta.height - top);
  const out = path.join(outDir, `${base}-${String(++i).padStart(2, '0')}.jpg`);
  await sharp(file).extract({ left: 0, top, width: meta.width, height }).resize({ width: Math.min(Number(w), meta.width) }).jpeg({ quality: 72 }).toFile(out);
  console.log(out);
}
