<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Unavailability;

use Proximum\Vimeet\Application\Command\Unavailability\Update;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateUnavailabilityType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('from', 'time', [
                'input'         => 'datetime',
                'widget'        => 'choice',
                'view_timezone' => 'Europe/Paris',
            ])
            ->add('to', 'time', [
                'input'         => 'datetime',
                'widget'        => 'choice',
                'view_timezone' => 'Europe/Paris',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Update::class,
            'intention'  => 'update_unavailability',
        ]);
    }
}
