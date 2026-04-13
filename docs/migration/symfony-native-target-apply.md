# Apply Symfony-native target replacements

## Purpose

This note explains how to apply the prepared Symfony-native replacement files that were staged under `migration/symfony-native-target/` during the migration branch.

## Prepared replacement set

The following existing files have prepared target replacements:

- `composer.json`
- `public/index.php`
- `tag.yaml`
- `config/tag_runtime.php`
- `docs/http/http-wiring.md`

## Apply script

Use:

```bash
php tools/migration/apply-symfony-native-target.php --dry-run
php tools/migration/apply-symfony-native-target.php
```

### Options

- `--dry-run` prints the replacement plan without mutating files
- `--no-backup` applies replacements without creating a backup copy

## Backups

By default the script stores replaced originals under:

- `var/migration-backup/<timestamp>/...`

This backup path is intended only for the transition window. Once the migration is validated, transitional backups should not be kept as active repository truth.

## Expected next step after apply

After replacements are applied, the branch should proceed with:

1. dependency installation/update
2. verification of the new Symfony-native baseline surface
3. migration of runtime wiring out of `host-minimal/bootstrap.php`
4. cleanup of stale host-minimal runtime references
