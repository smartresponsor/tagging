# Tagging Wave 21 — Post-Canon PHPUnit Runner

## Scope

Wave 21 adds a PHPUnit-oriented runner for post-canonicalization gate tests.

This wave does **not** rename files, move classes, delete files, or change namespaces.
`App\Tagging\...` remains canonical.

## Added runner

- `tools/test/tag-post-canon-tests-wave21.php`

Wave 20 runs audit scripts directly. Wave 21 runs the PHPUnit wrappers for the canonicalization audit gates.

## Safety

No files are deleted in this wave. The apply script overlays touched files only and backs up overwritten files.

## Verification

```bash
composer dump-autoload
php tools/test/tag-post-canon-tests-wave21.php
vendor/bin/phpunit tests/TagPostCanonTestRunnerWave21Test.php
```
