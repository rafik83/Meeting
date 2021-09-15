<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Library;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType as CoreTextType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TelephoneType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['country']);
        $resolver->setDefaults([
            'attr' => function (Options $options) {
                return [
                    'class'                => 'telephone-intl-input',
                    'data-initial-country' => strtolower($options['country']),
                ];
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CoreTextType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'telephone';
    }
}
