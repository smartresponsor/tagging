.SILENT:

help:
	@echo "Targets: up, migrate, migration-smoke, seed, clear, smoke, audit, preflight, serve, symfony-serve, lint, cs-check, cs-fix, phpstan, test, test-integration, test-panther, test-e2e, fixture-dry-run, logs, down, ps"
	@echo "Set DB_DSN/DB_USER/DB_PASS and optional TENANT=demo."

lint:
	composer run -n lint

cs-check:
	composer run -n cs:check

cs-fix:
	composer run -n cs:fix

phpstan:
	composer run -n phpstan

serve:
	bash tools/local/tag-serve.sh

symfony-serve:
	composer run -n symfony:server:start

migrate:
	php tools/db/tag-migrate.php

migration-smoke:
	bash tools/db/tag-migration-smoke.sh

seed:
	php tools/seed/tag-seed.php

clear:
	php tools/seed/tag-clear.php

smoke:
	php tools/smoke/tag-smoke.php

test:
	composer run -n test:unit

test-integration:
	composer run -n test:integration

test-panther:
	composer run -n test:panther

test-e2e:
	composer run -n test:e2e

fixture-dry-run:
	composer run -n fixture:dry-run

audit:
	php tools/audit/tag-surface-audit.php
	php tools/audit/tag-contract-audit.php
	php tools/audit/tag-route-controller-audit.php
	php tools/audit/tag-bootstrap-audit.php
	php tools/audit/tag-version-audit.php
	php tools/audit/tag-config-audit.php
	php tools/audit/tag-sdk-audit.php

preflight:
	php tools/audit/tag-bootstrap-audit.php
	php tools/audit/tag-version-audit.php
	php tools/audit/tag-config-audit.php
	php tools/audit/tag-sdk-audit.php
	php tools/release/tag-preflight.php

preflight:
	php tools/release/tag-preflight.php

logs:
	docker compose logs -f --tail=200

down:
	docker compose down -v

ps:
	docker compose ps

up:
	docker compose up -d --build
