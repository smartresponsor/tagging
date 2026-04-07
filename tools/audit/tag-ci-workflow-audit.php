<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$workflow = $root . '/.github/workflows/ci.yml';

if (!is_file($workflow)) {
    fwrite(STDERR, 'Missing CI workflow: .github/workflows/ci.yml
');
    exit(1);
}

$content = (string) file_get_contents($workflow);

$required = [
    'composer run -n docs:openapi:publish',
    'composer run -n audit:composer-integrity',
    'composer run -n audit:bootstrap-runtime',
    'composer run -n audit:snapshot-purity',
    'composer run -n audit:repo-map-truth',
    'composer run -n audit:demo-truth-pack',
    'composer run -n audit:release-assets',
    'composer run -n audit:release-grade-portrait',
    'composer run -n audit:openapi-semantics',
    'composer run -n audit:generated-openapi-surface',
    'composer run -n audit:antora-surface',
    'composer run -n audit:ci-workflow',
    'composer run -n test:unit',
    'composer run -n test:integration',
    'composer run -n smoke:runtime',
    'uses: actions/upload-artifact@v4',
];

foreach ($required as $needle) {
    if (!str_contains($content, $needle)) {
        fwrite(STDERR, "Missing workflow gate: {$needle}
");
        exit(1);
    }
}

if (preg_match('/^\s*-\s*run:\s*composer run -n audit:repo-hygiene\s*
\s*composer run -n audit:snapshot-purity/m', $content)) {
    fwrite(STDERR, 'Malformed workflow run block detected around repo-hygiene/snapshot-purity.
');
    exit(1);
}

echo 'CI workflow audit passed.
';
