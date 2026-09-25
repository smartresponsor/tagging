<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$moves = [
    'src/Application/Write/Tag/Dto/TagCreateCommand.php' => 'src/Command/Input/TagCreateCommand.php',
    'src/Application/Write/Tag/Dto/TagDeleteCommand.php' => 'src/Command/Input/TagDeleteCommand.php',
    'src/Application/Write/Tag/Dto/TagPatchCommand.php' => 'src/Command/Input/TagPatchCommand.php',
    'src/Application/Write/Tag/Dto/TagError.php' => 'src/Enum/TagError.php',
    'src/Application/Write/Tag/Dto/TagResultDTO.php' => 'src/DTO/Write/TagResultDTODTO.php',
    'src/Application/Write/Tag/UseCase/TagCreateHandler.php' => 'src/Handler/Write/TagCreateHandler.php',
    'src/Application/Write/Tag/UseCase/TagCreateHandlerInterface.php' => 'src/HandlerInterface/Write/TagCreateHandlerInterface.php',
    'src/Application/Write/Tag/UseCase/TagDeleteHandler.php' => 'src/Handler/Write/TagDeleteHandler.php',
    'src/Application/Write/Tag/UseCase/TagDeleteHandlerInterface.php' => 'src/HandlerInterface/Write/TagDeleteHandlerInterface.php',
    'src/Application/Write/Tag/UseCase/TagPatchHandler.php' => 'src/Handler/Write/TagPatchHandler.php',
    'src/Application/Write/Tag/UseCase/TagPatchHandlerInterface.php' => 'src/HandlerInterface/Write/TagPatchHandlerInterface.php',
    'src/Infrastructure/Config/TagRuntimeConfig.php' => 'src/DTO/Config/TagRuntimeConfigDTO.php',
    'src/Infrastructure/Config/TagRuntimeConfigFactory.php' => 'src/Factory/Config/TagRuntimeConfigFactory.php',
    'src/Infrastructure/Outbox/Tag/TagOutboxPublisher.php' => 'src/Publisher/Outbox/TagOutboxPublisher.php',
    'src/Infrastructure/Persistence/Tag/TagDoctrineRepository.php' => 'src/Repository/Storage/TagDoctrineRepository.php',
    'src/Infrastructure/Persistence/Tag/TagInMemoryRepository.php' => 'src/Repository/Storage/TagInMemoryRepository.php',
    'src/Infrastructure/ReadModel/Tag/TagReadModel.php' => 'src/Reader/Model/TagReadModel.php',
    'src/Http/Api/Tag/Middleware/TagAuthorizeMiddleware.php' => 'src/Middleware/Api/TagAuthorizeMiddleware.php',
    'src/Http/Api/Tag/Middleware/TagMiddlewarePipeline.php' => 'src/Middleware/Api/TagMiddlewarePipeline.php',
    'src/Http/Api/Tag/Middleware/TagObserveMiddleware.php' => 'src/Middleware/Api/TagObserveMiddleware.php',
    'src/Http/Api/Tag/Middleware/TagQuotaGateMiddleware.php' => 'src/Middleware/Api/TagQuotaGateMiddleware.php',
    'src/Http/Api/Tag/Middleware/TagTenantContextMiddleware.php' => 'src/Middleware/Api/TagTenantContextMiddleware.php',
    'src/Http/Api/Tag/Middleware/TagVerifySignatureMiddleware.php' => 'src/Middleware/Api/TagVerifySignatureMiddleware.php',
    'src/Http/Middleware/TagIdempotencyMiddleware.php' => 'src/Middleware/Api/TagIdempotencyMiddleware.php',
    'src/Http/Api/Tag/Responder/TagAssignmentResponder.php' => 'src/Responder/Api/TagAssignmentResponder.php',
    'src/Http/Api/Tag/Responder/TagJsonResponder.php' => 'src/Responder/Api/TagJsonResponder.php',
    'src/Http/Api/Tag/Responder/TagMiddlewareResponder.php' => 'src/Responder/Api/TagMiddlewareResponder.php',
    'src/Http/Api/Tag/Responder/TagReadResponder.php' => 'src/Responder/Api/TagReadResponder.php',
    'src/Http/Api/Tag/Responder/TagWebhookResponder.php' => 'src/Responder/Api/TagWebhookResponder.php',
    'src/Http/Api/Tag/Responder/TagWriteResponder.php' => 'src/Responder/Api/TagWriteResponder.php',
    'src/Http/Api/Tag/TagCorsHeaders.php' => 'src/Provider/Http/TagCorsHeaders.php',
    'src/Http/Api/Tag/TagRuntimeSurfaceCatalog.php' => 'src/Provider/Runtime/TagRuntimeSurfaceCatalog.php',
    'src/Http/Api/Tag/TagRuntimeVersion.php' => 'src/Provider/Runtime/TagRuntimeVersion.php',
    'src/Entity/Projection/Tag/TagAdminViewEntity.php' => 'src/Entity/Tag/TagAdminViewEntity.php',
    'src/Entity/Projection/Tag/TagAssignmentEffectEntity.php' => 'src/Entity/Tag/TagAssignmentEffectEntity.php',
    'src/EntityInterface/Tag/TagInterface.php' => 'src/EntityInterface/Contract/TagInterface.php',
    'src/EntityInterface/Tag/TagProductInterface.php' => 'src/EntityInterface/Contract/TagProductInterface.php',
    'src/EntityInterface/Tag/TagProjectInterface.php' => 'src/EntityInterface/Contract/TagProjectInterface.php',
];

foreach (glob($root . '/src/Command/Tag/*.php') ?: [] as $source) {
    $name = basename($source);
    $targetName = 'TagAbstractCommand.php' === $name ? 'TagAbstractCommand.php' : $name;
    $moves['src/Command/Tag/' . $name] = 'src/Command/Console/' . $targetName;
}

foreach (glob($root . '/src/Form/Tag/*.php') ?: [] as $source) {
    $name = basename($source);
    $targetName = 'TagAbstractType.php' === $name ? 'TagAbstractType.php' : $name;
    $moves['src/Form/Tag/' . $name] = 'src/Form/Type/' . $targetName;
}

foreach ($moves as $from => $to) {
    $source = $root . '/' . $from;
    $target = $root . '/' . $to;
    if (!is_file($source)) {
        continue;
    }
    if (is_file($target)) {
        throw new RuntimeException('Refusing to overwrite ' . $to);
    }
    if (!is_dir(dirname($target)) && !mkdir(dirname($target), 0777, true) && !is_dir(dirname($target))) {
        throw new RuntimeException('Cannot create ' . dirname($target));
    }
    if (!rename($source, $target)) {
        throw new RuntimeException('Cannot move ' . $from . ' to ' . $to);
    }
}

foreach ([
    'src/Data/Model/Tag/TagAssignmentRecord.php',
    'src/Data/Model/Tag/TagEntity.php',
    'src/Http/Api/Tag/TagHttpRequest.php',
] as $obsolete) {
    $path = $root . '/' . $obsolete;
    if (is_file($path) && !unlink($path)) {
        throw new RuntimeException('Cannot remove obsolete file ' . $obsolete);
    }
}

$replacements = [
    'App\\Tagging\\Application\\Write\\Tag\\Dto\\TagCreateCommand' => 'App\\Tagging\\Command\\Input\\TagCreateCommand',
    'App\\Tagging\\Application\\Write\\Tag\\Dto\\TagDeleteCommand' => 'App\\Tagging\\Command\\Input\\TagDeleteCommand',
    'App\\Tagging\\Application\\Write\\Tag\\Dto\\TagPatchCommand' => 'App\\Tagging\\Command\\Input\\TagPatchCommand',
    'App\\Tagging\\Application\\Write\\Tag\\Dto\\TagError' => 'App\\Tagging\\Enum\\TagError',
    'App\\Tagging\\Application\\Write\\Tag\\Dto\\TagResultDTO' => 'App\\Tagging\\DTO\\Write\\TagResultDTODTO',
    'App\\Tagging\\Application\\Write\\Tag\\UseCase\\TagCreateHandlerInterface' => 'App\\Tagging\\HandlerInterface\\Write\\TagCreateHandlerInterface',
    'App\\Tagging\\Application\\Write\\Tag\\UseCase\\TagCreateHandler' => 'App\\Tagging\\Handler\\Write\\TagCreateHandler',
    'App\\Tagging\\Application\\Write\\Tag\\UseCase\\TagDeleteHandlerInterface' => 'App\\Tagging\\HandlerInterface\\Write\\TagDeleteHandlerInterface',
    'App\\Tagging\\Application\\Write\\Tag\\UseCase\\TagDeleteHandler' => 'App\\Tagging\\Handler\\Write\\TagDeleteHandler',
    'App\\Tagging\\Application\\Write\\Tag\\UseCase\\TagPatchHandlerInterface' => 'App\\Tagging\\HandlerInterface\\Write\\TagPatchHandlerInterface',
    'App\\Tagging\\Application\\Write\\Tag\\UseCase\\TagPatchHandler' => 'App\\Tagging\\Handler\\Write\\TagPatchHandler',
    'App\\Tagging\\Infrastructure\\Config\\TagRuntimeConfigFactory' => 'App\\Tagging\\Factory\\Config\\TagRuntimeConfigFactory',
    'App\\Tagging\\Infrastructure\\Config\\TagRuntimeConfig' => 'App\\Tagging\\DTO\\Config\\TagRuntimeConfigDTO',
    'App\\Tagging\\Infrastructure\\Outbox\\Tag\\TagOutboxPublisher' => 'App\\Tagging\\Publisher\\Outbox\\TagOutboxPublisher',
    'App\\Tagging\\Infrastructure\\Persistence\\Tag\\TagDoctrineRepository' => 'App\\Tagging\\Repository\\Storage\\TagDoctrineRepository',
    'App\\Tagging\\Infrastructure\\Persistence\\Tag\\TagInMemoryRepository' => 'App\\Tagging\\Repository\\Storage\\TagInMemoryRepository',
    'App\\Tagging\\Infrastructure\\ReadModel\\Tag\\TagReadModel' => 'App\\Tagging\\Reader\\Model\\TagReadModel',
    'App\\Tagging\\Http\\Api\\Tag\\Middleware' => 'App\\Tagging\\Middleware\\Api',
    'App\\Tagging\\Http\\Middleware\\TagIdempotencyMiddleware' => 'App\\Tagging\\Middleware\\Api\\TagIdempotencyMiddleware',
    'App\\Tagging\\Http\\Api\\Tag\\Responder' => 'App\\Tagging\\Responder\\Api',
    'App\\Tagging\\Http\\Api\\Tag\\TagCorsHeaders' => 'App\\Tagging\\Provider\\Http\\TagCorsHeaders',
    'App\\Tagging\\Http\\Api\\Tag\\TagRuntimeSurfaceCatalog' => 'App\\Tagging\\Provider\\Runtime\\TagRuntimeSurfaceCatalog',
    'App\\Tagging\\Http\\Api\\Tag\\TagRuntimeVersion' => 'App\\Tagging\\Provider\\Runtime\\TagRuntimeVersion',
    'App\\Tagging\\Entity\\Projection\\Tag\\TagAdminViewEntity' => 'App\\Tagging\\Entity\\Tag\\TagAdminViewEntity',
    'App\\Tagging\\Entity\\Projection\\Tag\\TagAssignmentEffectEntity' => 'App\\Tagging\\Entity\\Tag\\TagAssignmentEffectEntity',
    'App\\Tagging\\EntityInterface\\Tag\\TagProductInterface' => 'App\\Tagging\\EntityInterface\\Contract\\TagProductInterface',
    'App\\Tagging\\EntityInterface\\Tag\\TagProjectInterface' => 'App\\Tagging\\EntityInterface\\Contract\\TagProjectInterface',
    'App\\Tagging\\EntityInterface\\Tag\\TagInterface' => 'App\\Tagging\\EntityInterface\\Contract\\TagInterface',
    'App\\Tagging\\Command\\Tag' => 'App\\Tagging\\Command\\Console',
    'App\\Tagging\\Form\\Tag' => 'App\\Tagging\\Form\\Type',
];

$classReplacements = [
    'TagCreateHandlerInterface' => 'TagCreateHandlerInterface',
    'TagCreateHandler' => 'TagCreateHandler',
    'TagDeleteHandlerInterface' => 'TagDeleteHandlerInterface',
    'TagDeleteHandler' => 'TagDeleteHandler',
    'TagPatchHandlerInterface' => 'TagPatchHandlerInterface',
    'TagPatchHandler' => 'TagPatchHandler',
    'TagResultDTO' => 'TagResultDTODTO',
    'TagAdminViewEntity' => 'TagAdminViewEntity',
    'TagAssignmentEffectEntity' => 'TagAssignmentEffectEntity',
    'TagProductInterface' => 'TagProductInterface',
    'TagProjectInterface' => 'TagProjectInterface',
    'TagAbstractCommand' => 'TagAbstractCommand',
    'TagAbstractType' => 'TagAbstractType',
];

$roots = ['src', 'config', 'tests', 'tools', 'docs'];
$extensions = ['php', 'yaml', 'yml', 'xml', 'neon', 'md', 'adoc', 'json'];
foreach ($roots as $scanRoot) {
    $directory = $root . '/' . $scanRoot;
    if (!is_dir($directory)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile() || !in_array(strtolower($file->getExtension()), $extensions, true)) {
            continue;
        }
        $contents = file_get_contents($file->getPathname());
        if (false === $contents) {
            throw new RuntimeException('Cannot read ' . $file->getPathname());
        }
        $updated = strtr($contents, $replacements);
        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
        if (str_starts_with($relative, 'src/') || str_starts_with($relative, 'tests/') || str_starts_with($relative, 'config/') || str_starts_with($relative, 'tools/')) {
            $updated = strtr($updated, $classReplacements);
        }
        if ($updated !== $contents && false === file_put_contents($file->getPathname(), $updated)) {
            throw new RuntimeException('Cannot write ' . $file->getPathname());
        }
    }
}

$namespaceReplacements = [
    'src/Command/Input/TagCreateCommand.php' => ['namespace App\\Tagging\\Application\\Write\\Tag\\Dto;' => 'namespace App\\Tagging\\Command\\Input;'],
    'src/Command/Input/TagDeleteCommand.php' => ['namespace App\\Tagging\\Application\\Write\\Tag\\Dto;' => 'namespace App\\Tagging\\Command\\Input;'],
    'src/Command/Input/TagPatchCommand.php' => ['namespace App\\Tagging\\Application\\Write\\Tag\\Dto;' => 'namespace App\\Tagging\\Command\\Input;'],
    'src/Enum/TagError.php' => ['namespace App\\Tagging\\Application\\Write\\Tag\\Dto;' => 'namespace App\\Tagging\\Enum;'],
    'src/DTO/Write/TagResultDTODTO.php' => ['namespace App\\Tagging\\Application\\Write\\Tag\\Dto;' => 'namespace App\\Tagging\\DTO\\Write;'],
    'src/Handler/Write/TagCreateHandler.php' => ['namespace App\\Tagging\\Application\\Write\\Tag\\UseCase;' => 'namespace App\\Tagging\\Handler\\Write;'],
    'src/Handler/Write/TagDeleteHandler.php' => ['namespace App\\Tagging\\Application\\Write\\Tag\\UseCase;' => 'namespace App\\Tagging\\Handler\\Write;'],
    'src/Handler/Write/TagPatchHandler.php' => ['namespace App\\Tagging\\Application\\Write\\Tag\\UseCase;' => 'namespace App\\Tagging\\Handler\\Write;'],
    'src/HandlerInterface/Write/TagCreateHandlerInterface.php' => ['namespace App\\Tagging\\Application\\Write\\Tag\\UseCase;' => 'namespace App\\Tagging\\HandlerInterface\\Write;'],
    'src/HandlerInterface/Write/TagDeleteHandlerInterface.php' => ['namespace App\\Tagging\\Application\\Write\\Tag\\UseCase;' => 'namespace App\\Tagging\\HandlerInterface\\Write;'],
    'src/HandlerInterface/Write/TagPatchHandlerInterface.php' => ['namespace App\\Tagging\\Application\\Write\\Tag\\UseCase;' => 'namespace App\\Tagging\\HandlerInterface\\Write;'],
    'src/DTO/Config/TagRuntimeConfigDTO.php' => ['namespace App\\Tagging\\Infrastructure\\Config;' => 'namespace App\\Tagging\\DTO\\Config;'],
    'src/Factory/Config/TagRuntimeConfigFactory.php' => ['namespace App\\Tagging\\Infrastructure\\Config;' => 'namespace App\\Tagging\\Factory\\Config;'],
    'src/Publisher/Outbox/TagOutboxPublisher.php' => ['namespace App\\Tagging\\Infrastructure\\Outbox\\Tag;' => 'namespace App\\Tagging\\Publisher\\Outbox;'],
    'src/Repository/Storage/TagDoctrineRepository.php' => ['namespace App\\Tagging\\Infrastructure\\Persistence\\Tag;' => 'namespace App\\Tagging\\Repository\\Storage;'],
    'src/Repository/Storage/TagInMemoryRepository.php' => ['namespace App\\Tagging\\Infrastructure\\Persistence\\Tag;' => 'namespace App\\Tagging\\Repository\\Storage;'],
    'src/Reader/Model/TagReadModel.php' => ['namespace App\\Tagging\\Infrastructure\\ReadModel\\Tag;' => 'namespace App\\Tagging\\Reader\\Model;'],
    'src/Provider/Http/TagCorsHeaders.php' => ['namespace App\\Tagging\\Http\\Api\\Tag;' => 'namespace App\\Tagging\\Provider\\Http;'],
    'src/Provider/Runtime/TagRuntimeSurfaceCatalog.php' => ['namespace App\\Tagging\\Http\\Api\\Tag;' => 'namespace App\\Tagging\\Provider\\Runtime;'],
    'src/Provider/Runtime/TagRuntimeVersion.php' => ['namespace App\\Tagging\\Http\\Api\\Tag;' => 'namespace App\\Tagging\\Provider\\Runtime;'],
    'src/Entity/Tag/TagAdminViewEntity.php' => ['namespace App\\Tagging\\Entity\\Projection\\Tag;' => 'namespace App\\Tagging\\Entity\\Tag;'],
    'src/Entity/Tag/TagAssignmentEffectEntity.php' => ['namespace App\\Tagging\\Entity\\Projection\\Tag;' => 'namespace App\\Tagging\\Entity\\Tag;'],
    'src/EntityInterface/Contract/TagInterface.php' => ['namespace App\\Tagging\\EntityInterface\\Tag;' => 'namespace App\\Tagging\\EntityInterface\\Contract;'],
    'src/EntityInterface/Contract/TagProductInterface.php' => ['namespace App\\Tagging\\EntityInterface\\Tag;' => 'namespace App\\Tagging\\EntityInterface\\Contract;'],
    'src/EntityInterface/Contract/TagProjectInterface.php' => ['namespace App\\Tagging\\EntityInterface\\Tag;' => 'namespace App\\Tagging\\EntityInterface\\Contract;'],
];

foreach ($namespaceReplacements as $relative => $pairs) {
    $path = $root . '/' . $relative;
    $contents = file_get_contents($path);
    if (false === $contents) {
        throw new RuntimeException('Cannot read moved file ' . $relative);
    }
    $updated = strtr($contents, $pairs);
    if ($updated !== $contents && false === file_put_contents($path, $updated)) {
        throw new RuntimeException('Cannot update moved file ' . $relative);
    }
}

$runtimeConfigPath = $root . '/src/DTO/Config/TagRuntimeConfigDTO.php';
if (is_file($runtimeConfigPath)) {
    $contents = file_get_contents($runtimeConfigPath);
    $contents = str_replace('class TagRuntimeConfig', 'class TagRuntimeConfigDTO', (string) $contents);
    file_put_contents($runtimeConfigPath, $contents);
}
$runtimeFactoryPath = $root . '/src/Factory/Config/TagRuntimeConfigFactory.php';
if (is_file($runtimeFactoryPath)) {
    $contents = file_get_contents($runtimeFactoryPath);
    $contents = str_replace('TagRuntimeConfig', 'TagRuntimeConfigDTO', (string) $contents);
    if (!str_contains((string) $contents, 'use App\\Tagging\\DTO\\Config\\TagRuntimeConfigDTO;')) {
        $contents = str_replace('namespace App\\Tagging\\Factory\\Config;\n', "namespace App\\Tagging\\Factory\\Config;\n\nuse App\\Tagging\\DTO\\Config\\TagRuntimeConfigDTO;\n", (string) $contents);
    }
    file_put_contents($runtimeFactoryPath, $contents);
}

fwrite(STDOUT, "Tagging role-first migration applied.\n");
