# Tenancy & Quotas (E30)

- Заголовок: `X-Tenant-Id` (см. config/tag_tenant.yaml).
- File driver: данные каждого тенанта изолированы в `report/tag/<tenant>/`.
- DB driver: колонки `tenant_id` в `tag` и `tag_assignment` (см. V30__tenant_tag.sql).

## Wiring (file driver example)

```php
$guard = new App\Service\Tag\TenantGuard($cfgTenant);
$tenantId = $guard->requireTenant(getallheaders());
$repo = new App\Data\Tag\MultiTenantAssignmentRepository($tenantId, 'report/tag');
$svc = new App\Service\Tag\AssignmentService($repo);
$ctl = new App\Http\Tag\AssignmentController($svc, null);
```

## Quotas

- `max_tags` и `max_assignments` проверяются по количеству строк в `label.ndjson` и `assignment.ndjson` тенанта.
- При превышении возвращайте HTTP 429 и инкрементируйте метрику `tag_quota_exceeded_total`.
