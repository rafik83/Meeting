<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuantityType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setRequired(
            [
                'max',
                'minMessage',
                'maxMessage',
            ]
        );
        $optionsResolver->setDefaults(
            [
                'min' => 0,
                'attr' => function (Options $options) {
                    return [
                        'data-min'         => $options['min'],
                        'data-max'         => $options['max'],
                        'data-min-message' => $options['minMessage'],
                        'data-max-message' => $options['maxMessage'],
                    ];
                },
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'quantity';
    }
}
