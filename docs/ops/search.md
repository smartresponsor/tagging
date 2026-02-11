# Search v1 (file driver & Postgres)

- File driver: search по label.ndjson и synonym.ndjson (contains/prefix); сортировка по score+usageCount.
- Entities by tags: assignment.ndjson агрегируется в памяти.
- DB mode: включите prefer_db=true и примените V29__tag_search.sql (pg_trgm).

## ETag

- GET /tag/search отвечает заголовком ETag (weak). Если клиент присылает If-None-Match и он совпадает → 304.
