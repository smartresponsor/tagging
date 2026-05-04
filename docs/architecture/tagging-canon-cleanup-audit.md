# Tagging canon cleanup audit and milestone

## Snapshot baseline

This audit is based only on the provided current Tagging repository slice `TaggingThu.zip`.

## Canon correction

Tagging is a component-scoped Symfony repository. Its canonical namespace is `App\Tagging\...`.

This wave intentionally does **not** migrate Tagging to plain `App\...`. The existing Composer PSR-4 mapping remains:

```json
{
  "App\\Tagging\\": "src/"
}
```

Any future Tagging cleanup must preserve `App\Tagging\...` unless the owner explicitly replaces this component namespace canon.

## Main findings

1. **Namespace is component-scoped and must remain so**
   - Current namespace: `App\Tagging\...`.
   - This is now considered correct for Tagging.
   - Plain default `App\...` migration is not part of this patch and should not be repeated.

2. **Root deployment clutter**
   - `tag-compose.yaml` was at repository root while the Dockerfile was already under `deploy/docker/`.
   - `host/Dockerfile` duplicated deployment responsibility outside `deploy/`.
   - This wave adds `deploy/docker/compose.yaml`, `deploy/docker/host.Dockerfile`, and the missing `deploy/docker/entrypoint.sh`.
   - Root `tag-compose.yaml` and `host/` are retired through the apply script as targeted removals only.

3. **Runtime deployment gap**
   - `deploy/docker/Dockerfile` referenced `deploy/docker/entrypoint.sh`, but that file was absent in the current slice.
   - This wave provides the missing entrypoint and keeps runtime bootstrapping in `deploy/docker/`.

4. **Class/file naming convention gap**
   - Several active classes are semantically useful but not fully canonical by ecosystem naming:
     - use-case classes such as `TagCreateUseCase`, `TagPatchUseCase`, `TagDeleteUseCase`;
     - generic controllers such as `TagAssignController`, `TagSearchController`, `TagStatusController`, `TagSurfaceController`;
     - generic middleware names such as `TagAuthorizeMiddleware`, `TagObserveMiddleware`, `TagQuotaGateMiddleware`, `TagTenantContextMiddleware`, `TagVerifySignatureMiddleware`;
     - generic cache names such as `TagSearchCache` and `TagSuggestCache`;
     - mixed service/interface placement under `src/Service/Core/Tag`.
   - These should be handled in follow-up waves because renaming classes requires synchronized changes across routes, tests, docs, service wiring, and OpenAPI truth checks.

5. **Layer clarity**
   - The repository already avoids forbidden `src/Domain`, `src/Port`, `src/Adaptor`, and root `src/Tagging` paths.
   - `src/Entity/Core/Tag` is acceptable as the entity-scoped exception area.
   - `src/Http/Api/Tag/Middleware` is a remaining layer-type smell because middleware classes are nested under HTTP API rather than a top-level type layer. This should be moved carefully in a later wave.

## Milestone

### Wave 1 — deploy/root hygiene and namespace-canon lock

Status: implemented in this corrected patch.

- Preserve `App\Tagging\...` namespace and Composer PSR-4 mapping.
- Move docker compose responsibility into `deploy/docker/compose.yaml`.
- Preserve old host Dockerfile content as `deploy/docker/host.Dockerfile` for traceability.
- Add missing `deploy/docker/entrypoint.sh`.
- Extend repo hygiene audits to reject root `tag-compose.yaml` and root `host/`.

### Wave 2 — HTTP and middleware canonicalization

Recommended next.

- Rename controllers to component-prefixed/suffixed names:
  - `TagAssignController` -> `TagAssignController`
  - `TagAssignmentReadController` -> `TagAssignmentReadController`
  - `TagSearchController` -> `TagSearchController`
  - `TagSuggestController` -> `TagSuggestController`
  - `TagStatusController` -> `TagStatusController`
  - `TagSurfaceController` -> `TagSurfaceController`
- Move middleware from `src/Http/Api/Tag/Middleware` into a type layer:
  - target: `src/Middleware/Tag/...`
  - names: `TagAuthorizeMiddleware`, `TagObserveMiddleware`, `TagQuotaGateMiddleware`, `TagTenantContextMiddleware`, `TagVerifySignatureMiddleware`
- Update route catalogs, tests, docs, and public surface fixtures.

### Wave 3 — application/use-case naming

- Rename use cases to explicit forms:
  - `TagCreateUseCase` -> `TagCreateUseCase`
  - `TagPatchUseCase` -> `TagPatchUseCase`
  - `TagDeleteUseCase` -> `TagDeleteUseCase`
- Keep the component namespace `App\Tagging\...` during all class moves.
- Update cache invalidation and write flow tests.

### Wave 4 — service/interface split and type-form cleanup

- Split service contracts from implementations where required by the owner canon.
- Keep Symfony-oriented practical layers; do not introduce ports/adapters or `src/Domain`.
- Rename generic services that lack component prefix/suffix where collision risk exists:
  - `TagSearchService` -> `TagSearchService`
  - `TagSuggestService` -> `TagSuggestService`
  - `TagRateLimiter` -> `TagRateLimiter`
  - `TagTenantGuard` -> `TagTenantGuard`
  - `TagUlidGenerator` -> `TagUlidGenerator`

### Wave 5 — docs, SDK, release and public surface truth

- Update public docs, SDK examples, OpenAPI references, and release manifests after class moves.
- Remove historical references to retired paths once the runtime and tests prove the new names.
- Keep docs descriptive comments intact; only adjust type/signature documentation where needed.

## Patch application policy

This patch is intentionally non-destructive as an archive overlay. Targeted removals are listed in the PowerShell script and are limited to known legacy root deployment artifacts.
