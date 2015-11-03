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

class OptionType extends AbstractLocalizedType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $template = $options['template'];
        $locale   = $options['locale'];

        $builder->add('value', 'checkbox', [
            'label'    => $template['label'][$locale],
            'required' => false,
        ]);

        if (isset($template['quantity']['min'])
            && isset($template['quantity']['max'])
            && $template['quantity']['min'] < $template['quantity']['max']
        ) {
            $builder->add('quantity', new QuantityType(), [
                'min'   => $template['quantity']['min'],
                'max'   => $template['quantity']['max'],
                'range' => isset($template['quantity']['range']) ? $template['quantity']['range'] : 1
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'lib_option';
    }
}
