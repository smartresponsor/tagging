# Tag Webhooks (E19)

Events:

- tag.created, tag.updated, tag.deleted, tag.assigned, tag.merged, tag.split

Delivery:

- Sync HTTP POST with JSON: {"ts": "...", "type": "tag.created", "payload": {...}}
- Signature: HMAC-SHA256 over body, header: X-SR-Signature
- Timeout: 1s; no retries (minimal)

API:

- POST /tag/_webhooks/subscribe {url, secret?} → {ok:true}
- GET /tag/_webhooks → {items:[{url,secret?}]}
- POST /tag/_webhooks/test → emits sample event via emitter

Storage:

- Registry file: report/webhook/registry.json
- Audit NDJSON: report/tag/audit.ndjson

Metrics:

- tag_webhook_delivered_total{url,type}
- tag_webhook_failed_total{url,type}
