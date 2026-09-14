<?php

declare(strict_types=1);

namespace App\Tagging\Service\Config;

use App\Administering\Service\Config\AdministrationConfigApplyService;
use App\Administering\Service\Config\AdministrationConfigFileWriterService;
use App\Administering\ServiceInterface\Config\AdministrationConfigToolServiceInterface;
use App\Administering\Value\Config\AdministrationConfigToolDescriptor;
use App\Tagging\Form\Config\TaggingPolicyConfigFormType;
use App\Tagging\Value\Form\Config\TaggingPolicyConfigData;
use Symfony\Component\Yaml\Yaml;

final readonly class TaggingPolicyConfigService implements AdministrationConfigToolServiceInterface
{
    public function __construct(
        private string $projectDir,
        private AdministrationConfigApplyService $applyService,
        private AdministrationConfigFileWriterService $fileWriter,
    ) {}

    public function descriptor(): AdministrationConfigToolDescriptor
    {
        return new AdministrationConfigToolDescriptor(
            applicationCode: 'Tagging',
            toolCode: 'tagging.policy',
            label: 'Tagging Policy',
            description: 'Safe tag slug and normalization policy stored in tag_policy.yaml.',
            formClass: TaggingPolicyConfigFormType::class,
            serviceClass: self::class,
            requiredPermission: 'administration.config.update',
            editableFields: [
                'maxLength',
                'maxTagsPerEntity',
                'lowercaseNormalize',
                'collapseSpaces',
                'stripSymbols',
                'defaultLocale',
                'allowedLocales',
            ],
            sensitiveFields: [],
            readableFiles: ['config/tag_policy.yaml'],
            writableFiles: ['config/tag_policy.yaml'],
            metadata: [
                'section' => 'Configuration',
                'kind' => 'policy',
            ],
            secretNames: [],
            applyStrategy: 'component_yaml',
        );
    }

    public function loadData(): object
    {
        $data = new TaggingPolicyConfigData();
        $policy = $this->policyManifest();

        $data->maxLength = (string) ($policy['max_length'] ?? $data->maxLength);
        $data->maxTagsPerEntity = (string) ($policy['max_tags_per_entity'] ?? $data->maxTagsPerEntity);
        $data->lowercaseNormalize = !empty($policy['normalize']['lowercase'] ?? false) ? '1' : '0';
        $data->collapseSpaces = !empty($policy['normalize']['collapse_spaces'] ?? false) ? '1' : '0';
        $data->stripSymbols = !empty($policy['normalize']['strip_symbols'] ?? false) ? '1' : '0';
        $data->defaultLocale = (string) ($policy['i18n']['default_locale'] ?? $data->defaultLocale);
        $data->allowedLocales = implode(',', array_values(array_map('strval', is_array($policy['i18n']['allowed_locales'] ?? null) ? $policy['i18n']['allowed_locales'] : [])));

        if ('' === trim($data->allowedLocales)) {
            $data->allowedLocales = 'en-US,uk-UA,ru-RU';
        }

        return $data;
    }

    public function save(object $data, array $context = []): array
    {
        $payload = $this->assertData($data);
        $values = $this->stateRows($payload, 'pending');
        $masked = [
            'max_length' => $payload->maxLength,
            'max_tags_per_entity' => $payload->maxTagsPerEntity,
            'lowercase_normalize' => $payload->lowercaseNormalize,
            'collapse_spaces' => $payload->collapseSpaces,
            'strip_symbols' => $payload->stripSymbols,
            'default_locale' => $payload->defaultLocale,
            'allowed_locales' => $payload->allowedLocales,
        ];

        return $this->applyService->save($this->descriptor(), (string) ($context['actor'] ?? 'system'), $values, $masked, []);
    }

    public function apply(object $data, array $context = []): array
    {
        $payload = $this->assertData($data);
        $patch = $this->policyPatch($payload);
        $write = $this->fileWriter->write(
            $this->projectDir . '/../Tagging',
            'config/tag_policy.yaml',
            $patch,
            $this->descriptor()->writableFiles,
        );

        $status = 'applied' === $write['status'] ? 'applied' : 'failed';
        $values = $this->stateRows($payload, $status);

        return $this->applyService->apply(
            $this->descriptor(),
            (string) ($context['actor'] ?? 'system'),
            $values,
            $patch,
            [],
            [[
                'path' => $write['path'],
                'backup_path' => $write['backup_path'],
                'status' => $write['status'],
                'message' => $write['message'],
            ]],
            [],
            'applied' === $write['status'] ? null : $write['message'],
            $status,
        );
    }

    private function assertData(object $data): TaggingPolicyConfigData
    {
        if (!$data instanceof TaggingPolicyConfigData) {
            throw new \InvalidArgumentException('Tagging policy config expects TaggingPolicyConfigData.');
        }

        return $data;
    }

    /** @return array<string, mixed> */
    private function policyManifest(): array
    {
        $path = $this->projectDir . '/../Tagging/config/tag_policy.yaml';
        $parsed = is_file($path) ? Yaml::parseFile($path) : [];

        return is_array($parsed) ? $parsed : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function policyPatch(TaggingPolicyConfigData $data): array
    {
        $allowedLocales = array_values(array_filter(array_map('trim', explode(',', $data->allowedLocales)), static fn(string $value): bool => '' !== $value));

        return [
            'max_length' => (int) $data->maxLength,
            'max_tags_per_entity' => (int) $data->maxTagsPerEntity,
            'normalize' => [
                'lowercase' => '1' === $data->lowercaseNormalize,
                'collapse_spaces' => '1' === $data->collapseSpaces,
                'strip_symbols' => '1' === $data->stripSymbols,
            ],
            'i18n' => [
                'default_locale' => $data->defaultLocale,
                'allowed_locales' => $allowedLocales,
            ],
        ];
    }

    /**
     * @return array<string, array{fieldType:string, secret:bool, current:?string, pending:?string, masked:?string, status:string}>
     */
    private function stateRows(TaggingPolicyConfigData $data, string $status): array
    {
        return [
            'max_length' => ['fieldType' => 'integer', 'secret' => false, 'current' => $data->maxLength, 'pending' => $data->maxLength, 'masked' => null, 'status' => $status],
            'max_tags_per_entity' => ['fieldType' => 'integer', 'secret' => false, 'current' => $data->maxTagsPerEntity, 'pending' => $data->maxTagsPerEntity, 'masked' => null, 'status' => $status],
            'lowercase_normalize' => ['fieldType' => 'choice', 'secret' => false, 'current' => $data->lowercaseNormalize, 'pending' => $data->lowercaseNormalize, 'masked' => null, 'status' => $status],
            'collapse_spaces' => ['fieldType' => 'choice', 'secret' => false, 'current' => $data->collapseSpaces, 'pending' => $data->collapseSpaces, 'masked' => null, 'status' => $status],
            'strip_symbols' => ['fieldType' => 'choice', 'secret' => false, 'current' => $data->stripSymbols, 'pending' => $data->stripSymbols, 'masked' => null, 'status' => $status],
            'default_locale' => ['fieldType' => 'string', 'secret' => false, 'current' => $data->defaultLocale, 'pending' => $data->defaultLocale, 'masked' => null, 'status' => $status],
            'allowed_locales' => ['fieldType' => 'string', 'secret' => false, 'current' => $data->allowedLocales, 'pending' => $data->allowedLocales, 'masked' => null, 'status' => $status],
        ];
    }
}
