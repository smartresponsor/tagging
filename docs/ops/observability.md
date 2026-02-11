# E25 Observability+

Endpoints:

- GET /tag/_status → минимальная проверка здоровья

Metrics:

- tag_request_latency_seconds{op} summary (read/write)
- tag_error_total{op,cls} counters (cls=4xx|5xx)
- (existing) tag_up, tag_cache_*, tag_webhook_*

Slowlog:

- report/tag/slowlog.ndjson
- Пишется, если read > 500ms или write > 1000ms (по умолчанию; настраивается)

Integration:

- Добавьте middleware Observe перед бизнес-обработчиками.
- Конфиг см. config/tag_observability.yaml

Ops:

- Экспортируйте /tag/_metrics в Prometheus, а slowlog собирайте filebeat/fluent-bit.
