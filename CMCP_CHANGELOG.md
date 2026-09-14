# CMCP execution journal

## engine-20260906011001-tagging-e83f06

### Iteration 1 — reconnaissance and baseline

- Read the execution specification, repository governance, README, Composer manifest, runtime/service configuration, entity-first note, and RC inventory.
- Inspected the required dependency contour: Objecting, Cruding, Viewing, and Interfacing are real `*@dev` Composer dependencies resolved through local symlink path repositories. Gating and Canonization remain read-and-comply repositories.
- Baseline branch: `fix/symfony-native-cutover-defects`; the worktree already contains 113 changes belonging to an in-progress Symfony-native/Objecting/Cruding cutover. Preserve and validate that work instead of replacing it.
- Selected RC-critical workstream: finish and verify the current Symfony-native cutover, especially zero local generic CRUD routes/controllers, dependency resolution, Objecting field adoption, container wiring, and removal of stale surfaces.
- Separate growth workstream (post-RC): improve taxonomy governance, hierarchical/synonym UX, bulk ergonomics, analytics, and external interoperability only after correctness gates are green.
- Material risks: large pre-existing dirty worktree, backup artifacts visible in source inventory, README/runtime truth drift, and potential lock/vendor mismatch after path-dependency changes.
- Planned gates: Composer validation/integrity, targeted Cruding resolution test, canonical structure/stale-reference audits, PHP lint/PHPStan, unit suite, and release preflight; then Git integration if green.

Что имеем? Фактический cutover уже начат и его направление соответствует канону зависимостей и границ ответственности.

Что осталось? Детерминированно проверить текущий diff, исправить только подтверждённые сбои, закрыть backup/stale artifacts и довести ветку до проверенного RC-состояния.

### Iteration 2 — material implementation

- Verified Composer dependency wiring and the current Cruding public API.
- Updated `TagCrudingEntrypointResolutionTest` from removed nested namespaces to the role-first `CrudContextDTO` and `CrudServiceClassNameResolver` API.
- Targeted result: 14 tests, 27 assertions, green.

### Iteration 3 — verification and fix

- Repaired the truncated `TaggingPolicyConfigData` class.
- Removed the incompatible Objecting identity trait composition from `TagEntity`: Tagging keeps its existing string primary key while Objecting supplies the embedded UUID/slug value object and the specialized audit/title/state/soft-delete contracts.
- PHP syntax gate is green.
- PHPStan proceeds past parsing but reports pre-existing incomplete cutover surfaces: self-referential compatibility files, missing Administering types, and a missing integration test base.

### Iteration 4 — debt closure and integration decision

- Release preflight is red because files referenced by the current scripts were deleted without updating all consumers: `tag-route-controller-audit.php`, SDK clients, and `config/tag_public_surface.php`.
- The required Cruding sibling is itself dirty with 391 pending changes, including deletion/replacement of the API consumed by this branch.
- No stage, commit, push, or PR was performed: publishing the 110-file dirty cutover with red acceptance gates would create a false RC.

### Iteration 5 — final acceptance and handoff

- Green evidence: canonical stale audit, canonical structure audit, Composer integrity audit, PHP lint, and targeted Cruding integration test.
- Red evidence: strict Composer validation warnings for unbounded local development constraints, PHPStan incomplete cutover failures, unit run transport timeout after the earlier fatal was fixed, and release preflight missing-artifact failures.
- Final state remains intentionally dirty and uncommitted. The bounded next action is to complete the existing Tagging cutover (restore or retarget the missing test/audit/SDK/public-surface artifacts) after the Cruding branch API is finalized, then rerun full acceptance.

Что имеем? Устранены три concrete blockers внутри Tagging и получены зелёные targeted gates без выхода за repository boundary.

Что осталось? RC не достигнут: текущий большой pre-existing cutover и его sibling dependency требуют согласованного завершения; commit/push заблокированы красными acceptance gates.

### Continued iteration 2/5 — material implementation

- Rechecked the previous report against the live worktree.
- Retargeted SDK audits and tests from obsolete `TagTagClient.php` / `tag-tag-client.ts` paths to the canonical `TagClient.php` / `tag-client.ts` files.
- Retargeted runtime consistency and version verification to `config/tag_runtime.php` as the single public-surface source; the removed `config/tag_public_surface.php` remains forbidden.
- Restored `tag-route-controller-audit.php` as a zero-local-CRUD boundary gate. It requires `cruding/crud`, rejects the retired local generic CRUD route files, and rejects local `*Crud*` controllers.
- Verification: SDK audit, version audit, changed-file PHP lint, route/controller audit, and release preflight are green.

Что имеем? Release preflight is green and stale SDK/public-surface consumers are removed without restoring local CRUD ownership.

Что осталось? Iteration 3 must rerun PHPStan and the unit suite, then fix the next smallest in-scope failure set.

### Continued iteration 3/5 — verification and fix

- Re-ran PHPStan and the complete unit suite. PHPStan now reaches semantic analysis and isolates three unfinished cutover clusters: obsolete self-alias compatibility files, absent Administering contracts, and a missing integration-test base class.
- Removed stale test and audit expectations for deleted local Symfony-native route projections; generic CRUD routing remains owned by Cruding.
- Retargeted synthetic tooling, service-interface locations, hosted runtime truth, and the wave-10 tooling audit to the canonical files that exist in the repository.
- Verification: changed-file PHP lint is green for 100 PHP files, unit suite is green (197 tests, 1468 assertions), and release preflight is green.

Что имеем? Unit, syntax, and release-preflight gates are green with the zero-local-CRUD boundary preserved.

Что осталось? Iteration 4 should resolve the bounded PHPStan clusters, rerun full acceptance, and only then decide staging/commit integration.

### Continued iteration 4/5 — debt closure and integration

- Declared the existing Administering config-tool dependency and its local Symfony bundle repository; explicitly allowed the required Symfony Runtime Composer plugin and refreshed the lock/install graph.
- Made legacy compatibility aliases statically analyzable without restoring duplicate entity or repository implementations.
- Restored the shared integration evidence fixture against canonical Tagging entities, repositories, and services.
- Updated stale assignment fixtures and replaced local-controller integration coverage with service-level write/read symmetry after Cruding assumed generic controller ownership.
- Verification: PHPStan is green across 331 files; unit suite remains green (197 tests, 1468 assertions); release preflight remains green.
- Newly exercised integration suite reaches all 10 tests but is red because write services now return guarded `assign_failed` / `unassign_failed` results. This runtime exception cluster must be diagnosed before committing.

Что имеем? The reported PHPStan blocker is closed without reintroducing local CRUD ownership.

Что осталось? Iteration 5 must expose and fix the underlying Doctrine write exception, rerun integration and full acceptance, then create the coherent cutover commit only if all required gates are green.

### Continued iteration 5/5 — integration closure and RC acceptance

- Re-read the authoritative Canonization architecture material and mapped Tagging to the applicable rules: `Canon007Psr4IdentityRule`, `Canon008ComposerDependencyIntegrityRule`, `Canon018ComposerIdentityMappingRule`, and `Canon021CrudingOwnsGenericCrudRule`. `Canon022StandaloneApplicationDependencyBaselineRule` is non-applicable because Tagging is package-mode and does not own standalone Symfony boot surfaces.
- Re-read the mandatory dependency contour contracts from Objecting, Cruding, Viewing, and Interfacing, plus Gating as the executable enforcement companion.
- Market/maturity benchmark remained separated from RC correctness: mature tagging/taxonomy systems converge on stable IDs/slugs, scoped visibility/permissions, searchable assignment APIs, vocabularies/hierarchy, and controlled taxonomy governance; hierarchy/synonym/UX maturity remains a growth track rather than an RC blocker.
- Fixed TagEntity Doctrine indexes and unique constraints that still referenced retired Objecting-prefixed physical columns; current Objecting embeddables use entity-native `created_at`, `slug`, and `uuid` columns with `columnPrefix: false`.
- Fixed TagReadModel DQL to address the current embedded Objecting property paths (`objectIdentity.objectSlug` and `objectTitle.firstTitle`) instead of legacy virtual `slug` / `nameEntity` fields.
- Fixed the Cruding DTO namespace case in the targeted integration contract test (`DTO`, not `Dto`) in accordance with literal PSR-4 identity.
- Verification is green for integration (10 tests, 63 assertions), unit (197 tests, 1468 assertions), PHPStan (331 files, no errors), changed-file PHP lint (100 files), release preflight, canonical structure audit, and canonical stale-reference audit.
- `composer validate --strict --check-lock` reports a valid manifest but exits 1 because the five local platform dependencies still use unbounded `*@dev` constraints. No arbitrary branch/version pin was invented without an authoritative package-version contract.
- Git integration remains deferred because the strict Composer acceptance signal is not green and the branch contains a large pre-existing cutover; publishing it as an RC commit would overstate release readiness.

Что имеем? Runtime, integration, static analysis, syntax, release-preflight, and canonical structure/stale gates are green; the previously blocking Doctrine write/read path is closed.

Что осталось? RC packaging still needs an authoritative bounded-version policy for the local platform dependencies (Objecting, Cruding, Viewing, Interfacing, Administering), followed by strict Composer validation and then coherent stage/commit/push integration.

### Continued RC packaging closure — Canon043/Canon045

- Materialized the authoritative first-party development package policy in Canonization/Gating: `Canon043DevelopmentComposerDependencyVersionRule` requires `minimum-stability: dev`, `prefer-stable: true`, exact `dev-master` dependency constraints, and `options.versions[package] = dev-master` for local first-party path repositories.
- Materialized `Canon045DevelopmentComposerRepositoryClosureRule`: because Composer does not inherit dependency-owned `repositories`, the root development manifest must expose every reachable first-party runtime path repository. The existing `Canon044ObjectingSystemFieldNamingRule` remains unchanged; repository closure was assigned Canon045 after detecting the live Canon044 identity.
- Updated Tagging Composer topology to canonical `dev-master` identity for Objecting, Cruding, Viewing, Interfacing, and Administering; added root path visibility for transitive Collectioning and Tabling without falsely making them direct Tagging runtime dependencies.
- Refreshed the lock graph package-scoped. Cruding moved from the feature-version lock identity to `dev-master`; Collectioning and Tabling are now locally junctioned at `dev-master`. The no-`-W` update avoided unrelated Doctrine upgrades; only required transitive packages were added.
- Fixed `tag-composer-integrity-audit.php` to validate `require-dev` packages against the complete Composer lock package set rather than assuming a `packages-dev` section.
- Final verification: `composer validate --strict --check-lock` green; Composer integrity green; integration 10/63 green; unit 197/1468 green; PHPStan 331 files green; changed PHP lint 100 files green; release preflight green; canonical structure and canonical stale-reference audits green.
- Gating calibration is green; Gating reports 0 failed rules, with Canon043 and Canon045 passing and the Canonization/Gating mirror contract covering 46 rules. The remaining Gating coverage warning is pre-existing stale coverage evidence and is non-blocking for this packaging change.

Что имеем? Прежний RC packaging/version-policy blocker закрыт: канон материализован, executable enforcement существует, Tagging strict Composer validation и полный acceptance contour зелёные.

Что осталось? Только Git integration текущего большого cutover: проверить финальный diff/status, зафиксировать Canonization и Gating как отдельные канонические commits и затем интегрировать Tagging coherently без смешивания с посторонними изменениями.
