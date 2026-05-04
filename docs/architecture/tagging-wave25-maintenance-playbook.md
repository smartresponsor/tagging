# Tagging Wave 25 — Maintenance Playbook

## Scope

Wave 25 closes the current Tagging canonicalization sequence with an operator-facing maintenance playbook.

This wave does **not** rename files, move classes, delete files, or change namespaces.
`App\Tagging\...` remains canonical.

## Canon preserved

- Component namespace remains `App\Tagging\...`.
- Do not migrate Tagging to plain `App\...`.
- Future patches should stay touched-files only.
- No repository-wide cleanup.
- No cumulative snapshot application.
- No destructive full-repository overwrite.

## Safe local verification sequence

Run from the repository root:

```bash
composer dump-autoload
php tools/test/tag-post-canon-health-wave24.php
php tools/test/tag-post-canon-all-wave22.php
```

For PowerShell convenience:

```powershell
powershell -ExecutionPolicy Bypass -File tools/test/tag-post-canon-all-wave23.ps1
```

For Bash convenience:

```bash
bash tools/test/tag-post-canon-all-wave23.sh
```

## When continuing development

Use this order:

1. Inspect actual local failures from the Wave 20–24 runners.
2. Patch only the failing touched files.
3. Add or update a focused audit for the new invariant.
4. Add a PHPUnit wrapper only when it improves local repeatability.
5. Keep framework-conventional files generic when they are intentionally allowed:
   - `composer.json`
   - `config/routes.yaml`
   - `config/services.yaml`
   - `admin/index.html`
   - `public/tag/openapi/index.html`
   - `docs/README.md`

## Forbidden regressions

Do not reintroduce:

- root `docker-compose.yml`;
- root `host/Dockerfile`;
- root executable `Tagging`;
- generic `admin/app.js` or `admin/style.css`;
- generic SDK `Client.php` or `client.ts`;
- generic public HTTP examples such as `requests.http`, `http.http`, `seed.http`, `tour.http`;
- generic tooling entrypoints retired by Waves 8, 10, and 12.

## Verification

```bash
php tools/test/tag-maintenance-playbook-wave25.php
vendor/bin/phpunit tests/TagMaintenancePlaybookWave25Test.php
```
