<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Components\Participant\Remove\ParticipantConflictView;
use Proximum\Vimeet\Application\Components\Participant\Remove\ProductAttributedToParticipantConflictChecker;
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
    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var CartManager */
    private $cartManager;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var StepParticipantAndPlanning */
    private $stepParticipantAndPlanning;

    /** @var ProductAttributedToParticipantConflictChecker */
    private $productAttributedToParticipantConflictChecker;

    /**
     * @param ParticipantRepositoryInterface                $participantRepository
     * @param CartManager                                   $cartManager
     * @param DelayedEventDispatcher                        $eventDispatcher
     * @param MeetingRepositoryInterface                    $meetingRepository
     * @param ParticipantInfoGuesser                        $participantInfoGuesser
     * @param StepParticipantAndPlanning                    $stepParticipantAndPlanning
     * @param ProductAttributedToParticipantConflictChecker $productAttributedToParticipantConflictChecker
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        CartManager $cartManager,
        DelayedEventDispatcher $eventDispatcher,
        MeetingRepositoryInterface $meetingRepository,
        ParticipantInfoGuesser $participantInfoGuesser,
        StepParticipantAndPlanning $stepParticipantAndPlanning,
        ProductAttributedToParticipantConflictChecker $productAttributedToParticipantConflictChecker
    ) {
        $this->participantRepository = $participantRepository;
        $this->cartManager = $cartManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->meetingRepository = $meetingRepository;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->stepParticipantAndPlanning = $stepParticipantAndPlanning;
        $this->productAttributedToParticipantConflictChecker = $productAttributedToParticipantConflictChecker;
    }

    /**
     * @param Remove $remove
     *
     * @return RemoveResult
     *
     * @throws CanNotRemoveAllParticipantsException
     */
    public function handle(Remove $remove): RemoveResult
    {
        if (\count($remove->participants) === $remove->sheet->countParticipants()) {
            throw new CanNotRemoveAllParticipantsException('All participants can not be selected to be remove');
        }

        $participantsWithMeeting = [];

        /** @var Participant $participant */
        foreach ($remove->participants as $participant) {
            $participantHasMeeting = $this->meetingRepository->hasScheduledMeetingByParticipant($participant);

            if (true === $participantHasMeeting) {
                $participantsWithMeeting[$participant->getId()] = $participant;
            }
        }

        // Avoid deletion if there is someone with the exception of meeting
        if (!empty($participantsWithMeeting)) {
            return new RemoveResult(
                $this->getArrayOfParticipantsName($participantsWithMeeting, $remove->locale),
                true
            );
        }

        $conflictView = $this->productAttributedToParticipantConflictChecker
            ->getParticipantsWithConflictOnProductAttributed($remove->participants, $remove->locale)
        ;

        if ($conflictView->hasConflict()) {
            return new RemoveResult(
                array_map(function (ParticipantConflictView $participantConflictView) {
                    return $participantConflictView->participantName;
                }, $conflictView->participantConflicts),
                false,
                true
            );
        }

        $usersRemovedFromSheet = [];

        foreach ($remove->participants as $participantToDelete) {
            $participantUser = $participantToDelete->getUser();
            $usersRemovedFromSheet[$participantUser->getId()] = $participantUser;

            $remove->sheet->removeParticipant($participantToDelete);
            $this->participantRepository->delete($participantToDelete);
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
        $this->eventDispatcher->dispatch(
            Events::PARTICIPANT_REMOVED,
            new ParticipantRemovedEvent(
                $remove->sheet,
                $usersRemovedFromSheet
            )
        );

        return new RemoveResult();
    }

    /**
     * @param Participant[] $participants
     * @param string        $locale
     *
     * @return string[]
     */
    private function getArrayOfParticipantsName(array $participants, string $locale): array
    {
        return array_map(function (Participant $participant) use ($locale) {
            return $this->participantInfoGuesser->guessParticipantCompleteName($participant, $locale);
        }, $participants);
    }
}
