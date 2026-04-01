# Tag API error catalog

This catalog documents the currently shipped transport-level error codes for the public Tag shell.

## Core request validation

| code | HTTP | meaning |
|---|---:|---|
| `invalid_tenant` | 400 | missing or invalid tenant header for the routed business call |
| `validation_failed` | 400 | invalid request body, invalid entity coordinates, or malformed bulk item |

## Assignment and unassign flows

| code | HTTP | meaning |
|---|---:|---|
| `tag_not_found` | 404 | the referenced tag entity itself does not exist |
| `idempotency_conflict` | 409 | the idempotency key was reused with a different checksum |
| `assign_failed` | 500 | assign flow failed internally |
| `unassign_failed` | 500 | unassign flow failed internally |

## Important current guarantees

- `POST /tag/{id}/unassign` uses `404 tag_not_found` only when the tag entity is absent.
- If the tag exists but the entity link is already absent, unassign remains a successful `200` response with `not_found=true` in the JSON body.
- Bulk endpoints return per-item result bodies and may include `validation_failed`, `tag_not_found`, `idempotency_conflict`, `assign_failed`, or `unassign_failed` inside batch items.
- Search and suggest are read endpoints and currently rely on flat successful payloads; this catalog focuses on the shipped transport error codes explicitly stabilized in the current runtime shell.

## Source of truth

For the current shipped surface, cross-check this catalog against:

- `contracts/http/tag-openapi.yaml`
- `src/Http/Api/Tag/AssignController.php`
- `src/Http/Api/Tag/Responder/TagAssignmentResponder.php`
- `docs/api/assign.md`
