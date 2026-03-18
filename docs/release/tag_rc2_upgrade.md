# Tag RC2 Upgrade Guide

Historical note: this document describes an earlier, broader RC track.
It is not the authoritative guide for the current minimal public-ready shell.

Use the current flow instead:
1) `php tools/db/tag-migrate.php`
2) `php tools/seed/tag-fixture-validate.php`
3) `php tools/seed/tag-seed.php`
4) `php tools/smoke/tag-smoke.php`
5) `php tools/audit/tag-surface-audit.php`
