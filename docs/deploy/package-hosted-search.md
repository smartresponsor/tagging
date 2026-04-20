# Package-hosted read wiring

The shipped tagging component is a hosted package. It does not ship its own runtime kernel or front controller.

A host application composes Tagging services through the imported service maps and route resources. Search and suggest stay on the shared tagging read model.

```php
$read = new App\Tagging\Infrastructure\ReadModel\Tag\TagReadModel($pdo);
$suggestCache = new App\Tagging\Cache\Store\Tag\SuggestCache($cacheDir);
$suggest = new App\Tagging\Service\Core\Tag\SuggestService($read, $suggestCache);
```

The same shared read model also supports bulk assignments and related projections, so host wiring must not construct suggest directly from a raw PDO handle.

## Operational note

Route truth remains authoritative in `tag.yaml`, and the host application should import `config/routes.yaml` plus service maps from `config/services.yaml`.
