# SDK quick usage

The shipped SDK surface matches the runnable `host-minimal` public API only:

- `GET /tag/_status` via `status()`
- `GET /tag/_surface` via `surface()`
- `create()` / `get()` / `patch()` / `delete()`
- `assign()` / `unassign()` / `assignments()`
- `bulkAssignments()` for `POST /tag/assignments/bulk`
- `assignBulkToEntity()` for `POST /tag/assignments/bulk-to-entity`
- `GET /tag/search` via `search()` with flat payloads and authoritative `total`
- `GET /tag/suggest` via `suggest()` with flat payloads

## TypeScript

```ts
import { TagClient } from './ts/tag/client';

const client = new TagClient('http://127.0.0.1:8080', { 'X-Tenant-Id': 'demo' });
await client.surface();
await client.bulkAssignments({
  operations: [
    { op: 'assign', tagId: '01HTESTASSIGN00000000000000', entityType: 'product', entityId: 'demo-product-1' },
  ],
});
await client.assignBulkToEntity({
  entityType: 'product',
  entityId: 'demo-product-2',
  tagIds: ['01HTESTASSIGN00000000000000'],
});
await client.search('priority');
await client.suggest('pri');
```

## PHP

```php
use SR\SDK\Tag\Client;

$client = new Client('http://127.0.0.1:8080', ['X-Tenant-Id' => 'demo']);
$client->surface();
$client->bulkAssignments([
    'operations' => [
        ['op' => 'assign', 'tagId' => '01HTESTASSIGN00000000000000', 'entityType' => 'product', 'entityId' => 'demo-product-1'],
    ],
]);
$client->assignBulkToEntity([
    'entityType' => 'product',
    'entityId' => 'demo-product-2',
    'tagIds' => ['01HTESTASSIGN00000000000000'],
]);
$client->search('priority');
$client->suggest('pri');
```

Gate recommendation:

- `php tools/audit/tag-sdk-audit.php`
