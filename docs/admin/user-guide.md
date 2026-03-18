# Tag Admin User Guide

## Configure

- API Base: for example `http://127.0.0.1:8080`
- Tenant: for example `demo`

## Suggested flow

1. Click **Ping** and confirm the service is reachable.
2. Open the **Discovery** tab and load `/tag/_surface`.
3. If search is empty, run the fixture seed scripts.
4. Use **Create** to add one tag. The static shell auto-fills the `tagId` field after a successful create.
5. Use **Assignments** to attach or detach a tag.
6. Use **Search** to confirm the write is visible.

## Notes

- The shell sends `X-Tenant-Id` on every request.
- Write requests also send `X-Idempotency-Key`.
- The shell does not perform HMAC signing.
