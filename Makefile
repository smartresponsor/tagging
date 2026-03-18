.SILENT:

help:
	@echo "Targets: up, migrate, migration-smoke, seed, clear, smoke, audit, preflight, serve, logs, down, ps"
	@echo "Set DB_DSN/DB_USER/DB_PASS and optional TENANT=demo."

lint:
	composer run -n lint

serve:
	bash tools/local/tag-serve.sh

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
