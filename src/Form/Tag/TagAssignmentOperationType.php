<?php

declare(strict_types=1);

namespace App\Tagging\Form\Tag;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

final class TagAssignmentOperationType extends AbstractTagType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('operation', ChoiceType::class, [
                'choices' => ['assign' => 'assign', 'unassign' => 'unassign'],
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('tagId', TextType::class, [
                'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 64)],
            ])
            ->add('assignedType', TextType::class, [
                'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 255)],
            ])
            ->add('assignedId', TextType::class, [
                'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 255)],
            ]);
    }
}
