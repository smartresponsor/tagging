<?php

declare(strict_types=1);

namespace App\Tagging\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

final class TagWebhookCreateType extends TagAbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('url', UrlType::class, [
                'constraints' => [new Assert\NotBlank(), new Assert\Url()],
            ])
            ->add('secret', TextType::class, [
                'required' => false,
                'constraints' => [new Assert\Length(min: 16, max: 255)],
            ]);
    }
}
