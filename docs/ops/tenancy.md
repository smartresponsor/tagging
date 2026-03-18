# Tag tenancy

The minimal runtime is DB-backed and isolates data by `tenant` columns in `tag_entity`, `tag_link`, `outbox_event`, and related tables.

The older file-based multi-tenant assignment repository has been retired from the shipped archive to avoid dual sources of truth.
