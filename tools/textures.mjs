// Maakt de subtiele grain-textuur (tegel) en de grafische placeholder voor ontbrekende beelden.
import fs from 'node:fs';
import path from 'node:path';

const out = path.resolve(import.meta.dirname, '../wp-content/themes/megacenter/assets/img');
// Grain als SVG-ruisfilter: een paar honderd bytes in plaats van een zware afbeelding.
const grain = `<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><filter id="n"><feTurbulence type="fractalNoise" baseFrequency=".85" numOctaves="2" stitchTiles="stitch"/><feColorMatrix values="0 0 0 0 1  0 0 0 0 1  0 0 0 0 1  0 0 0 .55 0"/></filter><rect width="100%" height="100%" filter="url(#n)"/></svg>
`;
fs.writeFileSync(path.join(out, 'grain.svg'), grain);

const placeholder = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 960 640" width="960" height="640">
<defs><radialGradient id="g" cx="20%" cy="10%" r="90%"><stop offset="0" stop-color="#FF3EA5" stop-opacity=".55"/><stop offset=".6" stop-color="#181532" stop-opacity="0"/></radialGradient>
<radialGradient id="h" cx="90%" cy="90%" r="70%"><stop offset="0" stop-color="#3EE8FF" stop-opacity=".45"/><stop offset=".7" stop-color="#181532" stop-opacity="0"/></radialGradient></defs>
<rect width="960" height="640" fill="#181532"/><rect width="960" height="640" fill="url(#g)"/><rect width="960" height="640" fill="url(#h)"/>
<path d="M480 250l18 44 46 4-35 30 11 45-40-24-40 24 11-45-35-30 46-4z" fill="#C8FF2E"/>
<text x="480" y="420" text-anchor="middle" font-family="Arial, sans-serif" font-weight="700" font-size="26" letter-spacing="4" fill="#F7F4FF">BEELD VOLGT</text>
</svg>
`;
fs.writeFileSync(path.join(out, 'placeholder.svg'), placeholder);
console.log('ok', fs.statSync(path.join(out, 'grain.svg')).size, 'bytes grain');
