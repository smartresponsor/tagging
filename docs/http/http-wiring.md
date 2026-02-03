# HTTP Wiring v1 (RC5-E7)

Implements REST controllers for Tag CRUD and Assign/Unassign, plus Idempotency & minimal HMAC middleware stubs.

## Endpoints
- POST /tag
- GET /tag/{id}
- PATCH /tag/{id}
- DELETE /tag/{id}
- POST /tag/{id}/assign
- POST /tag/{id}/unassign
- POST /tag/assign-bulk

Headers: X-Tenant-Id (required), X-Idempotency-Key (optional), X-SR-Signature/X-SR-Nonce (optional if SR_HMAC_SECRET set).

Generated: 2025-10-27T20:56:13.260638
