# Tagging Wave 11 — Audit Stabilization

## Scope

Wave 11 stabilizes audit gates after the prior class-form, duplicate-residue, and tooling-surface cleanup waves.

This wave does **not** migrate the component namespace. `App\Tagging\...` remains canonical.

## Fixes

- `tag-class-form-audit.php` now accepts both:
  - `namespace App\Tagging;` for the bundle root class;
  - `namespace App\Tagging\...;` for nested component classes.
- `tag-legacy-facade-audit.php` no longer treats later audit files as live source references when those files merely list retired paths as forbidden data.
- tooling audits no longer expect accidental `tag-tag-smoke` names after Wave 10; the canonical smoke entrypoint is `tools/smoke/tag-smoke.sh`.

## Safety

No repository-wide cleanup is performed. No cumulative snapshot is produced. The apply script overlays only touched files.

## Verification

```bash
composer dump-autoload
php tools/audit/tag-audit-stabilization-wave11-audit.php
vendor/bin/phpunit tests/TagAuditStabilizationWave11AuditTest.php
```
