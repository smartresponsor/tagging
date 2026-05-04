# Tagging Wave 28 — CI Bridge Command Wrappers

Wave 28 adds shell wrappers for the CI bridge runner introduced in Wave 27.

This wave does not rename files, move classes, delete files, or change namespaces.
`App\Tagging\...` remains canonical.

## Added wrappers

- `tools/test/tag-ci-bridge-wave28.ps1`
- `tools/test/tag-ci-bridge-wave28.sh`

Both wrappers call `tools/test/tag-ci-bridge-wave27.php`.

## Verification

```bash
composer dump-autoload
php tools/test/tag-ci-bridge-wave27.php
vendor/bin/phpunit tests/TagCiBridgeCommandWrapperWave28Test.php
```
