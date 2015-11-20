<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Unavailability;

use Proximum\Vimeet\Application\Command\Unavailability\AddUnavailability;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddUnavailabilityType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('from', 'time', [
                'input'  => 'datetime',
                'widget' => 'choice',
            ])
            ->add('to', 'time', [
                'input'  => 'datetime',
                'widget' => 'choice',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AddUnavailability::class,
            'intention'  => 'add_unavailability',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'add_unavailability';
    }
}
