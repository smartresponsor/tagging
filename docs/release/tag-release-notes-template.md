# Release notes template — Tag

Release: `tag-vX.Y.Z`
Date: YYYY-MM-DD
Audience: Platform / Integrators / Ops

## Summary

- What changed in the minimal public-ready shell.

## Customer impact

- New capabilities:
- Behavior changes:
- Removed or quarantined legacy surface:

## Compatibility

- API contract: `contracts/http/tag-openapi.yaml`
- Runtime host: `host-minimal/`
- Demo/admin assets: `admin/`, `public/tag/examples/`
- Database migrations: list new migration files:

## Upgrade steps

1) Apply migrations.
2) Validate fixture pack.
3) Seed demo tenant if needed.
4) Run runtime smoke.
5) Run surface audit.

## Risks and rollback

- Risks:
- Rollback plan:
    - revert deploy to previous image
    - restore previous contract/examples/docs if surface changed

## Public-ready checks

- `/tag/_status` returns 200
- `/tag/_surface` matches the shipped contract
- `admin/index.html` opens and loads discovery
- `public/tag/examples/*.http` stay in sync with runtime

## Changelog

- Added:
- Changed:
- Fixed:
- Removed:
