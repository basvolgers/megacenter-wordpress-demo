// Genereert het typografische woordmerk "MEGACENTER" + sticker "Rijssen" als SVG-paden
// (geen afhankelijkheid van het lettertype in de browser).
// Gebruik: node tools/logo.mjs
import fs from 'node:fs';
import path from 'node:path';
import opentype from 'opentype.js';

const root = path.resolve(import.meta.dirname, '..');
const fontFile = path.join(root, 'node_modules/@fontsource/bricolage-grotesque/files/bricolage-grotesque-latin-800-normal.woff');
const buf = fs.readFileSync(fontFile);
const font = opentype.parse(buf.buffer.slice(buf.byteOffset, buf.byteOffset + buf.byteLength));

// Elke letter wordt op de oorsprong getekend en daarna verschoven (voorkomt afrondingsfouten/NaN).
function textPath(text, x, y, size, tracking = 0) {
  let cursor = x;
  const parts = [];
  for (const ch of text) {
    const glyph = font.charToGlyph(ch);
    const cmds = glyph.getPath(0, 0, size).commands;
    const f = (n, d) => (n + d).toFixed(2);
    let d = '';
    for (const c of cmds) {
      if (c.type === 'M' || c.type === 'L') d += `${c.type}${f(c.x, cursor)} ${f(c.y, y)}`;
      else if (c.type === 'Q') d += `Q${f(c.x1, cursor)} ${f(c.y1, y)} ${f(c.x, cursor)} ${f(c.y, y)}`;
      else if (c.type === 'C') d += `C${f(c.x1, cursor)} ${f(c.y1, y)} ${f(c.x2, cursor)} ${f(c.y2, y)} ${f(c.x, cursor)} ${f(c.y, y)}`;
      else if (c.type === 'Z') d += 'Z';
    }
    if (d.includes('NaN')) throw new Error('NaN in glyph ' + ch);
    parts.push(d);
    cursor += (glyph.advanceWidth / font.unitsPerEm) * size + tracking;
  }
  return { d: parts.join(''), width: cursor - x - tracking };
}

const size = 50;
const word = textPath('MEGACENTER', 0, 40, size, -0.6);
const tag = textPath('RIJSSEN', 0, 0, 12.5, 1.6);

const pillW = tag.width + 18;
const pillH = 22;
const pillX = word.width + 8;
const pillY = 3;
const width = Math.ceil(word.width + pillW + 14);
const height = 46;
const vbY = 0;

const svg = (standalone) => `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 ${vbY} ${width} ${height - vbY}" width="${width}" height="${height - vbY}" class="mc-logo__svg" role="img" aria-hidden="true" focusable="false">
<path class="mc-logo__word" fill="${standalone ? '#F7F4FF' : 'currentColor'}" d="${word.d}"/>
<g transform="translate(${pillX.toFixed(1)} ${pillY}) rotate(-6 ${(pillW / 2).toFixed(1)} ${pillH / 2})">
<rect class="mc-logo__pill" width="${pillW.toFixed(1)}" height="${pillH}" rx="${pillH / 2}" fill="#C8FF2E"/>
<path class="mc-logo__tag" fill="#0E0C20" transform="translate(9 ${(pillH / 2 + 4.6).toFixed(1)})" d="${tag.d}"/>
</g>
</svg>
`;

const out = path.join(root, 'wp-content/themes/megacenter/assets/img');
fs.mkdirSync(out, { recursive: true });
fs.writeFileSync(path.join(out, 'logo-wordmark.svg'), svg(false));
fs.writeFileSync(path.join(out, 'logo-wordmark-standalone.svg'), svg(true));
console.log('Logo geschreven:', width, 'x', height - vbY);
