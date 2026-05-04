# Tagging Wave 20 — Post-Canon Verification Runner

## Scope

Wave 20 adds a single local verification runner for the post-canonicalization audit set.

This wave does **not** rename files, move classes, delete files, or change namespaces.
`App\Tagging\...` remains canonical.

## Added runner

- `tools/audit/tag-post-canon-verification-wave20.php`

The runner executes the focused audit scripts from Waves 2–19 in a stable order and reports first-class audit failures without requiring PHPUnit.

## Safety

No files are deleted in this wave. The apply script overlays touched files only and backs up overwritten files.

## Verification

```bash
composer dump-autoload
php tools/audit/tag-post-canon-verification-wave20.php
vendor/bin/phpunit tests/TagPostCanonVerificationWave20Test.php
```
