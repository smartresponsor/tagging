# Tagging Wave 3 — service-depth and class-form cleanup

Wave 3 keeps the component-scoped namespace `App\Tagging\...` intact. It does not migrate Tagging to plain `App\...`.

## Canon applied

- Component namespace remains `App\Tagging\...`.
- `src/Service` is direction/type-oriented and no longer keeps the third-level domain wrapper `src/Service/Core/Tag`.
- `src/Service/Authz/Tag` is retired in favor of `src/Service/Authz`.
- Service support classes, command DTOs, HTTP middleware, and the remaining generic infrastructure entities use explicit `Tag*` names.
- Entity directory layout remains entity-scoped; only generic entity class names were prefixed where the old name did not identify the component.

## Main changes

- `src/Service/Core/Tag/*` → `src/Service/Core/*`.
- `src/Service/Authz/Tag/TagAuthorizer.php` → `src/Service/Core/Authz/TagAuthorizer.php`; legacy facade cleanup is completed by Wave 4.
- `CreateTagCommand`, `DeleteTagCommand`, `PatchTagCommand` → `TagCreateCommand`, `TagDeleteCommand`, `TagPatchCommand`.
- Generic support classes such as `QuotaService`, `TenantGuard`, `UlidGenerator`, and `TransactionRunnerInterface` now use `Tag*` forms.
- `IdempotencyMiddleware` is now `TagIdempotencyMiddleware`.
- `IdempotencyStore` and `OutboxEvent` entity class names are now `TagIdempotencyStore` and `TagOutboxEvent`.

## Not in scope

- No repository-wide delete.
- No cumulative snapshot.
- No `App\Tagging` → `App` namespace migration.
- No broad Entity directory restructuring beyond explicit class/file names.
