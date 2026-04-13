# Verify Symfony-native wave bundle

## Purpose

This checklist defines the post-apply verification sequence for the Symfony-native wave bundle.

It is intended to be used after applying:

- `tools/migration/apply-symfony-native-target.php`
- `tools/migration/apply-wave2-host-minimal-bridge.php`
- or the combined `tools/migration/apply-symfony-native-wave-bundle.php`

## Minimum verification sequence

Run these checks from the repository root:

```bash
php bin/console about
php bin/console debug:router --no-interaction
php bin/console debug:container App\Http\Api\Tag\TagController
php bin/console debug:container App\Application\Write\Tag\UseCase\CreateTag
composer validate
```

## Expected signals

### Symfony runtime baseline

- `php bin/console about` succeeds
- reported kernel is `App\Kernel`
- reported Symfony runtime is available without host-minimal bootstrap as the primary path

### Router baseline

- tag routes are visible in `debug:router`
- public CRUD and search/suggest routes remain present

### DI baseline

- `TagController` resolves through Symfony container wiring
- `CreateTag` resolves through Symfony container wiring
- no fallback to manual host-minimal composition is required for the primary runtime path

### Composer baseline

- `composer validate` succeeds
- the repository remains internally consistent after replacement and bridge apply steps

## Additional recommended checks

Where practical, also run:

```bash
composer test
```

and any local smoke checks that still exercise the shrinking compatibility path.

## Failure handling

If any verification step fails:

1. inspect the most recent apply step
2. review backup copies under `var/migration-backup/<timestamp>/...`
3. fix the immediate blocker before proceeding to further sanitation waves

## Interpretation

Passing this checklist means the wave bundle apply did not merely copy files; it preserved a live Symfony-native runtime baseline that is ready for the next cleanup wave.
