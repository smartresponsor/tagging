# Repository hygiene gates

Wave 07 removes transport-only wave metadata from the repository root and makes that rule testable.
Wave 11 reconciles repo-map truth policy with the actual repository content: documentation trees such as `docs/public/`, `docs/release/`, and `docs/rc5/` are valid documentation roots and must not be treated as stale transport residue.

## Scope

The repository should not persist delivery-only files such as per-wave manifests or `ZZ_*` transport notes.
Those files may exist in exported patch archives, but they must not remain inside the cumulative repository snapshot.

## Guardrails

- `composer run -n audit:repo-hygiene`
- `Tests\RepoHygieneAuditTest`
- `composer run -n audit:repo-map-truth`
- `Tests\RepoMapTruthAuditTest`

## Failing conditions

The hygiene gate fails when root-level transport artifacts are present, including historical `MANIFEST.wave-*.json` files and `ZZ_*` delivery notes.

The repo-map truth gate fails when `repo-map.md` includes stale runtime/application paths that are no longer part of the repository, such as forbidden `src/Domain/` or `src/Infra/` trees, or when canonical `src/.../.../Tag/...` branches are missing.

## Allowed documentation roots

The following documentation trees are allowed and should remain visible in `repo-map.md` when they physically exist in the repository:

- `docs/public/`
- `docs/release/`
- `docs/rc5/`
- `docs/ops/`
- `docs/fixtures/`

These are documentation areas, not transport artifacts.


## Disallowed transient workspace roots

Cumulative snapshots must not contain temporary overlay or handoff workspaces such as `tag_cons_patched/`, `tag_fix/`, or `tmp/`.
These are transport or operator leftovers, not canonical repository roots.
