<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\MeetingRequest;

use Doctrine\ORM\EntityRepository;
use Proximum\Vimeet\Application\Components\Participant\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\Model\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Proximum\Vimeet\Application\Command\MeetingRequest\PositionMeeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

class PositionMeetingType extends AbstractType
{
    private $participantInfoGuesser;

    public function __construct(ParticipantInfoGuesser $participantInfoGuesser)
    {
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fromParticipants', ChoiceType::class, [
                'choices_as_values' => true,
                'multiple'          => true,
                'expanded'          => true,
                'choices'           => $options['meeting_request']->getFrom()->getParticipants()->toArray(),
                'choice_label'      => function (Participant $participant) {
                    return $this->participantInfoGuesser->guessParticipantInfo($participant);
                }
            ])
            ->add('toParticipants', ChoiceType::class, [
                'choices_as_values' => true,
                'multiple'          => true,
                'expanded'          => true,
                'choices'           => $options['meeting_request']->getTo()->getParticipants()->toArray(),
                'choice_label'      => function (Participant $participant) {
                    return $this->participantInfoGuesser->guessParticipantInfo($participant);
                }
            ])
            ->add('slot', EntityType::class, [
                'class' => MeetingSlot::class,
                'expanded' => true,
                'query_builder' => function (EntityRepository $repository) use ($options) {
                    return $repository
                        ->createQueryBuilder('slot')
                        ->join('slot.schedule', 'schedule', 'WITH', 'schedule.event = :event')
                        ->setParameter('event', $options['event']);
                },
                'choice_label' => function (MeetingSlot $meetingSlot) {
                    return sprintf(
                        '%s : %s %s',
                        $meetingSlot->getBegin()->format('d/m/Y'),
                        $meetingSlot->getBegin()->format('H\hi'),
                        $meetingSlot->getEnd()->format('H\hi')
                    );
                },
                'choice_attr' => function (MeetingSlot $meetingSlot) {
                    return ['data-id' => $meetingSlot->getId()];
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['meeting_request', 'event']);
        $resolver->setDefaults([
            'method'     => 'POST',
            'data_class' => PositionMeeting::class,
        ]);
    }
}
