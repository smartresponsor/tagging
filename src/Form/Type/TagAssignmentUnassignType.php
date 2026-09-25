<?php

declare(strict_types=1);

namespace App\Tagging\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

final class TagAssignmentUnassignType extends TagAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tagId', TextType::class, ['constraints' => [new Assert\NotBlank()]])
            ->add('assignedType', TextType::class, ['constraints' => [new Assert\NotBlank()]])
            ->add('assignedId', TextType::class, ['constraints' => [new Assert\NotBlank()]])
            ->add('idempotencyKey', TextType::class, [
                'required' => false,
                'constraints' => [new Assert\Length(max: 255)],
            ]);
    }
}
