# Tagging Wave 29 — Verification Profile

## Scope

Wave 29 adds a machine-readable verification profile for the post-canon Tagging state.

This wave does **not** rename files, move classes, delete files, or change namespaces.
`App\Tagging\...` remains canonical.

## Added artifact

- `delivery/canon/tagging-verification-profile.json`

The profile points CI and local tooling to the current verification entrypoints:

- `tools/test/tag-ci-bridge-wave27.php`
- `tools/test/tag-ci-bridge-wave28.ps1`
- `tools/test/tag-ci-bridge-wave28.sh`
- `tools/test/tag-canon-status-wave26.php`
- `tools/test/tag-post-canon-health-wave24.php`
- `tools/test/tag-post-canon-all-wave22.php`

## Verification

```bash
composer dump-autoload
php tools/test/tag-verification-profile-wave29.php
vendor/bin/phpunit tests/TagVerificationProfileWave29Test.php
```
