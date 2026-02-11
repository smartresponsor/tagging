# Tag Slug Policy (RC5-E6)

- Lowercase-only, `[a-z0-9-]`, length 2..64.
- Transliteration: basic UA/RU → latin.
- Reserved words blocked (`config/tag_slug.yaml`).
- Conflict resolution: numeric suffix `-2`, `-3`, … with safe truncate to max_length.

## Examples

| source                       | slug          |
|------------------------------|---------------|
| "Зелений чай"                | zelenyi-chai  |
| "Тестовый тег"               | testovyi-teg  |
| "Admin"                      | admin-x       |
| "summer-sale" (duplicate #2) | summer-sale-2 |

Generated: 2025-10-27T20:54:00.843306
