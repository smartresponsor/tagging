# Snapshot Purity

Cumulative snapshots must not retain transport-only artifacts at repository root.

Forbidden root artifacts:
- `MANIFEST.wave-*.json`
- `ZZ_*`

These files are acceptable inside delivery patch archives, but they must not remain in the repository snapshot after application.

Gates:
- `composer run -n audit:snapshot-purity`
- `vendor/bin/phpunit --filter SnapshotPurityAuditTest`


## Transient workspace roots

Snapshot purity also forbids temporary operator workspaces in repository root, including `tag_cons_patched/`, `tag_fix/`, and `tmp/`.
These are transport leftovers and must be caught by `audit:snapshot-purity`, not only by the broader hygiene gate.
