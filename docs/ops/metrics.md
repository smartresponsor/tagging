# Metrics v1 (Prometheus)

## Public endpoint

There is no shipped `/tag/_metrics` route in the current public contract. Metrics support is internal instrumentation that a Symfony host may export through its own observability surface.

## Metrics

- `tag_assign_total{tenant}`
- `tag_unassign_total{tenant}`
- `tag_search_total{tenant}`
- `http_request_duration_seconds_bucket{route,le}`, `http_request_duration_seconds_sum{route}`,
  `http_request_duration_seconds_count{route}`

## Instrumentation (example)

```php
use App\Tagging\Recorder\Metrics\TagMetrics;

$start = microtime(true);
// ... handle request /tag/search
TagMetrics::incSearch($tenantId);
TagMetrics::observeLatency('/tag/search', microtime(true)-$start);
```

## Hosted composition

Tagging instrumentation is composed through the Symfony service container. A host that exports Prometheus metrics owns that external endpoint and must not imply that `/tag/_metrics` is part of Tagging's shipped HTTP contract.
