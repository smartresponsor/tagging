# Tag error visibility

The tagging component keeps failure handling compact, but no longer allows silent operational loss in key runtime and write paths.

## Covered paths

- `StatusController` emits `status.db_probe_failed` to an optional error sink when DB probing fails.
- `QuotaService` emits `quota.count_failed` to an optional error sink when quota counting fails.
- `AssignService` emits `tag.assign_failed` and returns `code=assign_failed` when assignment fails unexpectedly.
- `UnassignService` emits `tag.unassign_failed` and returns `code=unassign_failed` when unassignment fails unexpectedly.

## Why this exists

This component is intentionally small. It should not grow a heavy logging subsystem inside the core, but it should still make failures observable for host or application wiring.

The optional callable error sink gives the host shell a place to log or capture structured errors without forcing a framework dependency into the core services.

## Internal sink contract

Runtime-facing controllers and core tag services should report degradations through `TagErrorSink`, with `CallableTagErrorSink` kept only as a compatibility adapter for tests and lightweight host wiring.
The sink contract now lives beside the rest of the core tag service contracts under `src/Service/Core/Tag/`, instead of in the former competing service-contract tree.
