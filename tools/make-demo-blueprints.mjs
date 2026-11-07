// Schrijft playground/blueprint-demo.json en blueprint-demo-cms.json voor een publieke GitHub-repo.
// Gebruik: node tools/make-demo-blueprints.mjs <github-gebruiker>/<repo> [branch=main]
import fs from 'node:fs';
import path from 'node:path';

const [repo = 'GITHUB-GEBRUIKER/megacenter-wordpress-demo', branch = 'main'] = process.argv.slice(2);
const url = `https://github.com/${repo}`;
const dir = path.resolve(import.meta.dirname, '../playground');

const blueprint = (landingPage, title) => ({
  $schema: 'https://playground.wordpress.net/blueprint-schema.json',
  meta: {
    title,
    description: 'Pitch-demo: blokthema "megacenter" + plugin "megacenter-core" met demo-inhoud. Alle inhoud is voorbeeldinhoud.',
    author: 'Bas',
    categories: ['Demo'],
  },
  landingPage,
  preferredVersions: { php: '8.3', wp: 'latest' },
  features: { networking: true },
  steps: [
    { step: 'setSiteLanguage', language: 'nl_NL' },
    { step: 'setSiteOptions', options: { blogname: 'Megacenter Rijssen', blogdescription: 'FUN begint hier.', timezone_string: 'Europe/Amsterdam', permalink_structure: '/%postname%/', blog_public: '0' } },
    { step: 'installPlugin', pluginData: { resource: 'git:directory', url, ref: branch, refType: 'branch', path: 'wp-content/plugins/megacenter-core' }, options: { activate: true } },
    { step: 'installTheme', themeData: { resource: 'git:directory', url, ref: branch, refType: 'branch', path: 'wp-content/themes/megacenter' }, options: { activate: true } },
    { step: 'runPHP', code: "<?php require_once '/wordpress/wp-load.php'; if ( function_exists( 'megacenter_demo_seed' ) ) { megacenter_demo_seed(); } flush_rewrite_rules( false );" },
    { step: 'login', username: 'admin' },
  ],
});

fs.writeFileSync(path.join(dir, 'blueprint-demo.json'), JSON.stringify(blueprint('/', 'Megacenter Rijssen – demo'), null, '\t') + '\n');
fs.writeFileSync(path.join(dir, 'blueprint-demo-cms.json'), JSON.stringify(blueprint('/wp-admin/edit.php?post_type=activiteit', 'Megacenter Rijssen – demo (beheer)'), null, '\t') + '\n');
const raw = (f) => `https://raw.githubusercontent.com/${repo}/${branch}/playground/${f}`;
// mode=seamless: alleen de site, zonder de werkbalk van Playground.
console.log('Site: https://playground.wordpress.net/?mode=seamless&blueprint-url=' + raw('blueprint-demo.json'));
console.log('CMS:  https://playground.wordpress.net/?mode=seamless&blueprint-url=' + raw('blueprint-demo-cms.json'));
