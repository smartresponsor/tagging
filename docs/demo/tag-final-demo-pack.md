# Tag final demo pack

This document is the compact final demo truth-pack for the current Tagging/Tag slice.

## Runnable endpoints

- `GET /tag/_status`
- `GET /tag/_surface`
- `POST /tag`
- `GET /tag/{id}`
- `PATCH /tag/{id}`
- `DELETE /tag/{id}`
- `POST /tag/assign`
- `POST /tag/unassign`
- `GET /tag/assignments`
- `GET /tag/search`
- `GET /tag/suggest`

## Truth line

This demo pack must stay aligned with:

- `docs/demo/tag-quick-demo.md`
- `docs/fixtures/demo.md`
- `docs/seed/tag-seed.md`
- current runtime `_status`
- current runtime `_surface`

## Minimal demo flow

1. Check runtime:
   - `GET /tag/_status`
2. Inspect public surface:
   - `GET /tag/_surface`
3. Seed demo data if needed.
4. Create a tag.
5. Search and suggest tags.
6. Assign and unassign a tag.
7. Verify assignments read surface.

## Notes

- This file is a release-grade documentation dependency.
- It should not promise routes or capabilities that are not present in the current runnable slice.
