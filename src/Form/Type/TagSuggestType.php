<?php

declare(strict_types=1);

namespace App\Tagging\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

final class TagSuggestType extends TagAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('q', TextType::class, [
                'required' => false,
                'constraints' => [new Assert\Length(max: 255)],
            ])
            ->add('limit', IntegerType::class, [
                'required' => false,
                'empty_data' => '10',
                'constraints' => [new Assert\Range(min: 1, max: 50)],
            ]);
    }
}
