#!/usr/bin/env bash
# Megacenter demo: start de website lokaal op http://127.0.0.1:9400 (macOS, Linux of Git Bash).
set -euo pipefail
cd "$(dirname "$0")"
command -v node >/dev/null || { echo "Node.js ontbreekt (installeer de LTS-versie via https://nodejs.org)."; exit 1; }
[ -x node_modules/.bin/wp-playground-cli ] || npm install --no-audit --no-fund
echo "Megacenter demo start op http://127.0.0.1:9400 (beheer: /wp-admin, automatisch ingelogd). Stoppen: Ctrl+C"
# MSYS_NO_PATHCONV voorkomt dat Git Bash het pad /wordpress/wp-content omzet naar een Windows-pad.
MSYS_NO_PATHCONV=1 exec node_modules/.bin/wp-playground-cli server --port=9400 --wp=latest --php=8.3 --login \
  --blueprint=./playground/blueprint-local.json \
  --mount-dir-before-install ./wp-content /wordpress/wp-content
