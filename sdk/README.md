# SDK quick usage

The shipped SDK surface matches the runnable `host-minimal` public API only.

## Public endpoints

- `GET /tag/_status`
- `GET /tag/_surface`
- `POST /tag`
- `GET /tag/{id}`
- `PATCH /tag/{id}`
- `DELETE /tag/{id}`
- `POST /tag/{id}/assign`
- `POST /tag/{id}/unassign`
- `GET /tag/assignments`
- `GET /tag/search`
- `GET /tag/suggest`

## TypeScript

```ts
import { TagClient } from './ts/tag/client';

const client = new TagClient('http://127.0.0.1:8080', { 'X-Tenant-Id': 'demo' });
await client.surface();
await client.search('priority');
```

## PHP

```php
use SR\SDK\Tag\Client;

$client = new Client('http://127.0.0.1:8080', ['X-Tenant-Id' => 'demo']);
$client->surface();
$client->search('priority');
```

Gate recommendation:

- `php tools/audit/tag-sdk-audit.php`
