# Tagging zero-controller and Cruding canon

- `TagEntity` is the root persistent entity.
- Reusable system fields are supplied by `Objecting`; Tagging must not recreate identity, audit, title, or lifecycle packs locally.
- HTTP entrypoints are `App\Tagging\Service\Http\Tag\Tag*Service` services.
- `src/Http/Api/Tag` and the aggregate `TagIndexService`/`*HttpService` transport generation are retired.
- CRUD entrypoints delegate to application use cases and query contracts; entrypoints do not own persistence logic.
- Cruding route keys retain the full business chain and use explicit `id`/`slug` variants.
- Polymorphic assignment remains `assigned_type + assigned_id`; no Doctrine relation to external component entities.
