# Tagging/Tag protocol audit

The protocol audit codifies the current Tagging/Tag canon for the active slice.

Pass conditions:

- `App\Tagging\ => src/`
- no `src/Tag/...`
- no `src/Tagging/...`
- no `src/Infra/...`
- no `src/Port`, `src/Adaptor`, `src/Adapter`
- no early `src/[Layer]/Tag/...`
- no forbidden test trees such as `tests/Tag/...`, `tests/Tagging/...`, or nested `tests/.../Tag/.../*.*`

Command:

```bash
php tools/audit/tag-protocol-audit.php
```

## Root hygiene covered by protocol

The protocol audit also rejects non-canonical root residues that compete with the canonical `config/` and current-slice shape:

- `tag.yaml`
- `tag_assignment.yaml`
- `tag_quota.yaml`
- `tag_cons_patched/`
- `tag_fix/`
- `tmp/`

These are treated as protocol violations, not merely optional hygiene warnings.


## Local root cleanup

If a touched-files archive was unpacked directly into the repository root, remove delivery residues before running the protocol and hygiene tests:

```bash
php tools/cleanup/tag-root-cleanup.php
```
