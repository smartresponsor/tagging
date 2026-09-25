<?php

declare(strict_types=1);

namespace App\Tagging\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

final class TagUpdateType extends TagAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nameEntity', TextType::class, [
                'required' => false,
                'constraints' => [new Assert\Length(max: 255)],
            ])
            ->add('locale', TextType::class, [
                'required' => false,
                'constraints' => [new Assert\Length(min: 2, max: 16)],
            ])
            ->add('weight', IntegerType::class, [
                'required' => false,
            ]);
    }
}
