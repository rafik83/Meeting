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

class WhatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('participant', new WhatSheetType(), [
                'template' => $options['who']->getParticipantTemplate(),
                'locale'   => $options['locale'],
            ])
            ->add('sheet', new WhatSheetType(), [
                'template' => $options['who']->getSheetTemplate(),
                'locale'   => $options['locale'],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'multiple' => true,
            'expanded' => true,
        ]);

        $resolver->setRequired(['who', 'locale']);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'what';
    }
}
