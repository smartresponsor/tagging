# RC5-E9 — Assembly & Local Smoke

**Purpose.** Склеить предыдущие конверты в локальный стенд (db+tag host) и дать команды для миграций/сидов/смоука/SLO.

## Steps
1. Создай рабочую папку `bundle/` и распакуй туда содержимое артефактов E2..E8 (сохрани структуру `db/`, `config/`, `host-minimal/`, `tools/`, `docs/`).
2. Скопируй `.env.example` в `.env` и укажи `SRC_ROOT=./bundle` (или абсолютный путь).
3. `make up` — поднимет Postgres и host (php -S).
4. `make migrate` — применит все SQL из `bundle/db/postgres/migrations`.
5. `make seed` — прогонит сидер (E8) с `TENANT=demo`.
6. `make smoke` — проверит базовые эндпойнты (create/get/assign/unassign/search).
7. `make slo` — запустит SLO-gate (если в bundle присутствует E5-скрипт).

**Notes.**
- Этот конверт не дублирует код домена; он ожидает, что ты соберёшь всё из E2..E8 в `bundle/`.
- В `docker-compose.yml` сервис `tag` монтирует `bundle/` внутрь контейнера в /app.

Generated: 2025-10-27T21:00:09.891682
