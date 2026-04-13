# Symfony-native Wave 2 DI cutover map

## Purpose

This document identifies the first manual composition hotspots that must be migrated out of `host-minimal/bootstrap.php` and into Symfony-native service wiring.

Wave 2 is focused on composition cutover, not on new product behavior.

## Primary manual composition hotspots

### 1. Write use case assembly inside `tagController`

`host-minimal/bootstrap.php` currently instantiates these use cases inline:

- `CreateTag`
- `PatchTag`
- `DeleteTag`

These instances are manually injected into `TagController` instead of being resolved as Symfony services.

### Wave 2 target

- register write use cases as Symfony services
- preserve interface-based controller dependencies where useful
- remove inline `new CreateTag(...)`, `new PatchTag(...)`, `new DeleteTag(...)`

### 2. Assignment service assembly inside `assignController`

`AssignController` currently receives manually created:

- `AssignService`
- `UnassignService`

### Wave 2 target

- register assignment services in Symfony DI
- inject them through controller wiring instead of manual factory assembly

### 3. Search and suggest service assembly

`SearchController` and `SuggestController` currently receive:

- `new SearchService(...)`
- `new SuggestService(...)`

### Wave 2 target

- register these services in Symfony DI
- remove inline creation from `host-minimal/bootstrap.php`

### 4. Repository and transaction seams

Current manual infrastructure wiring includes:

- `PdoTagEntityRepository`
- `PdoTransactionRunner`
- `TagEntityService`
- `TagReadModel`

### Wave 2 target

Move these into Symfony-native service registration with explicit aliases only for real seams such as:

- `TagEntityRepositoryInterface -> PdoTagEntityRepository`
- `TransactionRunnerInterface -> PdoTransactionRunner`
- `TagEntityQueryServiceInterface -> TagEntityService`

### 5. Middleware and security graph

Manual middleware/security composition currently includes:

- `Observe`
- `VerifySignature`
- `TagMiddlewarePipeline`
- `NonceStore`
- `HmacV2Verifier`
- `TagMiddlewareResponder`

### Wave 2 target

- identify which parts remain runtime adapter concerns
- move reusable services into Symfony DI
- keep only minimal transitional runtime glue around any still-unmigrated dispatch path

## Recommended Wave 2 order

### Step 1

Move infrastructure and core service seams into `config/services.yaml`.

### Step 2

Move write use cases and query/search/suggest services into Symfony DI.

### Step 3

Rewire controllers to consume Symfony-managed services rather than inline instances.

### Step 4

Reduce `host-minimal/bootstrap.php` to a shrinking compatibility adapter or remove it once no longer needed.

## Success signal

Wave 2 is considered materially complete when controller/service composition no longer depends on inline `new ...` assembly inside `host-minimal/bootstrap.php` for the main public runtime path.
