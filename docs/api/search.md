# Search & Suggest v1

- Search: substring with pagination (offset token), cache TTL default 60s.
- Suggest: prefix match with limit ≤ 50, cache TTL default 60s.
- Cache roots:
    - var/cache/tag-search
    - var/cache/tag-suggest
- Compatible with A4 purge (tenant/all). Cache filenames include tenant + normalized query.


## Host-minimal response contract

- `GET /tag/search` → `200 { ok, items, total, nextPageToken, cacheHit }`
- `GET /tag/suggest` → `200 { ok, items, cacheHit }`
- `GET /tag/assignments` → `200 { ok, items }`
- controllers also keep legacy nested `result` for search/suggest during transition
