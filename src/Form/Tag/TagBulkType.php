<?php

declare(strict_types=1);

namespace App\Tagging\Form\Tag;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Validator\Constraints as Assert;

final class TagBulkType extends AbstractTagType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('operations', CollectionType::class, [
            'entry_type' => TagAssignmentOperationType::class,
            'allow_add' => true,
            'allow_delete' => false,
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Count(min: 1, max: 500),
            ],
        ]);
    }
}
