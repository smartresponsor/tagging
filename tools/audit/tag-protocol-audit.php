<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$forbidden = [
    'src/Tag',
    'src/TagInterface',
    'src/Tagging',
    'src/TaggingInterface',
    'src/Infra',
    'src/opr',
    'src/Port',
    'src/Adaptor',
    'src/Adapter',
    'test/Tag',
    'test/Tagging',
    'tests/Tag',
    'tests/Tagging',
    'tag.yaml',
    'tag_assignment.yaml',
    'tag_quota.yaml',
    'tag_cons_patched',
    'tag_fix',
    'tmp',
];

$violations = [];
foreach ($forbidden as $path) {
    if (file_exists($root . DIRECTORY_SEPARATOR . $path)) {
        $violations[] = $path;
    }
}

$rii = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($rii as $item) {
    $path = str_replace('\\', '/', substr($item->getPathname(), strlen($root) + 1));
    if ($path === false || $path === '') {
        continue;
    }

    if ($item->isDir()) {
        if (preg_match('#^src/[^/]+/Tag(?:/|$)#', $path)) {
            $violations[] = $path;
        }
        if (preg_match('#^src/[^/]+/Tagging(?:/|$)#', $path)) {
            $violations[] = $path;
        }
        if (preg_match('#^tests?/[^/]+/Tag/.+#', $path)) {
            $violations[] = $path;
        }
        if (preg_match('#^tests?/[^/]+/Tagging/.+#', $path)) {
            $violations[] = $path;
        }
    } else {
        if (preg_match('#^tests?/Tag(?:/|$)#', $path)) {
            $violations[] = $path;
        }
        if (preg_match('#^tests?/Tagging(?:/|$)#', $path)) {
            $violations[] = $path;
        }
        if (preg_match('#^tests?/[^/]+/Tag/.+#', $path)) {
            $violations[] = $path;
        }
        if (preg_match('#^tests?/[^/]+/Tagging/.+#', $path)) {
            $violations[] = $path;
        }
    }
}

$violations = array_values(array_unique($violations));
sort($violations);

if ($violations !== []) {
    fwrite(STDERR, "Tagging/Tag protocol violations detected:
- " . implode("
- ", $violations) . "
");
    exit(1);
}

echo "Tagging/Tag protocol audit passed.
";
