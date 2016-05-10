<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\MeetingRequest;

use Doctrine\ORM\EntityRepository;
use Proximum\Vimeet\Application\Command\MeetingRequest\PositionMeeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PositionMeetingType extends AbstractType
{
    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * @param ParticipantInfoGuesser $participantInfoGuesser
     */
    public function __construct(ParticipantInfoGuesser $participantInfoGuesser)
    {
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fromParticipants', ChoiceType::class, [
                'choices_as_values' => true,
                'multiple'          => true,
                'expanded'          => true,
                'choices'           => $options['meeting_request']->getFromSheet()->getParticipants()->toArray(),
                'choice_label'      => function (Participant $participant) {
                    return $this->participantInfoGuesser->guessParticipantCompleteName(
                        $participant,
                        $participant->getSheet()->getEvent()->getFallback()
                    );
                },
                'choice_value'      => function (Participant $participant) {
                    return $participant->getId();
                },
            ])
            ->add('toParticipants', ChoiceType::class, [
                'choices_as_values' => true,
                'multiple'          => true,
                'expanded'          => true,
                'choices'           => $options['meeting_request']->getToSheet()->getParticipants()->toArray(),
                'choice_label'      => function (Participant $participant) {
                    return $this->participantInfoGuesser->guessParticipantCompleteName(
                        $participant,
                        $participant->getSheet()->getEvent()->getFallback()
                    );
                },
                'choice_value'      => function (Participant $participant) {
                    return $participant->getId();
                },
            ])
            ->add('slot', EntityType::class, [
                'class'         => MeetingSlot::class,
                'expanded'      => true,
                'query_builder' => function (EntityRepository $repository) use ($options) {
                    return $repository
                        ->createQueryBuilder('slot')
                        ->where('slot.event = :event')
                        ->setParameter('event', $options['event']);
                },
                'choice_label' => function (MeetingSlot $meetingSlot) use ($options) {
                    $begin    = clone $meetingSlot->getBegin();
                    $end      = clone $meetingSlot->getEnd();
                    $timezone = new \DateTimeZone($options['event']->getTimeZone());

                    return sprintf(
                        '%s : %s %s',
                        $begin->setTimezone($timezone)->format('d/m/Y'),
                        $begin->setTimezone($timezone)->format('H\hi'),
                        $end->setTimezone($timezone)->format('H\hi')
                    );
                },
                'choice_attr' => function (MeetingSlot $meetingSlot) {
                    return ['data-id' => $meetingSlot->getId(), 'disabled' => 'disabled'];
                },
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['meeting_request', 'event']);
        $resolver->setDefaults([
            'method'     => 'POST',
            'data_class' => PositionMeeting::class,
        ]);
    }
}
