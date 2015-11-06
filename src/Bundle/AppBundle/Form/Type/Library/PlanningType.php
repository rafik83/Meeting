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

class PlanningType extends AbstractLocalizedType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $template = $options['template'];
        $locale   = $options['locale'];

        $choices = [1 => 1, 2 => 2, 3 => 3];

        $builder
            ->add('planning', 'checkbox', [
                'label'    => $template['label'][$locale],
                'required' => isset($template['required']) ? $template['required'] : false,
            ])
            ->add('planning_bought', 'choice', [
                'choices'  => $choices,
                'label'    => false,
                'required' => isset($template['required']) ? $template['required'] : false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'lib_planning';
    }
}
