# Tag demo seed

## Validate fixture

```bash
php tools/seed/tag-fixture-validate.php
```

## Run deterministic seed

```bash
export DB_DSN="pgsql:host=localhost;port=5432;dbname=app"
export DB_USER="app"
export DB_PASS="app"
export TENANT="demo"
php tools/db/tag-migrate.php
SEED_RESET=1 php tools/seed/tag-seed.php
```

## Clear

```bash
php tools/seed/tag-clear.php
```

## Verify runtime truth

```bash
curl -sS 'http://127.0.0.1:8080/tag/_surface'
curl -sS 'http://127.0.0.1:8080/tag/_status'
BASE_URL=http://127.0.0.1:8080 TENANT=demo php tools/smoke/tag-smoke.php
```

## Final reference

For the compact final demo truth pack, see `docs/demo/tag-final-demo-pack.md`.
