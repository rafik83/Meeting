<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Library;

use Proximum\Vimeet\Application\Components\Library\Choice\ChoiceBuilder;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceType extends AbstractLocalizedType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'placeholder' => function (Options $options) {
                return $options['template']['placeholder'][$options['locale']];
            },
            'choices' => function (Options $options) {
                return $this->getChoices($options);
            },
            'choices_as_values' => true,
            'translation_domain' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'lib_choice';
    }

    /**
     * @param \ArrayAccess $options
     *
     * @return array
     */
    protected function getChoices(\ArrayAccess $options)
    {
        $choices = $options['template']['choices'];
        $locale = $options['locale'];
        $builder = new ChoiceBuilder();

        return $builder->areGroupedChoices($choices) ?
            $builder->buildGroupedChoices($choices, $locale) :
            $builder->buildChoices($choices, $locale);
    }
}
