# Symfony-native Wave 2 bootstrap reduction map

## Purpose

This map lists the manual composition nodes in `host-minimal/bootstrap.php` that are already covered by the Symfony container and can be removed or bypassed in the next local mutation pass.

## Verified container-backed controller composition

The following controller services are already Symfony-managed:

- `App\Http\Api\Tag\TagController`
- `App\Http\Api\Tag\AssignController`
- `App\Http\Api\Tag\SearchController`
- `App\Http\Api\Tag\SuggestController`

Their constructor dependencies are already resolved through Symfony service wiring rather than needing hand-built runtime assembly.

## Verified container-backed write use cases

The following services are already Symfony-managed and used by controllers:

- `App\Application\Write\Tag\UseCase\CreateTag`
- `App\Application\Write\Tag\UseCase\PatchTag`
- `App\Application\Write\Tag\UseCase\DeleteTag`

## Verified container-backed core seams

The following interface-to-implementation seams are already resolved by Symfony:

- `TagEntityRepositoryInterface -> PdoTagEntityRepository`
- `TransactionRunnerInterface -> PdoTransactionRunner`
- `TagEntityQueryServiceInterface -> TagEntityService`

## First manual nodes to delete from `host-minimal/bootstrap.php`

### Inline write use case construction under `tagController`

Delete or bypass manual construction of:

- `new CreateTag(...)`
- `new PatchTag(...)`
- `new DeleteTag(...)`

### Inline assignment service construction under `assignController`

Delete or bypass manual construction of:

- `new AssignService(...)`
- `new UnassignService(...)`

### Inline read service construction

Delete or bypass manual construction of:

- `new SearchService(...)`
- `new SuggestService(...)`

## Recommended local reduction strategy

### Step 1

Keep `host-minimal/bootstrap.php` only as compatibility glue for any still-required runtime values such as:

- runtime metadata
- default tenant
- environment-derived configuration values

### Step 2

Remove it as the source of truth for controller and use-case composition.

### Step 3

If a host-minimal path still needs controller resolution temporarily, resolve Symfony-managed services instead of constructing them inline.

## Success signal

The next successful reduction step is reached when `host-minimal/bootstrap.php` no longer manually assembles controller graphs already covered by Symfony DI.
