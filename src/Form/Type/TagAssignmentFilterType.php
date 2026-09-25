<?php

declare(strict_types=1);

namespace App\Tagging\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

final class TagAssignmentFilterType extends TagAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tagId', TextType::class, ['required' => false])
            ->add('assignedType', TextType::class, ['required' => false])
            ->add('assignedId', TextType::class, ['required' => false])
            ->add('limit', IntegerType::class, [
                'required' => false,
                'empty_data' => '100',
                'constraints' => [new Assert\Range(min: 1, max: 500)],
            ]);
    }
}
