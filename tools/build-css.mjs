// Minimaliseert de eigen CSS (thema + plugin) zonder externe afhankelijkheden.
// De .min.css-bestanden staan in de repo; WordPress laadt ze automatisch (behalve met SCRIPT_DEBUG).
// Gebruik: node tools/build-css.mjs   (na elke wijziging in theme.css of blocks.css)
import fs from 'node:fs';
import path from 'node:path';

const root = path.resolve(import.meta.dirname, '..');
const files = ['wp-content/themes/megacenter/assets/css/theme.css', 'wp-content/plugins/megacenter-core/assets/css/blocks.css'];

function minify(css) {
  // Strings en url(...) ongemoeid laten; daarbuiten commentaar en witruimte opschonen.
  const parts = css.split(/("(?:[^"\\]|\\.)*"|'(?:[^'\\]|\\.)*')/);
  return parts
    .map((p, i) => {
      if (i % 2) return p; // string
      return p
        .replace(/\/\*[\s\S]*?\*\//g, '')
        .replace(/\s+/g, ' ')
        .replace(/\s*([{};,])\s*/g, '$1')
        .replace(/:\s+/g, ':') // alleen ná de dubbele punt (spatie ervóór kan een selector-combinator zijn)
        .replace(/;}/g, '}');
    })
    .join('')
    .trim();
}

for (const rel of files) {
  const src = path.join(root, rel);
  const out = src.replace(/\.css$/, '.min.css');
  const css = fs.readFileSync(src, 'utf8');
  const min = minify(css) + '\n';
  fs.writeFileSync(out, min);
  console.log(`${rel}: ${(css.length / 1024).toFixed(1)} KB → ${(min.length / 1024).toFixed(1)} KB`);
}
