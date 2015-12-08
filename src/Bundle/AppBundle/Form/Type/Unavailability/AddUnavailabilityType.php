<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Unavailability;

use Proximum\Vimeet\Application\Command\Unavailability\Add;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
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
            ->add('from', TimeType::class, [
                'input'         => 'datetime',
                'widget'        => 'choice',
                'view_timezone' => 'Europe/Paris',
            ])
            ->add('to', TimeType::class, [
                'input'         => 'datetime',
                'widget'        => 'choice',
                'view_timezone' => 'Europe/Paris',
            ])
            ->add('participants', 'participant_choice', [
                'sheet'      => $options['sheet'],
                'multiple'   => true,
                'expanded'   => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'sheet'
        ]);

        $resolver->setDefaults([
            'data_class' => Add::class,
            'csrf_token_id'  => 'add_unavailability',
        ]);
    }
}
