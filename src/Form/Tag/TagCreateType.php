<?php

declare(strict_types=1);

namespace App\Tagging\Form\Tag;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

final class TagCreateType extends AbstractTagType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nameEntity', TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(max: 255),
                ],
            ])
            ->add('slug', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Length(max: 255),
                    new Assert\Regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/'),
                ],
            ])
            ->add('locale', TextType::class, [
                'empty_data' => 'en',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(min: 2, max: 16),
                ],
            ])
            ->add('weight', IntegerType::class, [
                'empty_data' => '0',
            ]);
    }
}
