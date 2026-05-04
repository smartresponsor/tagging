# Tagging Wave 19 — Canonicalization Review Report

## Scope

Wave 19 is a documentation/reporting wave. It does not rename files, move classes, delete files, or change namespaces.

`App\Tagging\...` remains the canonical component namespace.

## Current milestone status

### Namespace

- `composer.json` contains `App\\Tagging\\`: `True`
- `composer.json` contains plain `App\\ => src/` fallback: `False`

### Canonical paths checked

| Path | Present |
| --- | --- |
| `deploy/docker/compose.yaml` | `True` |
| `deploy/docker/entrypoint.sh` | `True` |
| `deploy/docker/host.Dockerfile` | `True` |
| `admin/tag-admin.js` | `True` |
| `admin/tag-admin.css` | `True` |
| `delivery/rc/tag-rc-manifest.yaml` | `True` |
| `public/tag/demo/tag-demo-requests.http` | `True` |
| `public/tag/examples/tag-http-examples.http` | `True` |
| `public/tag/examples/tag-seed-examples.http` | `True` |
| `public/tag/examples/tag-tour-examples.http` | `True` |
| `sdk/php/tag/TagClient.php` | `True` |
| `sdk/ts/tag/tag-client.ts` | `True` |
| `tools/tag-bootstrap.php` | `True` |
| `tools/cli/tag-cli.php` | `True` |
| `tools/tag-migration-smoke.sh` | `True` |
| `tools/tag-webhook-worker.php` | `True` |
| `tools/smoke/tag-smoke.sh` | `True` |
| `tools/synthetic/tag-slo.sh` | `True` |
| `tools/test-db/tag-compose.yaml` | `True` |
| `tools/audit/tag-canon-milestone-wave18-audit.php` | `True` |


### Retired paths checked

| Retired path | Present |
| --- | --- |
| `docker-compose.yml` | `False` |
| `host/Dockerfile` | `False` |
| `Tagging` | `False` |
| `admin/app.js` | `False` |
| `admin/style.css` | `False` |
| `delivery/rc/manifest.yaml` | `False` |
| `public/tag/demo/requests.http` | `False` |
| `public/tag/examples/http.http` | `False` |
| `public/tag/examples/seed.http` | `False` |
| `public/tag/examples/tour.http` | `False` |
| `sdk/php/tag/Client.php` | `False` |
| `sdk/ts/tag/client.ts` | `False` |
| `tools/_bootstrap.php` | `False` |
| `tools/cli/tag.php` | `False` |
| `tools/lint.php` | `False` |
| `tools/webhook_worker.php` | `False` |


## Audit surface

The prepared tree contains `41` Tagging audit scripts under `tools/audit/`.

Key milestone audits from Waves 10–18:

- `tools/audit/tag-tooling-surface-wave10-audit.php`
- `tools/audit/tag-audit-stabilization-wave11-audit.php`
- `tools/audit/tag-cli-bootstrap-wave12-audit.php`
- `tools/audit/tag-admin-asset-wave13-audit.php`
- `tools/audit/tag-public-example-wave14-audit.php`
- `tools/audit/tag-sdk-client-wave15-audit.php`
- `tools/audit/tag-delivery-manifest-wave16-audit.php`
- `tools/audit/tag-conventional-artifact-wave17-audit.php`
- `tools/audit/tag-canon-milestone-wave18-audit.php`

## Remaining intentionally generic artifacts

These are considered framework/static conventions and should not be renamed without a separate architectural decision:

- `admin/index.html`
- `public/tag/openapi/index.html`
- `docs/README.md`
- `docs/admin/README.md`
- `sdk/README.md`
- `migration/symfony-native-target/composer.json`
- `migration/symfony-native-target/public/index.php`
- Symfony config files under `config/**`

## Recommended local verification order

```bash
composer dump-autoload
php tools/audit/tag-canon-milestone-wave18-audit.php
php tools/audit/tag-conventional-artifact-wave17-audit.php
vendor/bin/phpunit tests/TagCanonMilestoneWave18AuditTest.php
vendor/bin/phpunit tests/TagConventionalArtifactWave17AuditTest.php
```

## Notes

This wave intentionally avoids broad cleanup. The remaining work, if any, should be driven by actual local audit/runtime failures after applying Waves 1–18.
