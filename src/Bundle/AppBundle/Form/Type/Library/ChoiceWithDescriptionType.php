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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ChoiceWithDescriptionType extends AbstractLocalizedType
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

        $builder
            ->add('value', ChoiceType::class, [
                'choices_as_values' => true,
                'choices'  => array_flip($choices),
                'expanded' => true,
                'required' => isset($template['required']) ? $template['required'] : true,
            ]);

        if (isset($template['quantity']['min'])
            && isset($template['quantity']['max'])
            && $template['quantity']['min'] <= $template['quantity']['max']
        ) {
            $builder->add('quantity', QuantityType::class, [
                'min'   => $template['quantity']['min'],
                'max'   => $template['quantity']['max'],
                'range' => isset($template['quantity']['range']) ? $template['quantity']['range'] : 1,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'choice_with_description';
    }
}
