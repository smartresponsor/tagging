# Assign/Unassign flows (RC5-E3)

- Idempotency via `idempotency_store(tenant,key)`; store response skeleton on completion.
- Outbox writes to `outbox_event(tenant,topic,payload)` for async delivery.
- Assign avoids duplicates via PK on `tag_link` and `ON CONFLICT DO NOTHING`.
