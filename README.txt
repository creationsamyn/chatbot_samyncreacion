# Chatbot site (ES/FR) — one folder
This folder contains a ready-to-deploy bilingual chatbot demo using HTML, CSS, JavaScript and PHP.
Place the whole folder on a PHP-capable web server (Apache/Nginx + PHP 7.4+).

Files:
- index.html   — UI (chat, language switch, suggestions)
- style.css    — styles
- app.js       — client logic and fetch to api.php
- api.php      — backend (rules engine; optional AI stub commented)
- README.txt   — this file

How to run:
1) Upload the `chatbot_site` folder to your hosting.
2) Visit `index.html` in the browser (same folder as api.php).

Notes:
- The demo uses a simple rules engine. Expand keyword maps or plug an AI provider in `api.php` (see commented call_ai()).
- Same-origin: index.html and api.php must live together in the same folder for fetch to work without CORS changes.
- If you host index.html elsewhere, you must enable CORS on api.php.
