# Tagging Wave 7 — test class form cleanup

## Scope

Wave 7 canonicalizes the PHPUnit and test-helper surface without changing the component namespace. `App\Tagging\...` remains the canonical namespace for this component.

## Rules enforced

- Test files under `tests/**` must use the `Tag` component prefix.
- Test symbols declared in those files must use the `Tag` component prefix.
- `TagDoctrineEntityManagerFactory` remains allowed as-is because it already follows the component prefix rule.
- The legacy root executable `Tagging` is retired from the repository root; smoke and SLO scripts belong under `tools/**`.

## Patch discipline

This wave is a touched-files patch. The apply script backs up and removes only explicitly listed legacy test files and the root `Tagging` artifact, then overlays the touched files from the ZIP. It does not delete or overwrite the full repository.

## Verification

```bash
composer dump-autoload
php tools/audit/tag-test-class-form-audit.php
vendor/bin/phpunit tests/TagTestClassFormWave7AuditTest.php
```