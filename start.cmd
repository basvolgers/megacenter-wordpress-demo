@echo off
REM Megacenter demo: dubbelklik om de website lokaal te starten. Sluit dit venster om te stoppen.
cd /d "%~dp0"
title Megacenter demo - http://127.0.0.1:9400
where node >nul 2>nul
if errorlevel 1 (
  echo Node.js ontbreekt. Installeer het eerst met:  winget install OpenJS.NodeJS.LTS
  pause
  exit /b 1
)
if not exist "node_modules\.bin\wp-playground-cli.cmd" (
  echo Eerste keer: benodigdheden installeren, dit duurt een paar minuten...
  call npm install --no-audit --no-fund
)
echo.
echo  Megacenter demo start... na ongeveer 20 seconden opent je browser vanzelf.
echo  Adres:   http://127.0.0.1:9400
echo  Beheer:  http://127.0.0.1:9400/wp-admin   (je bent automatisch ingelogd)
echo  Stoppen: sluit dit venster of druk op Ctrl+C
echo.
start "" /b cmd /c "timeout /t 22 /nobreak >nul & start http://127.0.0.1:9400"
call node_modules\.bin\wp-playground-cli.cmd server --port=9400 --wp=latest --php=8.3 --login --blueprint=./playground/blueprint-local.json --mount-dir-before-install ./wp-content /wordpress/wp-content
pause
