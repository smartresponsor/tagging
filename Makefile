.PHONY: up migrate migration-smoke seed clear smoke audit preflight serve symfony-serve 	lint cs-check cs-fix phpstan psalm phpunit behat verify openapi sdk antora 	release-check docker-up docker-down e2e demo-fixtures demo-seed demo-assert 	repo-map docs-sync docs-generate docs-audit package-openapi

help:
	@echo "Targets:"
	@echo "  up, migrate, migration-smoke, seed, clear, smoke, audit, preflight"
	@echo "  serve, symfony-serve, lint, cs-check, cs-fix, phpstan, psalm"
	@echo "  phpunit, behat, verify, openapi, sdk, antora, release-check"
	@echo "  docker-up, docker-down, e2e, demo-fixtures, demo-seed, demo-assert"
	@echo "  repo-map, docs-sync, docs-generate, docs-audit, package-openapi"
