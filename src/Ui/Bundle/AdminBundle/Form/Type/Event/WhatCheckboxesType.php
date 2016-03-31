<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WhatCheckboxesType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['template'] as $name => $step) {
            $label = isset($step['label'][$options['locale']]) ? $step['label'][$options['locale']] : '';

            if (isset($step['template'])) {
                $builder
                    ->add($name, self::class, [
                        'label'    => $label,
                        'template' => $step['template'],
                        'locale'   => $options['locale'],
                    ])
                ;
            } else {
                $builder
                    ->add($name, CheckboxType::class, [
                        'label'    => $label,
                        'required' => false,
                    ])
                ;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['template', 'locale']);
    }
}
