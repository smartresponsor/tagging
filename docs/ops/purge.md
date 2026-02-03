# Purge hooks (Tag)

## Endpoint
- POST `/tag/_purge` (recommend protecting with A1 HMAC middleware)
- Headers: `X-Tenant-Id: <tenant>` (required for `action=tenant`)
- Body (JSON):
  - `{"action":"tenant"}` → purge caches for current tenant
  - `{"action":"tag_ids","tag_ids":["123","124"]}` → purge specific tags
  - `{"action":"all"}` → purge all caches

## Config
- `config/tag_purge.yaml`: roots and safety list of allowed directories.

## host-minimal wiring
```php
$cfg = yaml_parse_file(__DIR__.'/../config/tag_purge.yaml') ?: [];
$ctl = new App\Http\Tag\PurgeController(new App\Service\Tag\PurgeService($cfg));

if ($method === 'POST' && $path === '/tag/_purge') {
  $raw = file_get_contents('php://input') ?: '';
  $req = ['method'=>$method,'path'=>$path,'headers'=>getallheaders(),'body'=>$raw];
  [$code,$hdr,$body] = $ctl->purge($req);
  http_response_code($code); foreach ($hdr as $k=>$v){ header($k.': '.$v); } echo $body; exit;
}
```

## Safety
- Service deletes only inside `allowed_roots`.
- Default is **idempotent** and safe: deletes files only, not directories.
- `policy.dry_run=true` for audit-only mode.
