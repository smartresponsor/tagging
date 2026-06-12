<?php

declare(strict_types=1);

namespace App\Tagging\Form\Tag;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

final class TagRelationCreateType extends AbstractTagType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fromTagId', TextType::class, ['constraints' => [new Assert\NotBlank()]])
            ->add('toTagId', TextType::class, ['constraints' => [new Assert\NotBlank()]])
            ->add('type', TextType::class, [
                'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 64)],
            ]);
    }
}
