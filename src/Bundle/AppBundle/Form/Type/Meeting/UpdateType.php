<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Meeting;

use Proximum\Vimeet\Application\Command\Meeting\Update;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('meetingSlot', 'entity', [
                'class'    => MeetingSlot::class,
                'expanded' => true,
                'choice_label' => function (MeetingSlot $meetingSlot) {
                    return $meetingSlot->getBegin()->format('d/m/Y H:i') . ' - '  . $meetingSlot->getEnd()->format('H:i');
                }
            ])
            ->add('fromParticipants', 'participant_choice', [
                'sheet'    => $options['data']->meeting->getFrom(),
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('toParticipants', 'participant_choice', [
                'sheet'    => $options['data']->meeting->getTo(),
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('message', 'textarea')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method'     => 'POST',
            'data_class' => Update::class
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'meeting_update';
    }
}
