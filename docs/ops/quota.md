# Quotas & RateLimit

## Config

See `config/tag_quota.yaml`.

- **hard.per_tenant**: token bucket per tenant+route (rps, burst).
- **hard.global**: token bucket per route (rps, burst).
- **soft.per_tenant**: minute-level caps for operations (assign/search).
- **paths**: protect/include and ignore patterns.
- **retry_after_sec**: hint returned in `Retry-After` header on 429.

## Wiring (host-minimal example)

```php
$cfg = yaml_parse_file(__DIR__.'/../config/tag_quota.yaml') ?: [];
$mw = new App\Http\Tag\Middleware\QuotaGate(new App\Service\Tag\RateLimiter(), $cfg);

$raw = file_get_contents('php://input') ?: '';
$req = ['method'=>$method,'path'=>$path,'headers'=>getallheaders(),'body'=>$raw];
list($code,$hdr,$body) = $mw->handle($req, function($r) use ($method,$path){
  // place actual route handling here
  return [200, ['Content-Type'=>'application/json'], json_encode(['ok'=>true])];
});
http_response_code($code); foreach ($hdr as $k=>$v){ header($k.': '.$v); } echo $body; exit;
```

## Metrics

- `tag_ratelimit_throttled_total{scope,route,tenant?}`
- `tag_quota_exceeded_total{tenant,op}`
  (Requires A2 addon; otherwise counters are no-op.)
