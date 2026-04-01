# Tag Admin User Guide

## Configure

- API Base: for example `http://127.0.0.1:8080`
- Tenant: for example `demo`

## Suggested flow

1. Click **Ping** and confirm the service is reachable.
2. Open the **Discovery** tab and load `/tag/_surface`.
3. If search is empty, run the fixture seed scripts.
4. Use **Create** to add one tag. The static shell auto-fills the `tagId` field after a successful create.
5. Use **Assignments** to attach or detach a tag for a single entity.
6. Use the bulk assignment surface for batch scenarios:
   - `POST /tag/assignments/bulk`
   - `POST /tag/assignments/bulk-to-entity`
7. Use **Search** and **Suggest** to confirm the write is visible.
8. For write-contract troubleshooting, remember that unassign returns `404 tag_not_found` when the tag entity itself is absent.

## Notes

- The shell sends `X-Tenant-Id` on every request.
- Write requests also send `X-Idempotency-Key`.
- Search and suggest return flat payloads without nested `result`.
- Search returns authoritative `total` for the current query.
- The shell does not perform HMAC signing.
