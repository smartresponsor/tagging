<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$routeFile = $root . '/config/platform/routes/crud/tag.yaml';
$formRoot = $root . '/src/Form/Tag';
$failures = [];

if (!is_file($routeFile)) {
    $failures[] = 'canonical route registry missing';
} else {
    $content = (string) file_get_contents($routeFile);
    preg_match_all('/type:\s+(App\\\\Tagging\\\\Form\\\\Tag\\\\([A-Za-z0-9_]+))/', $content, $matches);

    foreach ($matches[2] ?? [] as $class) {
        if (!is_file($formRoot . '/' . $class . '.php')) {
            $failures[] = 'route references missing form type: ' . $class;
        }
    }

    $requiredTypedRoutes = [
        'tag.create',
        'tag.update_id',
        'tag.delete_id',
        'tag.bulk',
        'tag.import',
        'tag.export',
        'tag.archive_id',
        'tag.restore_id',
        'tag.duplicate_id',
        'tag.assignment.assign',
        'tag.assignment.unassign',
        'tag.relation.create',
        'tag.synonym.create',
        'tag.proposal.create',
        'tag.proposal.approve_id',
        'tag.webhook.create',
    ];

    foreach ($requiredTypedRoutes as $route) {
        if (!preg_match('/^' . preg_quote($route, '/') . ':\s+\{[^\n]*\btype:/m', $content)) {
            $failures[] = 'write route has no Symfony form type: ' . $route;
        }
    }

    $mustRemainUntyped = [
        'tag.index',
        'tag.show_id',
        'tag.show_slug',
        'tag.status',
        'tag.metrics',
        'tag.webhook.index',
    ];

    foreach ($mustRemainUntyped as $route) {
        if (preg_match('/^' . preg_quote($route, '/') . ':\s+\{[^\n]*\btype:/m', $content)) {
            $failures[] = 'pure output route should not own a form type: ' . $route;
        }
    }
}

$composer = json_decode((string) file_get_contents($root . '/composer.json'), true);
foreach (['symfony/form', 'symfony/validator', 'symfony/options-resolver'] as $package) {
    if (!isset($composer['require'][$package])) {
        $failures[] = 'composer requirement missing: ' . $package;
    }
}

if ([] !== $failures) {
    fwrite(STDERR, "Tagging Symfony Form convergence audit FAILED\n");
    foreach ($failures as $failure) {
        fwrite(STDERR, " - {$failure}\n");
    }
    exit(1);
}

echo "Tagging Symfony Form convergence audit PASSED\n";
echo "Registry/type ownership is aligned with endpoint responsibility.\n";
