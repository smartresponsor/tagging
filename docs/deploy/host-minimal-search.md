# Host-minimal read wiring (example)

The shipped `host-minimal` runtime uses one `TagReadModel` for both search and suggest paths, then composes both services from that shared read-model.

```php
$cfg = yaml_parse_file(__DIR__.'/../config/tag_cache.yaml') ?: [];
$searchCache = new App\Cache\Store\Tag\SearchCache($cfg['search']['dir'] ?? 'var/cache/tag-search', (int)($cfg['search']['ttl_seconds'] ?? 60));
$suggestCache = new App\Cache\Store\Tag\SuggestCache($cfg['suggest']['dir'] ?? 'var/cache/tag-suggest', (int)($cfg['suggest']['ttl_seconds'] ?? 60));

$pdo = new PDO(getenv('DB_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=app', getenv('DB_USER') ?: 'app', getenv('DB_PASS') ?: 'app');
$read = new App\Infrastructure\ReadModel\Tag\TagReadModel($pdo);
$searchCtl = new App\Http\Api\Tag\SearchController(new App\Service\Core\Tag\SearchService($read, $searchCache));
$suggestCtl = new App\Http\Api\Tag\SuggestController(new App\Service\Core\Tag\SuggestService($read, $suggestCache));

if ($method === 'GET' && $path === '/tag/search') {
  $req = ['headers'=>getallheaders(),'query'=>$_GET];
  [$code,$hdr,$body] = $searchCtl->get($req);
  http_response_code($code); foreach ($hdr as $k=>$v){ header($k.': '.$v); } echo $body; exit;
}
if ($method === 'GET' && $path === '/tag/suggest') {
  $req = ['headers'=>getallheaders(),'query'=>$_GET];
  [$code,$hdr,$body] = $suggestCtl->get($req);
  http_response_code($code); foreach ($hdr as $k=>$v){ header($k.': '.$v); } echo $body; exit;
}
```

## Notes

- The full runtime router is generated from `tag.yaml` through `host-minimal/route.php`.
- Search and suggest are only part of the shipped surface together with CRUD, assignments, bulk assignments, status, and discovery.
- This example is intentionally focused on read wiring only; it is not the complete host bootstrap.
