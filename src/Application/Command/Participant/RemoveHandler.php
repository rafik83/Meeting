<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Components\Step\StepParticipantAndPlanning;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantRemovedEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Application\Exception\Participant\CanNotRemoveAllParticipantsException;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class RemoveHandler
{
    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /** @var StepParticipantAndPlanning */
    private $stepParticipantAndPlanning;

    /**
     * @param ParticipantRepositoryInterface $participantRepository
     * @param CartManager                    $cartManager
     * @param DelayedEventDispatcher         $eventDispatcher
     * @param MeetingRepositoryInterface     $meetingRepository
     * @param ParticipantInfoGuesser         $participantInfoGuesser
     * @param StepParticipantAndPlanning     $stepParticipantAndPlanning
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        CartManager $cartManager,
        DelayedEventDispatcher $eventDispatcher,
        MeetingRepositoryInterface $meetingRepository,
        ParticipantInfoGuesser $participantInfoGuesser,
        StepParticipantAndPlanning $stepParticipantAndPlanning
    ) {
        $this->participantRepository = $participantRepository;
        $this->cartManager = $cartManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->meetingRepository = $meetingRepository;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->stepParticipantAndPlanning = $stepParticipantAndPlanning;
    }

    /**
     * @param Remove $remove
     *
     * @return RemoveResult
     *
     * @throws CanNotRemoveAllParticipantsException
     */
    public function handle(Remove $remove)
    {
        if (count($remove->participants) === $remove->sheet->countParticipants()) {
            throw new CanNotRemoveAllParticipantsException('All participants can not be selected to be remove');
        }

        // Array of participant with meeting
        $hasMeeting = [];
        $toDelete   = [];

        /** @var Participant $participant */
        foreach ($remove->participants as $participant) {
            $countmeeting = $this->meetingRepository->countByParticipant($participant);

            if ($countmeeting !== 0) {
                $hasMeeting[$participant->getId()] = $participant;
            } else {
                $toDelete[$participant->getId()] = $participant;
            }
        }

        // Avoid delation if there is someone with the exception of meeting
        if (empty($hasMeeting)) {
            foreach ($toDelete as $participantToDelete) {
                $remove->sheet->removeParticipant($participantToDelete);
                $this->participantRepository->delete($participantToDelete);
            }
        }

        // Update cart
        $cart = $this->cartManager->getCart($remove->sheet);
        $cart = $this->cartManager->updateParticipantsQuantity(
            $cart,
            $this->stepParticipantAndPlanning->build($remove->sheet)->participantsProduct
        );
        $this->cartManager->save($cart);

        $sheetUpdated = new SheetUpdatedEvent($remove->sheet);
        $this->eventDispatcher->dispatch(Events::SHEET_UPDATED, $sheetUpdated);
        $this->eventDispatcher->dispatch(Events::PARTICIPANT_REMOVED, new ParticipantRemovedEvent($remove->sheet));

        $participantNames = [];

        foreach ($hasMeeting as $participantWithMeeting) {
            $participantNames[] = $this->participantInfoGuesser->guessParticipantCompleteName($participantWithMeeting, $remove->locale);
        }

        return new RemoveResult($participantNames);
    }
}
