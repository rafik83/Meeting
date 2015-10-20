<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Participant;

use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantCreateType extends AbstractParticipantType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['template', 'locale']);
        $resolver->setDefaults([
            'data_class' => 'Proximum\Vimeet\Application\Command\Participant\Create',
            'intention'  => 'participant_create',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'participant_create';
    }
}
