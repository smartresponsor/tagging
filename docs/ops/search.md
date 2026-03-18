# Search v1 (DB-backed runtime)

The shipped runtime is Postgres-backed.

- Search reads tag entities from the database.
- Suggest reads tag entities from the database.
- Assignment reads use the database-backed relation tables.
- The shipped public runtime shell is DB-backed; file-based assignment and synonym stores are historical notes only and are not part of the runnable host.

## ETag

- `GET /tag/search` responds with an ETag (weak).
- If the client sends `If-None-Match` and it matches, the runtime may return `304`.
