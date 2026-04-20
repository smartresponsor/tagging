# Install and test gates

Wave 06 hardens the repository around reproducible install and baseline PHPUnit execution.

## Gates

- `composer validate --no-interaction --strict`
- `composer run -n audit:composer-integrity`
- `composer run -n test:unit`

## Composer scripts

- `audit:composer-integrity` checks `composer.json` / `composer.lock` consistency for `require-dev`, required gate scripts, and `App\Tagging\ => src/` autoload.
- `test` runs the default PHPUnit suite.
- `test:unit` runs the `unit` testsuite.
- `test:integration` runs the `integration` testsuite.
- `gate:install-test` combines validate, composer-integrity, and unit tests into one baseline gate.

## Expected use

Local baseline:

```bash
composer install
composer run -n gate:install-test
```

CI baseline should fail fast on composer metadata drift before deeper runtime jobs.

## Default test policy

- `composer test` runs the **unit** suite only.
- `composer run -n test:integration` runs the PostgreSQL-backed integration suite explicitly.
- Integration tests skip themselves when the PostgreSQL test database is unreachable.
