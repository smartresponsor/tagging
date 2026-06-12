<?php

declare(strict_types=1);

namespace App\Tagging\Form\Config;

use App\Tagging\Value\Form\Config\TaggingPolicyConfigData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TaggingPolicyConfigFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('maxLength', IntegerType::class, [
                'label' => 'Max length',
                'required' => true,
                'help' => 'Maximum allowed slug length.',
            ])
            ->add('maxTagsPerEntity', IntegerType::class, [
                'label' => 'Max tags per entity',
                'required' => true,
            ])
            ->add('lowercaseNormalize', ChoiceType::class, [
                'label' => 'Lowercase normalize',
                'choices' => ['Yes' => '1', 'No' => '0'],
                'required' => true,
            ])
            ->add('collapseSpaces', ChoiceType::class, [
                'label' => 'Collapse spaces',
                'choices' => ['Yes' => '1', 'No' => '0'],
                'required' => true,
            ])
            ->add('stripSymbols', ChoiceType::class, [
                'label' => 'Strip symbols',
                'choices' => ['Yes' => '1', 'No' => '0'],
                'required' => true,
            ])
            ->add('defaultLocale', TextType::class, [
                'label' => 'Default locale',
                'required' => true,
            ])
            ->add('allowedLocales', TextType::class, [
                'label' => 'Allowed locales',
                'required' => true,
                'help' => 'Comma-separated locale list.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TaggingPolicyConfigData::class,
            'csrf_protection' => true,
            'label' => false,
        ]);
    }
}
