# Tagging Canonicalization Wave 2 — Class Form Cleanup

## Scope

Wave 2 deliberately does **not** migrate the component namespace. The canonical namespace for this repository remains:

```text
App\Tagging\...
```

This wave only addresses class/file form problems where names were too generic for Smart Responsor component conventions.

## Applied class-form direction

The cleanup follows this rule:

```text
Tag + responsibility + Symfony/class role suffix
```

Examples:

- `CreateTag` → `TagCreateUseCase`
- `Authorize` → `TagAuthorizeMiddleware`
- `JsonResponder` → `TagJsonResponder`
- `SearchController` → `TagSearchController`
- `OutboxPublisher` → `TagOutboxPublisher`
- `HmacV2Verifier` → `TagHmacV2Verifier`

## Why this wave is narrow

This repository already contains working business code, docs, fixtures, release assets, SDK references, route catalogs, and tests. A broad one-shot restructure would produce high review noise and unnecessary runtime risk.

Wave 2 therefore focuses on the most visible generic names in:

- write use cases;
- cache stores;
- HTTP controllers/responders/middleware;
- outbox/persistence-facing infrastructure edge;
- ops/security helpers;
- slug and core service forms;
- directly coupled tests and route/docs references.

## Remaining milestone after Wave 2

1. **Layer typing pass**
   - Evaluate `src/Http/Api/Tag/Middleware` versus canonical `src/Middleware/...`.
   - Evaluate `src/Ops/*` versus type-specific Symfony folders.
   - Decide whether `Infrastructure` remains acceptable in this component or whether pieces should move into typed Symfony-oriented layers.

2. **Entity/table canon pass**
   - Verify every Doctrine table uses the required `tag` prefix.
   - Keep Entity-specific folder exceptions separate from services/controllers.

3. **Fixture/demo purity pass**
   - Ensure interface/demo pages consume component fixtures or explicit demo fixtures, not hidden hardcoded UI data.

4. **Root/deploy final pass**
   - After Wave 1 is applied, keep Docker under `deploy/docker`.
   - Avoid reintroducing root deployment artifacts.

5. **Proof pass**
   - Run PHPUnit/PHPStan/route smoke after all mechanical renames are applied locally.
   - Treat runtime proof as a separate phase from architecture/business completeness.
