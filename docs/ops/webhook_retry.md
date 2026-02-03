# E24 Webhook Retry & Backoff
Config (`config/tag_webhooks.yaml`):
  - retries: 5
  - base_delay_ms: 200
  - max_delay_ms: 10000
  - spool_dir: report/webhook/spool
  - dlq_path: report/webhook/dlq.ndjson

Flow:
  1) TagAuditEmitter emits audit line and enqueues jobs via TagWebhookSender.
  2) Worker (`tools/webhook_worker.php`) drains the spool with exponential backoff.
  3) After retries exhausted, job goes to DLQ (append NDJSON).

Metrics:
  - tag_webhook_retried_total{url,type}
  - tag_webhook_dlq_total{url,type}
  - (existing) delivered_total / failed_total

Ops:
  - Run worker: `php tools/webhook_worker.php` (cron/systemd).
  - Inspect DLQ: `report/webhook/dlq.ndjson`.
