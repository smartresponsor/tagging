<?php

declare(strict_types=1);

namespace App\Tagging\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

final class TagSynonymFilterType extends TagAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('tagId', TextType::class, ['constraints' => [new Assert\NotBlank()]]);
    }
}
