<?php

declare(strict_types=1);

$repoRoot = dirname(__DIR__, 2);
$composerPath = $repoRoot . '/composer.json';
if (!is_file($composerPath)) {
    fwrite(STDERR, "Missing composer.json\n");
    exit(1);
}
$composer = (string) file_get_contents($composerPath);
if (!str_contains($composer, 'App\\\\Tagging\\\\')) {
    fwrite(STDERR, "composer.json must keep App\\Tagging\\ as the component namespace.\n");
    exit(1);
}
if (str_contains($composer, '"App\\\\": "src/"')) {
    fwrite(STDERR, "composer.json must not collapse Tagging to plain App\\ namespace.\n");
    exit(1);
}
$requiredFiles = [
    'tools/audit/tag-post-canon-verification-wave20.php',
    'tools/test/tag-post-canon-tests-wave21.php',
    'tools/test/tag-post-canon-all-wave22.php',
    'tools/test/tag-post-canon-all-wave23.ps1',
    'tools/test/tag-post-canon-all-wave23.sh',
    'tests/TagPostCanonVerificationWave20Test.php',
    'tests/TagPostCanonTestRunnerWave21Test.php',
    'tests/TagPostCanonAllRunnerWave22Test.php',
    'tests/TagPostCanonCommandWrapperWave23Test.php',
];
$violations = [];
foreach ($requiredFiles as $relativePath) {
    if (!is_file($repoRoot . '/' . $relativePath)) {
        $violations[] = 'required post-canon verification file missing: ' . $relativePath;
    }
}
$namespaceGuardFiles = [
    'tools/audit/tag-post-canon-verification-wave20.php',
    'tools/test/tag-post-canon-tests-wave21.php',
    'tools/test/tag-post-canon-all-wave22.php',
    'tools/test/tag-post-canon-all-wave23.ps1',
    'tools/test/tag-post-canon-all-wave23.sh',
];
foreach ($namespaceGuardFiles as $relativePath) {
    $absolutePath = $repoRoot . '/' . $relativePath;
    if (!is_file($absolutePath)) {
        continue;
    }
    $contents = (string) file_get_contents($absolutePath);
    if (!str_contains($contents, 'App\\Tagging\\') && !str_contains($contents, 'App\\\\Tagging\\\\')) {
        $violations[] = 'post-canon runner lacks App\\Tagging namespace guard: ' . $relativePath;
    }
}
if ($violations !== []) {
    fwrite(STDERR, "Tagging post-canon health check failed:\n");
    foreach ($violations as $violation) {
        fwrite(STDERR, ' - ' . $violation . "\n");
    }
    exit(1);
}
echo "Tagging post-canon health check passed.\n";
