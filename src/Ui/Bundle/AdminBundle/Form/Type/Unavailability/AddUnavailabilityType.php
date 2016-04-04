<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Unavailability;

use Proximum\Vimeet\Application\Command\Unavailability\Add;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Participant\ParticipantEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddUnavailabilityType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $event = $options['sheet']->getEvent();

        $builder
            ->add('from', DateTimeType::class, [
                'input'         => 'datetime',
                'widget'        => 'choice',
                'view_timezone' => $event->getTimeZone(),
            ])
            ->add('to', DateTimeType::class, [
                'input'         => 'datetime',
                'widget'        => 'choice',
                'view_timezone' => $event->getTimeZone(),
            ])
            ->add('participants', ParticipantEntityType::class, [
                'sheet'    => $options['sheet'],
                'multiple' => true,
                'expanded' => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'sheet',
        ]);

        $resolver->setDefaults([
            'data_class'    => Add::class,
            'csrf_token_id' => 'add_unavailability',
        ]);
    }
}
