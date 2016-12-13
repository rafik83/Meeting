<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening;

use Proximum\Vimeet\Application\Exception\Happening\ParticipantNotAvailableException;
use Proximum\Vimeet\Application\Exception\Happening\ParticipantRequiredException;
use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class ParticipateHandler
{
    /** @var HappeningParticipationRepositoryInterface */
    private $happeningParticipationRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var QuestionRepositoryInterface */
    private $questionRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     * @param ParticipantRepositoryInterface            $participantRepository
     * @param QuestionRepositoryInterface               $questionRepository
     * @param \DateTimeInterface                        $dateTime
     */
    public function __construct(
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        ParticipantRepositoryInterface $participantRepository,
        QuestionRepositoryInterface $questionRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->participantRepository            = $participantRepository;
        $this->questionRepository               = $questionRepository;
        $this->dateTime                         = $dateTime;
    }

    /**
     * @param Participate $participate
     *
     * @throws ParticipantNotAvailableException
     * @throws ParticipantRequiredException
     */
    public function handle(Participate $participate)
    {
        if (0 === count($participate->participants)) {
            throw new ParticipantRequiredException();
        }

        $availableParticipants = $this->participantRepository->getAvailableParticipants(
            $participate->participants,
            $participate->happening->getBegin(),
            $participate->happening->getEnd()
        );

        foreach ($participate->participants as $participant) {
            $happeningParticipation = $this->happeningParticipationRepository->findByHappeningAndParticipant(
                $participate->happening,
                $participant
            );

            if (null === $happeningParticipation) {
                if (!in_array($participant, $availableParticipants)) {
                    throw new ParticipantNotAvailableException();
                }

                // Add participant to happening
                $this->happeningParticipationRepository->add(
                    new HappeningParticipation($participate->happening, $participant)
                );
            }
        }

        // Add question
        if ($participate->happening->isQuestionAllowed() && !empty($participate->question)) {
            $this->questionRepository->add(
                new Question(
                    $participate->happening,
                    $participate->sheet,
                    $participate->createdBy,
                    $this->dateTime,
                    $participate->question
                )
            );
        }
    }
}
