# Host-minimal wiring (example)

```php
$cfg = yaml_parse_file(__DIR__.'/../config/tag_cache.yaml') ?: [];
$searchCache = new App\Cache\Tag\SearchCache($cfg['search']['dir'] ?? 'var/cache/tag-search', (int)($cfg['search']['ttl_seconds'] ?? 60));
$suggestCache = new App\Cache\Tag\SuggestCache($cfg['suggest']['dir'] ?? 'var/cache/tag-suggest', (int)($cfg['suggest']['ttl_seconds'] ?? 60));

$pdo = new PDO(getenv('DB_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=app', getenv('DB_USER') ?: 'app', getenv('DB_PASS') ?: 'app');
$read = new App\Infra\Tag\TagReadModel($pdo);
$searchCtl = new App\Http\Tag\SearchController(new App\Service\Tag\SearchService($read, $searchCache));
$suggestCtl = new App\Http\Tag\SuggestController(new App\Service\Tag\SuggestService($pdo, $suggestCache));

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
