# Symfony-native read wiring

The shipped tagging runtime is now the Symfony public entry point:

```bash
php -S 127.0.0.1:8080 -t public public/index.php
```

Routes are loaded through `config/routes.yaml`, which imports `config/routes/tagging_native.yaml`.
The search and suggest endpoints are part of that Symfony route map:

- `GET /tag/search`
- `GET /tag/suggest`

The controllers are resolved through Symfony service maps under `config/services/*.yaml`.
The HTTP layer registers `App\Tagging\Http\Api\Tag\`, responders, and middleware from `config/services/http.yaml`.
The read-model layer is registered from `config/services/read_model.yaml` and keeps search and suggest on the shared tagging read model.

## Operational check

Use the local Symfony-native server script for development:

```bash
bash tools/local/tag-serve.sh
```

Then verify the public surface:

```bash
curl -fsS http://127.0.0.1:8080/tag/_surface
curl -fsS 'http://127.0.0.1:8080/tag/search?q=demo&pageSize=10'
curl -fsS 'http://127.0.0.1:8080/tag/suggest?q=dem&limit=10'
```

The runtime truth is `symfony-native`; route, controller, and service-map truth should not be duplicated in retired bootstrap files.
