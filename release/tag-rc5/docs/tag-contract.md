# Tag API Contract v1.0 (frozen)

- Headers: `X-Tenant-Id` (required), `X-Idempotency-Key` (optional on mutating requests).
- If A1/HMAC enabled: `X-SR-Signature` + `X-SR-Nonce` must be provided and verified.
- Pagination: `pageSize` ≤ 100, opaque `pageToken`.
- Stability: **frozen**. Any breaking change → v1.1; non-breaking via new fields (nullable) or feature flags.

Generated: 2025-10-27T20:23:28.763706
