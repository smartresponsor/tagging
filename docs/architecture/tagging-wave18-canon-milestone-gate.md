# Tagging Wave 18 — Canon Milestone Gate

## Scope

Wave 18 closes the current canonicalization milestone with an aggregate audit gate.

It does not rename files, move classes, or change namespaces. `App\Tagging\...` remains canonical.

## What the gate checks

- `composer.json` keeps `App\Tagging\` and does not collapse the component to plain `App\`.
- Key focused audits from Waves 2–17 are present.
- Canonical artifacts from the cleanup sequence exist.
- Retired generic/root/tooling/admin/public/SDK/delivery artifacts do not return.
- PHP files under `src/` remain under `App\Tagging` or `App\Tagging\...`.

## Relationship to focused audits

This audit is intentionally an aggregate milestone gate. It does not replace the focused audits. It confirms that the wave sequence remains structurally visible and guards against the most important regressions.

## Safety

No files are deleted in this wave. The apply script overlays touched files only and backs up any overwritten files.

## Verification

```bash
composer dump-autoload
php tools/audit/tag-canon-milestone-wave18-audit.php
vendor/bin/phpunit tests/TagCanonMilestoneWave18AuditTest.php
```
