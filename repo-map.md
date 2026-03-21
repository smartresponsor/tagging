REPO MAP
root: .
generated: 2026-03-17 01:35:00 -0500

TREE
.
.commanding/
.commanding/backup/
.commanding/database/
.commanding/dependencies/
.commanding/deploy/
.commanding/docker/
.commanding/git/
.commanding/misc/
.commanding/policy/
.commanding/ps1/
.commanding/sh/
.commanding/test/
admin/
config/
contracts/
contracts/http/
db/
db/postgres/
db/postgres/indexes/
db/postgres/migrations/
docs/
docs/acceptance/
docs/admin/
docs/api/
docs/architecture/
docs/architecture/decisions/
docs/bulk/
docs/data/
docs/db/
docs/demo/
docs/deploy/
docs/fixtures/
docs/http/
docs/ops/
docs/policy/
docs/public/
docs/rc5/
docs/release/
docs/security/
docs/seed/
docs/tag/
host-minimal/
src/
src/Application/
src/Application/Write/
src/Application/Write/Tag/
src/Cache/
src/Cache/Store/
src/Cache/Store/Tag/
src/Data/
src/Data/Model/
src/Data/Model/Tag/
src/Entity/
src/Entity/Core/
src/Entity/Core/Tag/
src/Event/
src/Event/Lifecycle/
src/Event/Lifecycle/Tag/
src/Http/
src/Http/Api/
src/Http/Api/Tag/
src/Http/Middleware/
src/Infrastructure/
src/Infrastructure/Outbox/
src/Infrastructure/Outbox/Tag/
src/Infrastructure/Persistence/
src/Infrastructure/Persistence/Tag/
src/Infrastructure/ReadModel/
src/Infrastructure/ReadModel/Tag/
src/Ops/
src/Ops/Metrics/
src/Ops/Security/
src/Service/
src/Service/Core/
src/Service/Core/Tag/
src/Service/Security/
tests/
tests/integration/
tools/
tools/audit/
tools/db/
tools/local/
tools/release/
tools/seed/
tools/slugify/
tools/smoke/
tools/synthetic/
tools/test-db/
.github/
.github/workflows/

## current canonical src layout
- `src/Application/Write/Tag/...`
- `src/Cache/Store/Tag/...`
- `src/Data/Model/Tag/...`
- `src/Entity/Core/Tag/...`
- `src/Event/Lifecycle/Tag/...`
- `src/Http/Api/Tag/...`
- `src/Infrastructure/Outbox/Tag/...`
- `src/Infrastructure/Persistence/Tag/...`
- `src/Infrastructure/ReadModel/Tag/...`
- `src/Service/Core/Tag/...`

## gates
- `composer run -n audit:canonical-structure`
- `composer run -n audit:canonical-stale`
- `composer run -n audit:bootstrap-runtime`
- `composer run -n audit:composer-integrity`
- `composer run -n audit:repo-hygiene`
- `composer run -n audit:snapshot-purity`
- `composer run -n audit:repo-map-truth`

Snapshot policy: cumulative snapshots must not contain root transport artifacts such as `MANIFEST.wave-*.json` or `ZZ_*`.
