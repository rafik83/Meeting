<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Participant;

use Proximum\Vimeet\Application\Components\Participant\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeetingParticipantChoiceType extends ParticipantChoiceType
{
    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * ParticipantAvailableForSlotChoiceType constructor.
     *
     * @param ParticipantInfoGuesser         $participantInfoGuesser
     * @param ParticipantRepositoryInterface $participantRepository
     */
    public function __construct(ParticipantInfoGuesser $participantInfoGuesser, ParticipantRepositoryInterface $participantRepository)
    {
        parent::__construct($participantInfoGuesser);
        $this->participantRepository = $participantRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'choices' => function (Options $options) {
                return $this
                    ->participantRepository
                    ->findAvailableBySheetAndMeeting($options['sheet'], $options['meeting']);
            }
        ]);
        $resolver->setRequired('meeting');
    }
}
