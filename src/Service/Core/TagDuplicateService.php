<?php

declare(strict_types=1);

namespace App\Tagging\Service\Core;

use App\Tagging\Application\Write\Tag\Dto\TagCreateCommand;
use App\Tagging\Application\Write\Tag\UseCase\TagCreateUseCaseInterface;

final readonly class TagDuplicateService implements TagDuplicateServiceInterface
{
    public function __construct(private TagEntityQueryServiceInterface $query, private TagCreateUseCaseInterface $create) {}
    public function duplicate(string $tenant, ?string $id, ?string $slug, array $override = []): array
    {
        $source = null !== $id ? $this->query->findById($tenant, $id) : $this->query->findBySlug($tenant, (string) $slug);
        if (null === $source) {
            throw new \RuntimeException('not_found');
        }
        $payload = array_replace($source, $override);
        unset($payload['id'],$payload['created_at'],$payload['updated_at']);
        $payload['slug'] = $override['slug'] ?? ($source['slug'] . '-copy');
        $payload['nameEntity'] = $override['nameEntity'] ?? (($source['nameEntity'] ?? $source['label'] ?? 'Tag') . ' copy');
        $result = $this->create->execute(new TagCreateCommand($tenant, $payload));
        if (!$result->ok) {
            throw new \RuntimeException($result->error?->value ?? 'duplicate_failed');
        }
        return $result->payload;
    }
}
