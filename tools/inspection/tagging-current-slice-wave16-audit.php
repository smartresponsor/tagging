<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$errors = [];

$forbidden = [
    'config/tag_route_catalog.php',
    'src/Entity/Tag/TagLinkEntity.php',
    'src/Repository/Core/Tag/TagLinkRepository.php',
    'src/Data/Model/Tag/TagLink.php',
];

foreach ($forbidden as $path) {
    if (is_file($root . '/' . $path)) {
        $errors[] = 'legacy artifact remains: ' . $path;
    }
}

$assignment = (string) file_get_contents($root . '/src/Entity/Tag/TagAssignmentEntity.php');

foreach ([
    'private readonly string $tagId',
    'public static function create(',
    'string $tenant',
    'string $id',
    'string $tagId',
    'string $assignedType',
    'string $assignedId',
] as $signal) {
    if (!str_contains($assignment, $signal)) {
        $errors[] = 'assignment contract signal missing: ' . $signal;
    }
}

if (str_contains($assignment, 'ManyToOne')) {
    $errors[] = 'assignment entity still contains incompatible ManyToOne contract';
}

$publicSurface = (string) file_get_contents($root . '/config/tag_public_surface.php');
if (!str_contains($publicSurface, 'config/platform/routes/crud/tag.yaml')
    && !str_contains($publicSurface, 'platform/routes/crud/tag.yaml')
) {
    $errors[] = 'public surface is not projected from canonical route registry';
}

$services = '';
foreach ([
    'config/component/services.yaml',
    'config/services.yaml',
    'config/services/http.yaml',
] as $path) {
    if (is_file($root . '/' . $path)) {
        $services .= PHP_EOL . file_get_contents($root . '/' . $path);
    }
}

if (!str_contains($services, 'App\\Tagging\\Service\\Http\\Tag\\')) {
    $errors[] = 'HTTP service resource registration not found across active service configs';
}

if ([] !== $errors) {
    fwrite(STDERR, "Tagging Wave 16 current-slice audit FAILED\n");
    foreach ($errors as $error) {
        fwrite(STDERR, " - {$error}\n");
    }
    exit(1);
}

echo "Tagging Wave 16 current-slice audit PASSED\n";
