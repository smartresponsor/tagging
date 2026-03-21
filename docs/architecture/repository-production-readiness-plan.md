# Repository Production Readiness Analysis and Action Plan

## Scope and current baseline

- Branch reality: repository currently exposes only local branch `work`; there is no local `master` branch to check out.
- Platform baseline is a PHP 8.2 library with direct PDO usage and a minimal host router (`host-minimal/index.php`)
  wiring services/controllers manually.

## 1) Architecture analysis

### What is working

- There is a visible split of namespaces (`Domain`, `Service`, `Infra`, `Http`, `Data`) that indicates a layered intent.
- Core domain entities (`Tag`, `TagAssignment`, `TagRelation`, etc.) exist and are independent of HTTP.
- Host-minimal read paths now depend on a colocated `TagReadModelInterface`; search, suggest, and assignment reads share one read-model contract and one infrastructure implementation.
- Search and suggest now share one file-backed cache-store pattern, and write use-cases invalidate both read caches through a single query-cache invalidator instead of duplicating per-cache clear logic.

### Structural risks and growth points

1. **Boundary mismatch and dual contracts**
    - Repository contracts now live directly beside the core tag services under `App\Service\Core\Tag`, removing the competing `ServiceInterface` tree and clarifying ownership.
    - Repository contract is very broad (CRUD + policy + moderation + analytics + effects), violating Interface
      Segregation and creating a god-interface.

2. **Transport and application logic are intertwined**
    - `TagController` directly performs SQL and response mapping, bypassing dedicated application services for write
      paths.
    - `host-minimal/index.php` manually instantiates every dependency and routes HTTP via conditionals; no composition
      root abstraction.

3. **Tenant isolation is inconsistent in service abstractions**
    - Database schema is tenant-centric, but several service/repository signatures do not force tenant in method
      contracts, increasing risk of accidental cross-tenant operations.

4. **Pattern usage is partial and uneven**
    - Outbox and idempotency are present for assignment flows, but not systematically applied across all write
      operations (e.g., tag create/update/delete path).
    - Multiple policy/security classes exist (`TagPolicyEngine`, signature middleware/validators), but integration
      appears fragmented.

### Industry-grade target state

- Introduce explicit **Application layer use-cases** (`CreateTag`, `PatchTag`, `AssignTag`, etc.) returning typed
  results/errors.
- Split repository contracts by aggregate/capability (`TagWriteRepository`, `TagReadRepository`, `PolicyRepository`,
  `ModerationRepository`, `AnalyticsRepository`).
- Make `tenant` an explicit required argument/value object for all persistence boundary methods.
- Replace controller-level SQL with use-cases + mappers; keep controllers thin.
- Add a small dependency container/composition root for `host-minimal` and future framework hosts.

## 2) Code quality analysis

### Observed issues

- **God object tendency** in repository interface and `PdoTagRepository` implementation (many unrelated concerns).
- **Inconsistent style/readability**: single-line methods mixed with long procedural blocks.
- **Legacy/demo artifacts in tests path**: `tests/tag/AssignFlowTest.php` is a script, not a PHPUnit test class.
- **Potentially stale references** in README (mentions paths like `ops/`, `docs/release/...`) while repository has
  broader and partly different layout.
- **Duplicated concepts** (`src/Service/Core/Tag/TagQuotaService.php` and `src/Service/Core/Tag/QuotaService.php`) need
  consolidation review.
- Stale cache trees should not exist beside active cache/store code; the old `src/Service/Cache/` subtree was removed to keep store ownership under `src/Cache/Store/Tag/`.

### Refactor blocks

- **Refactor Block A: Repository contract decomposition**
    - Break `TagRepositoryInterface` into cohesive interfaces.
    - Introduce adapter in `PdoTagRepository` during migration to keep backward compatibility.

- **Refactor Block B: HTTP write flow cleanup**
    - Move SQL from `TagController` into `TagService`/use-cases.
    - Normalize error catalog and HTTP mapping in a shared responder.

- **Refactor Block C: Bootstrap and wiring**
    - Extract factory/bootstrap from `host-minimal/index.php` into dedicated bootstrap class/file.
    - Add route table structure instead of long if-chains.

## 3) Testing analysis

### Current state

- Core unit tests exist for normalization, graph, and service basics.
- No clear automated integration/e2e suite in CI.
- A non-test executable script is located in `tests/`, which can mislead tooling and contributors.

### Gaps

- Missing deterministic integration tests for:
    - tenant isolation guarantees,
    - idempotency behavior for all write endpoints,
    - conflict handling and optimistic race behavior,
    - migration compatibility from earliest supported schema.
- No coverage report or enforced quality gate in workflow.

### Test strategy improvements

- Create `tests/integration/` with Dockerized Postgres setup and seeded fixtures.
- Convert `tests/tag/AssignFlowTest.php` into PHPUnit integration test class.
- Add contract tests validating OpenAPI examples against host-minimal endpoints.
- Add mutation/static checks (`phpstan`, `psalm`, `phpunit --coverage-text`) in CI.

## 4) Reliability and predictability analysis

### Risks

- Inconsistent error handling strategy (`try/catch` in controller returning generic `conflict`) can hide root causes.
- Concurrency behavior appears implicit; assignment dedup relies on DB constraints/idempotency table in some flows but
  not systematically documented across all operations.
- Multiple config files for policy/security/quotas exist without a clear precedence/merge strategy document.

### Strengthening actions

- Define global error taxonomy and map DB exceptions deterministically.
- Add explicit transaction boundaries in write use-cases with retry policy for transient DB errors.
- Formalize idempotency requirements endpoint-by-endpoint in docs and tests.

## 5) Documentation and operations analysis

### Present assets

- OpenAPI contract available.
- Operational docs exist (metrics, SLO, webhooks, observability, tenancy).
- Grafana dashboard + alert yaml present.

### Missing/weak for real deployment

- No clear production deployment bundle (Helm/K8s manifests absent).
- No single runbook index covering incident response, backup/restore, migrations rollback.
- README should distinguish demo host capabilities vs full component capabilities and declare support matrix.

### Documentation tasks

- Add `docs/ops/runbook.md` (startup checks, degraded modes, rollback steps).
- Add `docs/architecture/decisions/` ADRs for tenancy model, outbox/idempotency guarantees, and repository
  decomposition.
- Add API error catalog aligned with implementation codes.

## 6) Data and migrations analysis

### Observations

- Multiple migration tracks exist (`db/postgres/migrations/*`, `data/migration/*`) which can drift.
- Newer migration files are more comprehensive, but migration ordering and source-of-truth strategy are not explicit.

### Data/migration hardening tasks

- Declare a single authoritative migration pipeline and deprecate secondary tracks.
- Add migration smoke job that runs from empty DB then verifies critical tables/indexes.
- Add forward/backward compatibility policy for at least one previous release.

## 7) CI/CD and infrastructure analysis

### Current baseline

- GitHub Actions workflow exists only for manual SLO gate execution.

### Gaps

- No default CI on push/PR for lint/tests/static analysis.
- No build/publish pipeline for container images.
- No SBOM/dependency scanning/security checks.

### CI/CD strengthening tasks

- Add `ci.yml` for: composer validate, lint, phpunit, static analysis.
- Add DB-backed integration job (service container Postgres).
- Add image build workflow with provenance + vulnerability scan.
- Add release workflow to package OpenAPI + migration artifacts.

---

## Prioritized actionable backlog

### P0 (Immediate, reliability and correctness)

1. Decompose repository interface and add tenant-explicit contracts.
2. Move `TagController` SQL writes into application services/use-cases.
3. Convert `tests/tag/AssignFlowTest.php` into deterministic PHPUnit integration test.
4. Create baseline CI workflow for tests + static analysis.

### P1 (Near-term, maintainability and operability)

1. Add architecture decision records and runbook.
2. Unify migration source-of-truth and add migration verification job.
3. Introduce centralized error catalog + responder mapping.

### P2 (Scale/readiness)

1. Add deployment manifests/examples (Kubernetes/Helm or explicit non-goal).
2. Add contract test suite from OpenAPI examples.
3. Add security/dependency scanning and release artifact pipeline.

## Suggested commit units (execution slicing)

1. **Commit 1: Contracts and interfaces**
    - Introduce segregated repository interfaces + compatibility adapter.
2. **Commit 2: Application use-cases for tag writes**
    - Add use-case classes and refactor `TagController` to delegate.
3. **Commit 3: Test modernization**
    - Replace script-like test with PHPUnit integration tests + fixtures.
4. **Commit 4: CI foundation**
    - Add workflow for lint/static/unit/integration.
5. **Commit 5: Docs and ops hardening**
    - Add runbook, ADR skeletons, migration policy doc.

## Suggested “ready next” implementation tasks

- Create `src/Application/Write/Tag/CreateTag.php`, `PatchTag.php`, `DeleteTag.php` with typed command/result DTOs.
- Keep repository contracts colocated in `src/Service/Core/Tag/` and do not reintroduce a parallel interface tree.
- Add `tests/integration/TagAssignmentIdempotencyTest.php` and `tests/integration/TenantIsolationTest.php`.
- Add `.github/workflows/ci.yml` with matrix for PHP 8.2/8.3 and Postgres service.
- Add `docs/ops/runbook.md` + `docs/architecture/adr-0001-tenancy-boundary.md`.

- Wave 18 hardens assign/unassign controller response codes, normalizes quota result shape, and removes the stale src/Service/Quota tree.


## Wave 19 bootstrap/container cleanup

- extracted a minimal host container under `src/HostMinimal/Container/`
- moved runtime/env parsing out of `host-minimal/bootstrap.php` into `HostMinimalRuntimeConfig`
- kept exported host-minimal callable keys stable while shrinking composition-root drift

- Wave 20 cleans webhook/audit/observability edges: host-minimal exports observe+webhook composition, transport contracts are stabilized, and stale Service/Audit, Service/Webhook, Service/Metric trees are removed.

- wave 21 hardens host-minimal transport security by moving signature verification into an explicit middleware pipeline, exporting the verify-signature middleware from bootstrap, and removing stale duplicate security trees.
