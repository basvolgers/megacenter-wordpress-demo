// Beeldpijplijn voor de demo.
// 1. Downloadt elke bron uit tools/images.manifest.json (cache in tools/.cache, niet in git).
// 2. Snijdt bij op de gevraagde verhouding, schaalt, strip metadata en zet om naar WebP (q 78).
// 3. Maakt extra breedtes (480/800) voor srcset.
// 4. Schrijft demo-content/images/manifest.json (voor de seeder) en CREDITS.md.
//
// Eigen beeld van de opdrachtgever staat apart in tools/images.private.json (niet in git). Bestaat dat
// bestand, dan komen die beelden in demo-content/images-private/ (ook niet in git) en vervangt de
// seeder daarmee de stockbeelden met dezelfde sleutel. Zo blijft de publieke repo vrij van klantbeeld.
// Gebruik: node tools/images.mjs [--public]   (--public slaat het eigen beeld van de opdrachtgever over)
import crypto from 'node:crypto';
import fs from 'node:fs';
import path from 'node:path';
import sharp from 'sharp';

const root = path.resolve(import.meta.dirname, '..');
const read = (file) => JSON.parse(fs.readFileSync(file, 'utf8'));
const manifest = read(path.join(root, 'tools/images.manifest.json'));
const privateFile = path.join(root, 'tools/images.private.json');
const imagesDir = path.join(root, 'wp-content/plugins/megacenter-core/demo-content/images');
const privateDir = path.join(root, 'wp-content/plugins/megacenter-core/demo-content/images-private');
const cacheDir = path.join(root, 'tools/.cache');
const publicOnly = process.argv.includes('--public');
fs.mkdirSync(cacheDir, { recursive: true });

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
const ratio = (a) => { const [w, h] = a.split(':').map(Number); return h / w; };

async function build(images, outDir) {
  fs.mkdirSync(outDir, { recursive: true });
  const out = [];
  let total = 0;
  for (const img of images) {
    // Cachenaam bevat een hash van de URL: een andere bron voor dezelfde sleutel wordt opnieuw opgehaald.
    const hash = crypto.createHash('sha1').update(img.url).digest('hex').slice(0, 8);
    const cacheFile = path.join(cacheDir, `${img.key}-${hash}${path.extname(new URL(img.url).pathname)}`);
    if (!fs.existsSync(cacheFile)) {
      const res = await fetch(img.url, { headers: { 'User-Agent': 'Mozilla/5.0 (megacenter-demo image pipeline)' } });
      if (!res.ok) { console.error('FOUT bij downloaden', img.key, res.status); continue; }
      fs.writeFileSync(cacheFile, Buffer.from(await res.arrayBuffer()));
      await sleep(600);
    }
    const meta = await sharp(cacheFile).metadata();
    const width = Math.min(img.width, meta.width);
    const height = Math.round(width * ratio(img.aspect));
    const sizes = [];
    for (const w of [480, 800, width]) {
      if (w > width || sizes.some((s) => s.width === w)) continue;
      const h = Math.round(w * ratio(img.aspect));
      const file = w === width ? `${img.key}.webp` : `${img.key}-${w}.webp`;
      const info = await sharp(cacheFile)
        .rotate()
        .resize(w, h, { fit: 'cover', position: img.position || 'centre' })
        .webp({ quality: 78, effort: 6 })
        .toFile(path.join(outDir, file));
      total += info.size;
      sizes.push({ width: w, height: h, file, filesize: info.size });
    }
    out.push({
      key: img.key, file: `${img.key}.webp`, width, height, alt: img.alt,
      credit: `${img.creator} / ${img.source} (${img.license})`, client: !!img.client,
      sizes: sizes.filter((s) => s.width !== width),
    });
    console.log('ok', img.key.padEnd(16), `${width}x${height}`, Math.round(sizes.at(-1).filesize / 1024) + ' KB');
  }
  return { out, total };
}

const credits = (images, intro) => `# Beeldbronnen en licenties

${intro}

| Sleutel | Herkomst | Maker | Bron | Licentie | Link |
|---|---|---|---|---|---|
${images.map((i) => `| \`${i.key}\` | ${i.client ? '**Opdrachtgever**' : 'Stock'} | ${i.creator} | ${i.source} | ${i.license} | ${i.landing} |`).join('\n')}
`;

// 1. Publieke beelden (stock, in git).
const pub = await build(manifest.images, imagesDir);
const manifestFile = path.join(imagesDir, 'manifest.json');
// Gegenereerde beelden (zoals og-default van tools/brand-assets.mjs) behouden.
if (fs.existsSync(manifestFile)) {
  for (const m of read(manifestFile)) if (m.key.startsWith('og-')) pub.out.push(m);
}
fs.writeFileSync(manifestFile, JSON.stringify(pub.out, null, 2) + '\n');
console.log(`Publiek: ${(pub.total / 1024 / 1024).toFixed(2)} MB in ${pub.out.length} beelden`);

fs.writeFileSync(path.join(root, 'CREDITS.md'), credits(manifest.images, `Alle demo-beelden staan in \`wp-content/plugins/megacenter-core/demo-content/images/\` en zijn met
\`node tools/images.mjs\` bijgesneden en omgezet naar WebP. Bron, maker en licentie per beeld:`) + `
- **CC0** = vrij te gebruiken, ook commercieel, zonder naamsvermelding (vermelding hier is uit netheid).
- Lettertypes: Bricolage Grotesque en Manrope, beide SIL Open Font License 1.1 (zie \`wp-content/themes/megacenter/assets/fonts/\`).
- Iconen, logo-woordmerk, grain-textuur en grafische placeholders zijn zelf gemaakt voor deze demo.
- Eigen beeld van de opdrachtgever staat niet in deze repository. Lokaal kan het via \`tools/images.private.json\`
  worden toegevoegd (zie de uitleg bovenin \`tools/images.mjs\`).
`);

// 2. Eigen beeld van de opdrachtgever (alleen lokaal, niet in git).
if (!publicOnly && fs.existsSync(privateFile)) {
  const priv = read(privateFile).images.map((i) => ({ ...i, client: true }));
  const res = await build(priv, privateDir);
  fs.writeFileSync(path.join(privateDir, 'manifest.json'), JSON.stringify(res.out, null, 2) + '\n');
  fs.writeFileSync(path.join(privateDir, 'CREDITS.md'), credits(priv, 'Eigen beeld van de opdrachtgever, alleen voor de pitch. Niet publiceren.'));
  console.log(`Opdrachtgever (lokaal): ${res.out.length} beelden in ${path.relative(root, privateDir)}`);
} else if (publicOnly && fs.existsSync(privateDir)) {
  fs.rmSync(privateDir, { recursive: true, force: true });
  console.log('Map met beeld van de opdrachtgever verwijderd (--public).');
}
