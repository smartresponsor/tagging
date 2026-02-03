.SILENT:

help:
	@echo "Targets: up, migrate, seed, smoke, slo, logs, down, ps"
	@echo "Set SRC_ROOT in .env (see .env.example)."

up:
	docker compose up -d --build

migrate:
	# apply all *.sql under ${SRC_ROOT}/db/postgres/migrations in filename order
	bash tools/db/migrate.sh

seed:
	# run PHP seeder from E8
	docker compose exec -T tag bash -lc 'TENANT=$${TENANT:-demo} php /app/tools/seed/tag-seed.php || true || true'

smoke:
	bash tools/smoke/smoke.sh

slo:
	bash tools/synthetic/slo.sh

logs:
	docker compose logs -f --tail=200

down:
	docker compose down -v

ps:
	docker compose ps
