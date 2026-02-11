# Tag Admin v4 — User Guide

## Prerequisites

- Running Tag service (RC3+): endpoints /tag/_status, /tag/search, /tag/{id}/assign, /tag/{id}/synonym, etc.
- HMAC V2 shared secret and Tenant id.

## Configure

- API Base: e.g. `http://localhost:8080` (host-minimal) or your gateway prefix.
- Tenant: value for `X-Tenant-Id` header.
- Secret: shared secret for HMAC (X-SR-Signature).

## Features

- Search: label/slug/synonyms; supports ETag on server, shows HTTP code & JSON.
- Assignments: assign/unassign tag to entity; bulk assign multiple tags.
- Synonyms: list/add/delete synonyms for a tag.
- Shortcuts: Ctrl+1/2/3 to switch tabs; Ctrl+K to focus search.

## Notes

- SignatureV2: payload is `ts\nnonce\nMETHOD\nPATH\nsha256(body)`, headers:
    - X-SR-Timestamp (unix seconds)
    - X-SR-Nonce (random 12 chars)
    - X-SR-Signature (hex HMAC-SHA256)
- If your backend uses different signature rules, adjust `hmacSign()` in `admin/app.js`.
