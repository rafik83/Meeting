<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Happening;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\ParticipateEvent;
use Proximum\Vimeet\Application\Event\Happening\ParticipateHappeningEvent;
use Proximum\Vimeet\Application\Event\Happening\UnParticipateHappeningEvent;
use Proximum\Vimeet\Application\Exception\Happening\NotEnoughtRemainingParticipationsException;
use Proximum\Vimeet\Application\Exception\Happening\ParticipantNotAvailableException;
use Proximum\Vimeet\Application\Exception\Happening\ParticipantRequiredException;
use Proximum\Vimeet\Application\Exception\Happening\WrongInvitationCodeException;
use Proximum\Vimeet\Domain\Happening\ParticipationCount;
use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class ParticipateHandler
{
    /** @var HappeningParticipationRepositoryInterface */
    private $happeningParticipationRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var QuestionRepositoryInterface */
    private $questionRepository;

    /** @var ParticipationCount */
    private $participationCount;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    /**
     * @param HappeningParticipationRepositoryInterface $happeningParticipationRepository
     * @param ParticipantRepositoryInterface            $participantRepository
     * @param QuestionRepositoryInterface               $questionRepository
     * @param ParticipationCount                        $participationCount
     * @param DelayedEventDispatcher                    $eventDispatcher
     * @param \DateTimeInterface                        $dateTime
     */
    public function __construct(
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        ParticipantRepositoryInterface $participantRepository,
        QuestionRepositoryInterface $questionRepository,
        ParticipationCount $participationCount,
        DelayedEventDispatcher $eventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->participantRepository            = $participantRepository;
        $this->questionRepository               = $questionRepository;
        $this->participationCount               = $participationCount;
        $this->eventDispatcher                  = $eventDispatcher;
        $this->dateTime                         = $dateTime;
    }

    /**
     * @param Participate $participate
     *
     * @throws NotEnoughtRemainingParticipationsException
     * @throws ParticipantNotAvailableException
     * @throws ParticipantRequiredException
     * @throws WrongInvitationCodeException
     */
    public function handle(Participate $participate)
    {
        $previousParticipants = $this->participantRepository->getParticipantsForHappening(
            $participate->sheet,
            $participate->happening
        );

        $update = false;
        if (0 < count($previousParticipants)) {
            foreach ($participate->participants as $participant) {
                if (false === in_array($participant, $previousParticipants)) {
                    $update = true;
                }
            }
            foreach ($previousParticipants as $participant) {
                if (false === in_array($participant, $participate->participants)) {
                    $update = true;
                }
            }
        }

        $previousQuestion = $this->questionRepository
            ->findByHappeningAndSheet($participate->happening, $participate->sheet)
            ->getContent();

        $updatedQuestion = $previousQuestion !== $participate->question;

        if (!$participate->cancel
            && !$update
            && !$updatedQuestion
            && $participate->happening->isPrivate()
            && $participate->invitationCode !== $participate->happening->getInvitationCode()
        ) {
            throw new WrongInvitationCodeException();
        }

        // If not previous selected participants and 0 new selected participants
        if (0 === count($participate->participants) && 0 === count($previousParticipants)) {
            throw new ParticipantRequiredException();
        }

        $availableParticipants = [];

        if (0 < count($participate->participants)) {
            $availableParticipants = $this->participantRepository->getAvailableParticipantsForHappening(
                $participate->participants,
                $participate->happening
            );
        }

        $remainingParticipations = $this->participationCount->getRemaining($participate->happening);

        if (count($participate->participants) - count($previousParticipants) > $remainingParticipations) {
            throw new NotEnoughtRemainingParticipationsException($remainingParticipations);
        }

        foreach ($participate->participants as $participant) {
            if (!in_array($participant, $availableParticipants)) {
                throw new ParticipantNotAvailableException();
            }
        }

        // Add participants to happening
        foreach ($participate->participants as $participant) {
            if (false === in_array($participant, $previousParticipants)) {

                $happeningParticipation = $this->happeningParticipationRepository->findByHappeningAndUser(
                    $participate->happening,
                    $participant->getUser()
                );

                if ($happeningParticipation !== null) {
                    $this->happeningParticipationRepository->update(
                        $happeningParticipation->setDisabled(false)
                    );
                } else {
                    $this->happeningParticipationRepository->add(
                        new HappeningParticipation($participate->happening, $participant->getUser())
                    );
                }

                $this->eventDispatcher->dispatch(
                    Events::HAPPENING_PARTICIPATE,
                    new ParticipateHappeningEvent($participant)
                );
            }
        }

        // Remove deselected participants
        foreach ($previousParticipants as $participant) {
            if (false === in_array($participant, $participate->participants)) {
                $this->happeningParticipationRepository->removeUserForHappening(
                    $participant->getUser(),
                    $participate->happening
                );

                $this->eventDispatcher->dispatch(
                    Events::HAPPENING_UN_PARTICIPATE,
                    new UnParticipateHappeningEvent($participant)
                );
            }
        }

        if (true === $participate->happening->isQuestionAllowed()) {
            // Remove previous question
            $this->questionRepository->removeQuestionFromUserForHappening(
                $participate->createdBy,
                $participate->happening
            );

            // Add question
            if (0 < count($participate->participants) && !empty($participate->question)) {
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

        $this->eventDispatcher->dispatch(
            Events::HAPPENING_PARTICIPATED,
            new ParticipateEvent($participate->sheet, $participate->participants, $participate->happening)
        );
    }
}
