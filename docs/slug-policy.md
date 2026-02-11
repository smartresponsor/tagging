# Slug Policy (Tag)

- Base: lowercased, trim spaces, collapse whitespace to one hyphen `-`.
- Remove punctuation except `-` and `_`; transliterate common accents (é→e, ñ→n, ü→u).
- Max length: 80 chars; cut by word boundary when possible.
- Uniqueness: on conflict add `-<ulid8>` suffix.
- Locale: default `en`; custom analyzers by locale may append suffix `-<locale>` for collisions.
