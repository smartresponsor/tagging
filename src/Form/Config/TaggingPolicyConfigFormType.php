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
        $booleanChoices = ['Enabled' => '1', 'Disabled' => '0'];

        $builder
            ->add('maxLength', IntegerType::class)
            ->add('maxTagsPerEntity', IntegerType::class)
            ->add('lowercaseNormalize', ChoiceType::class, ['choices' => $booleanChoices])
            ->add('collapseSpaces', ChoiceType::class, ['choices' => $booleanChoices])
            ->add('stripSymbols', ChoiceType::class, ['choices' => $booleanChoices])
            ->add('defaultLocale', TextType::class)
            ->add('allowedLocales', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TaggingPolicyConfigData::class,
            'csrf_protection' => true,
            'allow_extra_fields' => false,
        ]);
    }
}
