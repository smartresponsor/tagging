<?php

declare(strict_types=1);

namespace App\Tagging\Form\Tag;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Validator\Constraints as Assert;

final class TagImportType extends AbstractTagType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('items', CollectionType::class, [
            'entry_type' => TagCreateType::class,
            'allow_add' => true,
            'allow_delete' => false,
            'constraints' => [new Assert\Count(min: 1, max: 1000)],
        ]);
    }
}
