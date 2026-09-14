<?php

declare(strict_types=1);

namespace App\Tagging\Entity\Tag;

use App\Objecting\Embeddable\ObjectIdentityEmbeddable;
use App\Objecting\EntityInterface\ObjectAuditedInterface;
use App\Objecting\EntityInterface\ObjectSoftDeletableInterface;
use App\Objecting\EntityInterface\ObjectStatefulInterface;
use App\Objecting\EntityInterface\ObjectTitledInterface;
use App\Objecting\EntityTrait\Embeddable\ObjectAuditEmbeddableTrait;
use App\Objecting\EntityTrait\Embeddable\ObjectSoftDeleteEmbeddableTrait;
use App\Objecting\EntityTrait\Embeddable\ObjectStateEmbeddableTrait;
use App\Objecting\EntityTrait\Embeddable\ObjectTitleEmbeddableTrait;
use App\Tagging\Repository\Core\Tag\TagRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Table(name: 'tag')]
#[ORM\Index(name: 'tag_tenant_created_idx', columns: ['tenant', 'created_at'])]
#[ORM\Index(name: 'tag_tenant_weight_idx', columns: ['tenant', 'weight'])]
#[ORM\UniqueConstraint(name: 'tag_slug_uq', columns: ['tenant', 'slug'])]
#[ORM\UniqueConstraint(name: 'tag_object_uuid_uq', columns: ['uuid'])]
#[ORM\Index(name: 'tag_object_slug_idx', columns: ['slug'])]
final class TagEntity implements
    ObjectAuditedInterface,
    ObjectSoftDeletableInterface,
    ObjectStatefulInterface,
    ObjectTitledInterface
{
    use ObjectAuditEmbeddableTrait;
    use ObjectTitleEmbeddableTrait;
    use ObjectStateEmbeddableTrait;
    use ObjectSoftDeleteEmbeddableTrait;

    #[ORM\Embedded(class: ObjectIdentityEmbeddable::class, columnPrefix: false)]
    private ObjectIdentityEmbeddable $objectIdentity;

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string', length: 26)]
        private readonly string $id,
        #[ORM\Column(type: 'string')]
        private readonly string $tenant,
        string $slug,
        string $label,
        #[ORM\Column(type: 'string', nullable: true)]
        private ?string $locale = null,
        #[ORM\Column(type: 'integer')]
        private int $weight = 0,
        #[ORM\Column(name: 'required_flag', type: 'boolean')]
        private bool $requiredFlag = false,
        #[ORM\Column(name: 'mod_only_flag', type: 'boolean')]
        private bool $modOnlyFlag = false,
    ) {
        $now = new \DateTimeImmutable();

        $this->objectIdentity = new ObjectIdentityEmbeddable(objectSlug: self::normalizeRequired($slug, 'slug'));
        $this->initializeObjectAudit($now);
        $this->touchModified($now);
        $this->initializeObjectTitle(self::normalizeRequired($label, 'label'));
        $this->initializeObjectState(objectStatus: 'active');
        $this->initializeObjectSoftDelete();
    }

    public static function create(
        string $tenant,
        string $id,
        string $slug,
        string $label,
        ?string $locale = null,
        int $weight = 0,
    ): self {
        return new self(
            id: $id,
            tenant: $tenant,
            slug: $slug,
            label: $label,
            locale: self::normalizeLocale($locale),
            weight: $weight,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function getObjectUuid(): string
    {
        return $this->objectIdentity->getObjectUuid();
    }

    public function getObjectSlug(): string
    {
        return $this->objectIdentity->getObjectSlug();
    }

    public function setObjectSlug(string $objectSlug): void
    {
        $this->objectIdentity->setObjectSlug($objectSlug);
    }

    public function tenant(): string
    {
        return $this->tenant;
    }

    public function slug(): string
    {
        return $this->getObjectSlug() ?? '';
    }

    public function label(): string
    {
        return $this->getFirstTitle() ?? '';
    }

    public function nameEntity(): string
    {
        return $this->label();
    }

    public function locale(): ?string
    {
        return $this->locale;
    }

    public function weight(): int
    {
        return $this->weight;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->getCreatedAt();
    }

    public function updatedAt(): ?\DateTimeImmutable
    {
        return $this->getModifiedAt();
    }

    public function requiredFlag(): bool
    {
        return $this->requiredFlag;
    }

    public function modOnlyFlag(): bool
    {
        return $this->modOnlyFlag;
    }

    public function rename(string $label): void
    {
        $label = self::normalizeRequired($label, 'label');

        if ($label === $this->label()) {
            return;
        }

        $this->setFirstTitle($label);
        $this->touchModified();
    }

    public function changeSlug(string $slug): void
    {
        $slug = self::normalizeRequired($slug, 'slug');

        if ($slug === $this->slug()) {
            return;
        }

        $this->setObjectSlug($slug);
        $this->touchModified();
    }

    public function changeLocale(?string $locale): void
    {
        $locale = self::normalizeLocale($locale);

        if ($locale === $this->locale) {
            return;
        }

        $this->locale = $locale;
        $this->touchModified();
    }

    public function changeWeight(int $weight): void
    {
        if ($weight === $this->weight) {
            return;
        }

        $this->weight = $weight;
        $this->touchModified();
    }

    public function setFlags(bool $requiredFlag, bool $modOnlyFlag): void
    {
        if ($requiredFlag === $this->requiredFlag && $modOnlyFlag === $this->modOnlyFlag) {
            return;
        }

        $this->requiredFlag = $requiredFlag;
        $this->modOnlyFlag = $modOnlyFlag;
        $this->touchModified();
    }

    /**
     * @param array{
     *     nameEntity?: string,
     *     label?: string,
     *     slug?: string,
     *     locale?: string|null,
     *     weight?: int,
     *     required_flag?: bool,
     *     mod_only_flag?: bool
     * } $patch
     */
    public function patch(array $patch): void
    {
        if (array_key_exists('nameEntity', $patch)) {
            $this->rename((string) $patch['nameEntity']);
        } elseif (array_key_exists('label', $patch)) {
            $this->rename((string) $patch['label']);
        }

        if (array_key_exists('slug', $patch)) {
            $this->changeSlug((string) $patch['slug']);
        }

        if (array_key_exists('locale', $patch)) {
            $this->changeLocale(null === $patch['locale'] ? null : (string) $patch['locale']);
        }

        if (array_key_exists('weight', $patch)) {
            $this->changeWeight((int) $patch['weight']);
        }

        if (array_key_exists('required_flag', $patch) || array_key_exists('mod_only_flag', $patch)) {
            $this->setFlags(
                array_key_exists('required_flag', $patch)
                    ? (bool) $patch['required_flag']
                    : $this->requiredFlag,
                array_key_exists('mod_only_flag', $patch)
                    ? (bool) $patch['mod_only_flag']
                    : $this->modOnlyFlag,
            );
        }
    }

    private static function normalizeRequired(string $value, string $field): string
    {
        $value = trim($value);

        if ('' === $value) {
            throw new \InvalidArgumentException(sprintf('Tag %s must not be empty.', $field));
        }

        return $value;
    }

    private static function normalizeLocale(?string $locale): ?string
    {
        if (null === $locale) {
            return null;
        }

        $locale = trim($locale);

        return '' === $locale ? null : $locale;
    }
}
