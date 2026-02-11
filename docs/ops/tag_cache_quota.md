# E18 Cache & Quota

## Cache integration

- For list/facet/cloud/labels endpoints:
    1) Build key from query parameters.
    2) Try `TagCache->get(kind, params)`; if hit -> return.
    3) On miss -> fetch from repo, then `set(kind, params, result)`.
- Invalidate cache on write paths (create/update/delete/assign/merge/split).
- Metrics: tag_cache_hits_total{kind}, tag_cache_misses_total{kind}.

## Quota

- Before serving request: `TagQuota->check(actor, op)` where op in {read,write}.
- On exception `quota_exceeded` map to HTTP 429 with JSON body `{code:"quota_exceeded"}`.
- Metrics: tag_quota_denied_total{actor,op}.
