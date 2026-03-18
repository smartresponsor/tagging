# CI gates and evidence

This repository uses CI as a canonical guardrail, not only as a test runner.

## Mandatory gates

- `composer validate --strict`
- `composer install`
- `composer run -n lint`
- `composer run -n lint:admin`
- `composer run -n audit:surface`
- `composer run -n audit:contract`
- `composer run -n audit:route`
- `composer run -n audit:bootstrap`
- `composer run -n audit:bootstrap-runtime`
- `composer run -n audit:config`
- `composer run -n audit:sdk`
- `composer run -n audit:version`
- `composer run -n audit:canonical-stale`
- `composer run -n audit:canonical-structure`

## Canonical structure intent

The CI must fail if any of the following reappear:

- `src/Domain/...`
- `src/Infra/...`
- `src/Tag/...`
- `src/TagInterface/...`
- `src/Tagging/...`
- `src/TaggingInterface/...`
- `src/Port/...`
- `src/Adaptor/...`
- `src/Adapter/...`
- `src/opr`
- `src/[Layer]/Tag/...` when `Tag` appears too early under `src/`

## Evidence on failure

`runtime-smoke` uploads the host-minimal runtime log when the job fails so that bootstrap/runtime regressions leave inspectable evidence.


## Workflow validity

CI workflow YAML must keep each gate as its own `- run:` step. In particular, `audit:repo-hygiene` and `audit:snapshot-purity` must remain separate steps, and the workflow must call `audit:ci-workflow` as a self-check gate.
