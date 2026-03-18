# SDK quick usage

The shipped SDK surface matches the runnable `host-minimal` public API only:

- `status()`
- `surface()`
- `create()` / `get()` / `patch()` / `delete()`
- `assign()` / `unassign()` / `assignments()`
- `search()` / `suggest()`

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
