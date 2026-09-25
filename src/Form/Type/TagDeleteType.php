<?php

declare(strict_types=1);

namespace App\Tagging\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints as Assert;

final class TagDeleteType extends TagAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('confirm', CheckboxType::class, [
            'mapped' => false,
            'constraints' => [new Assert\IsTrue(message: 'Deletion must be explicitly confirmed.')],
        ]);
    }
}
