# SDK quick usage

## TypeScript

```ts
import { TagClient } from './ts/tag/client';
const client = new TagClient('https://api.smartresponsor.local', {'X-Actor-Id':'admin'});
await client.create('Hello');
const list = await client.list('hello');
```

## PHP

```php
use SR\SDK\Tag\Client;
$client = new Client('https://api.smartresponsor.local', ['X-Actor-Id'=>'admin']);
$client->create('Hello');
$list = $client->list('hello');
```
