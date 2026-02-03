# Metrics v1 (Prometheus)

## Endpoint
- GET `/tag/_metrics` → Prometheus text format (0.0.4).

## Metrics
- `tag_assign_total{tenant}`
- `tag_unassign_total{tenant}`
- `tag_search_total{tenant}`
- `http_request_duration_seconds_bucket{route,le}`, `http_request_duration_seconds_sum{route}`, `http_request_duration_seconds_count{route}`

## Instrumentation (example)
```php
use App\Ops\Metrics\TagMetrics;

$start = microtime(true);
// ... handle request /tag/search
TagMetrics::incSearch($tenantId);
TagMetrics::observeLatency('/tag/search', microtime(true)-$start);
```

## host-minimal wiring
```php
if ($path === '/tag/_metrics') {
  $ctl = new App\Http\Tag\MetricsController();
  [$code,$hdr,$body] = $ctl->metrics();
  http_response_code($code); foreach ($hdr as $k=>$v){ header($k.': '.$v); } echo $body; exit;
}
```
