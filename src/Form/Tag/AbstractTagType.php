<?php

declare(strict_types=1);

namespace App\Tagging\Form\Tag;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractTagType extends AbstractType
{
    final public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'csrf_protection' => false,
            'allow_extra_fields' => false,
            'trim' => true,
        ]);

        $this->configureTagOptions($resolver);
    }

    protected function configureTagOptions(OptionsResolver $resolver): void {}
}
