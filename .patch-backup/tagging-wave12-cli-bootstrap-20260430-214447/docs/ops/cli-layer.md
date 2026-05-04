# CLI command layer

This layer gives the current runnable core a thin local CLI shell without introducing a framework console dependency.

## Entry point

```bash
php tools/cli/tag.php help
```

## Stable commands

- `status`
- `surface`
- `create`
- `get`
- `patch`
- `delete`
- `assign`
- `unassign`
- `assignments`
- `search`
- `suggest`

## Examples

```bash
php tools/cli/tag.php status --pretty
php tools/cli/tag.php surface --pretty
php tools/cli/tag.php search --tenant demo --q priority --pretty
php tools/cli/tag.php suggest --tenant demo --q pri --limit 5 --pretty
```

Write commands expect JSON payloads and a tenant header equivalent:

```bash
php tools/cli/tag.php create --tenant demo --json '{"name":"Alpha","slug":"alpha"}' --pretty
php tools/cli/tag.php patch --tenant demo --id TAG_ID --json '{"name":"Alpha 2"}' --pretty
php tools/cli/tag.php assign --tenant demo --tag TAG_ID --entity-type project --entity-id P1 --pretty
```

## Boundary

This CLI is only a thin adapter over the current host-minimal/bootstrap runtime. It is not the source of truth for routing or contracts.
