<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$failures = [];

$canonical = $root . '/config/platform/routes/crud/tag.yaml';
if (!is_file($canonical)) {
    $failures[] = 'canonical route registry is missing';
} else {
    $content = (string) file_get_contents($canonical);

    $requiredKeys = [
        'tag.index:',
        'tag.create:',
        'tag.assignment.index:',
        'tag.search:',
        'tag.relation.index:',
        'tag.synonym.index:',
        'tag.proposal.create:',
        'tag.status:',
        'tag.webhook.index:',
    ];

    foreach ($requiredKeys as $key) {
        if (!str_contains($content, $key)) {
            $failures[] = 'missing route key: ' . $key;
        }
    }

    if (str_contains($content, 'Controller') || str_contains($content, 'HttpService')) {
        $failures[] = 'legacy controller/HttpService vocabulary in canonical registry';
    }

    preg_match_all('/^([a-z0-9_.]+):/m', $content, $matches);
    $keys = $matches[1] ?? [];
    if (count($keys) !== count(array_unique($keys))) {
        $failures[] = 'duplicate route keys detected';
    }
}

$forbidden = [
    'config/platform/routes/crud/tag-adjacent.yaml',
    'config/routes/tagging_native.yaml',
    'tag.yaml',
    'config/tag_route_catalog.php',
    'config/tag_public_route_paths.php',
    'tools/audit/tag-route-controller-audit.php',
];

foreach ($forbidden as $path) {
    if (is_file($root . '/' . $path)) {
        $failures[] = 'legacy route source remains: ' . $path;
    }
}

if ([] !== $failures) {
    fwrite(STDERR, "Tagging route/config truth audit FAILED\n");
    foreach ($failures as $failure) {
        fwrite(STDERR, " - {$failure}\n");
    }
    exit(1);
}

echo "Tagging route/config truth audit PASSED\n";
echo "Source of truth: config/platform/routes/crud/tag.yaml\n";
