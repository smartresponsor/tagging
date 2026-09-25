<?php

declare(strict_types=1);

namespace App\Tagging\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints as Assert;

final class TagExportType extends TagAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('format', ChoiceType::class, [
                'choices' => ['json' => 'json', 'ndjson' => 'ndjson', 'csv' => 'csv'],
                'empty_data' => 'json',
            ])
            ->add('limit', IntegerType::class, [
                'required' => false,
                'empty_data' => '5000',
                'constraints' => [new Assert\Range(min: 1, max: 10000)],
            ]);
    }
}
