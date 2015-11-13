<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Event;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WhatSheetType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['template'] as $name => $step) {

            if (isset($step['template'])) {

                $builder
                    ->add($name, new WhatSheetType(), [
                        'template' => $step['template'],
                        'locale'   => $options['locale'],
                        'label'    => $step['label'][$options['locale']],
                    ])
                ;

            } else {

                $builder
                    ->add($name, 'checkbox', [
                        'label'    => $step['label'][$options['locale']],
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

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'what_sheet';
    }
}
