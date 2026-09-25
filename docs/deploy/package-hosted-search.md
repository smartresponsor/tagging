# Package-hosted read wiring

The shipped tagging component supports dual runtime: standalone Symfony verification through its Kernel and `bin/console`, plus hosted composition through `App\\Tagging\\TaggingBundle`.

A host application composes Tagging services through the imported service maps and route resources. Search and suggest stay on the shared tagging read model.

```php
$read = new App\Tagging\Repository\TagReadModel($pdo);
$suggestCache = new App\Tagging\Cache\Store\Tag\TagSuggestCache($cacheDir);
$suggest = new App\Tagging\Service\Core\TagSuggestService($read, $suggestCache);
```

The same shared read model also supports bulk assignments and related projections, so host wiring must not construct suggest directly from a raw PDO handle.

## Operational note

HTTP contract truth remains authoritative in `contracts/http/tag-openapi.yaml`, and a host application should import `config/routes.yaml` plus service maps from `config/services.yaml`.
