# Tagging Wave 6 — Persistence implementation naming cleanup

## Scope

This wave keeps the component-scoped namespace intact:

- `App\Tagging\...` remains canonical.
- No migration to plain `App\...` is performed.

The wave normalizes persistence implementation class names so the component prefix leads the file and class form.

## Renames

- `DoctrineTagEntityRepository` -> `TagDoctrineEntityRepository`
- `DoctrineTagRepository` -> `TagDoctrineRepository`
- `InMemoryTagRepository` -> `TagInMemoryRepository`

The implementation mechanism (`Doctrine`, `InMemory`) is preserved, but it no longer precedes the component prefix.

## Guardrail

`tools/audit/tag-persistence-implementation-naming-audit.php` rejects legacy persistence implementation names in:

- `src`
- `tests`
- `config`
- `docs`
- `release`
- `migration`

## Non-goals

- No entity-first redesign.
- No namespace migration.
- No full repository rewrite.
- No cumulative snapshot delivery.
