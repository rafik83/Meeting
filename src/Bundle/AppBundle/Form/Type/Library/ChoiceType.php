<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Library;

use Symfony\Component\Form\FormBuilderInterface;

class ChoiceType extends AbstractLocalizedType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $template = $options['template'];
        $locale   = $options['locale'];
        $choices  = [];

        foreach ($template['choices'] as $key => $choice) {
            $choices[$key] = $choice['label'][$locale];
        }

        asort($choices);

        $builder->add('value', 'choice', [
            'choices'     => $choices,
            'placeholder' => 'choice.placeholder',
            'required'    => isset($template['required']) ? $template['required'] : true,
            'label'       => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'lib_choice';
    }
}
