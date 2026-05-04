# Tagging Wave 23 — Post-Canon Command Wrappers

## Scope

Wave 23 adds shell wrappers for the complete post-canon runner introduced in Wave 22.

This wave does **not** rename files, move classes, delete files, or change namespaces.
`App\Tagging\...` remains canonical.

## Added wrappers

- `tools/test/tag-post-canon-all-wave23.ps1`
- `tools/test/tag-post-canon-all-wave23.sh`

Both wrappers call:

- `tools/test/tag-post-canon-all-wave22.php`

## Safety

No files are deleted in this wave. The apply script overlays touched files only and backs up overwritten files.

## Verification

```bash
composer dump-autoload
php tools/test/tag-post-canon-all-wave22.php
vendor/bin/phpunit tests/TagPostCanonCommandWrapperWave23Test.php
```

PowerShell convenience command:

```powershell
powershell -ExecutionPolicy Bypass -File tools/test/tag-post-canon-all-wave23.ps1
```

Bash convenience command:

```bash
bash tools/test/tag-post-canon-all-wave23.sh
```
