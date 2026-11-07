// Maakt installeerbare zips van thema en plugin (voor "Plugin/Thema uploaden" op elke WordPress-hosting).
// Gebruik: node tools/zips.mjs
import fs from 'node:fs';
import path from 'node:path';
import { ZipArchive } from 'archiver';

const root = path.resolve(import.meta.dirname, '..');
const out = path.join(root, 'deliverables/zips');
fs.mkdirSync(out, { recursive: true });

function zip(srcDir, name) {
  return new Promise((resolve, reject) => {
    const file = path.join(out, `${name}.zip`);
    const stream = fs.createWriteStream(file);
    const archive = new ZipArchive({ zlib: { level: 9 } });
    stream.on('close', () => resolve({ file, bytes: archive.pointer() }));
    archive.on('error', reject);
    archive.pipe(stream);
    // Mapnaam in de zip = slug, zodat WordPress hem correct installeert.
    archive.glob('**/*', { cwd: srcDir, dot: false, ignore: ['**/.DS_Store', '**/Thumbs.db'] }, { prefix: path.basename(srcDir) });
    archive.finalize();
  });
}

for (const [dir, name] of [
  ['wp-content/themes/megacenter', 'megacenter-theme'],
  ['wp-content/plugins/megacenter-core', 'megacenter-core'],
]) {
  const { file, bytes } = await zip(path.join(root, dir), name);
  console.log(`${path.relative(root, file)}  ${(bytes / 1024).toFixed(0)} KB`);
}
