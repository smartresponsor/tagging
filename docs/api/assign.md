# Assign/Unassign flows (RC5-E3)

- Idempotency via `idempotency_store(tenant,key)`; store response skeleton on completion.
- Outbox writes to `outbox_event(tenant,topic,payload)` for async delivery.
- Assign avoids duplicates via PK on `tag_link` and `ON CONFLICT DO NOTHING`.


HTTP hardening:

- assign returns `404` with `code=tag_not_found` when target tag is missing.
- assign/unassign return `409` with `code=idempotency_conflict` on idempotency checksum mismatch.
- assign/unassign return `500` with explicit failure codes on store/outbox faults.
- tenant header lookup accepts both `X-Tenant-Id` and `x-tenant-id`.
- quota decisions expose `used`, `max`, `remaining`, and an explicit quota code when exceeded.
