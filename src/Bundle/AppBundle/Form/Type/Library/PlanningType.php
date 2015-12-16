<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Library;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType as CoreCheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as CoreChoiceType;
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
        $sheet    = $options['sheet'];

        $range = range(1, $sheet->getType()->getMaxPlanning(), 1);

        $builder
            ->add('planning', CoreCheckboxType::class, [
                'label'    => $template['label'][$locale],
                'required' => isset($template['required']) ? $template['required'] : false,
            ])
            ->add('quantity', CoreChoiceType::class, [
                'choices'           => array_combine($range, $range),
                'label'             => false,
                'required'          => isset($template['required']) ? $template['required'] : false,
                'choices_as_values' => true,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'lib_planning';
    }
}
