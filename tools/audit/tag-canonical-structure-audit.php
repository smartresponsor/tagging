<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$forbiddenPaths = [
    'src/Domain',
    'src/Infra',
    'src/Tag',
    'src/TagInterface',
    'src/Tagging',
    'src/TaggingInterface',
    'src/Port',
    'src/Adaptor',
    'src/Adapter',
    'src/ServiceInterface',
    'src/Service/Audit',
    'src/Service/Webhook',
    'src/Service/Metric',
    'src/Service/Security/Tag',
    'src/Service/Core/Tag/Security',
    'src/opr',
];

$violations = [];

foreach ($forbiddenPaths as $relativePath) {
    if (file_exists($root . DIRECTORY_SEPARATOR . $relativePath)) {
        $violations[] = sprintf('Forbidden path exists: %s', $relativePath);
    }
}

$directoryIterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root . DIRECTORY_SEPARATOR . 'src', FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($directoryIterator as $item) {
    if (!$item->isDir()) {
        continue;
    }

    $fullPath = str_replace('\\', '/', $item->getPathname());
    $relativePath = ltrim(substr($fullPath, strlen(str_replace('\\', '/', $root))), '/');
    $segments = explode('/', $relativePath);

    if (count($segments) >= 3 && $segments[0] === 'src' && in_array($segments[2], ['Tag', 'Tagging'], true)) {
        $violations[] = sprintf(
            'Tag/Tagging appears too early under src: %s (expected src/[Layer]/[Responsibility]/Tag/...)',
            $relativePath
        );
    }
}

sort($violations);
$violations = array_values(array_unique($violations));

if ($violations !== []) {
    fwrite(STDERR, "Canonical structure audit failed.\n");
    foreach ($violations as $violation) {
        fwrite(STDERR, ' - ' . $violation . "\n");
    }
    exit(1);
}

fwrite(STDOUT, "Canonical structure audit passed.\n");
