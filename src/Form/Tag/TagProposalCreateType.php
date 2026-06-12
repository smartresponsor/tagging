<?php

declare(strict_types=1);

namespace App\Tagging\Form\Tag;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints as Assert;

final class TagProposalCreateType extends AbstractTagType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'create' => 'create',
                    'update' => 'update',
                    'relation' => 'relation',
                    'synonym' => 'synonym',
                    'classification' => 'classification',
                ],
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('payload', TextareaType::class, [
                'constraints' => [new Assert\NotBlank(), new Assert\Json()],
            ]);
    }
}
