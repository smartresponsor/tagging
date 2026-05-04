# Tagging Wave 22 — Complete Post-Canon Runner

## Scope

Wave 22 adds one command that chains the post-canonicalization verification runners.

This wave does **not** rename files, move classes, delete files, or change namespaces.
`App\Tagging\...` remains canonical.

## Added runner

- `tools/test/tag-post-canon-all-wave22.php`

## Execution chain

The runner executes:

1. `tools/audit/tag-post-canon-verification-wave20.php`
2. `tools/test/tag-post-canon-tests-wave21.php`

## Safety

No files are deleted in this wave. The apply script overlays touched files only and backs up overwritten files.

## Verification

```bash
composer dump-autoload
php tools/test/tag-post-canon-all-wave22.php
vendor/bin/phpunit tests/TagPostCanonAllRunnerWave22Test.php
```
