<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant\Add;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetAddParticipantEvent;
use Proximum\Vimeet\Application\Event\User\ActivateAccountEvent;
use Proximum\Vimeet\Application\Event\User\CompleteProfileEvent;
use Proximum\Vimeet\Application\Components\Token\User\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * On Participant Added, a few event need to be dispatched
 * For example:
 *  - Email to complete profile
 *  - Email to activate Account
 *  - Email to warn the adder that the participant has been added to his/her sheet
 */
class OnParticipantAddedHandler
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var ActivateAccountTokenGenerator */
    private $activateAccountTokenGenerator;

    /**
     * @param EventDispatcherInterface      $eventDispatcher
     * @param ActivateAccountTokenGenerator $activateAccountTokenGenerator
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActivateAccountTokenGenerator $activateAccountTokenGenerator
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->activateAccountTokenGenerator = $activateAccountTokenGenerator;
    }

    public function handle(OnParticipantAdded $command): void
    {
        $sheet = $command->participant->getSheet();
        $event = $sheet->getEvent();
        $user = $command->participant->getUser();

        if (!$sheet->isOwner($user)) {
            // send to the guest
            if ($user->isActive()) {
                $this->sendCompleteProfileEvent($event, $user, $command->participant);
            } else {
                $this->sendActivationEvent($command->adder, $sheet, $user);
            }

            // send to the adder
            $this->sendActivationConfirmEvent($sheet, $command->participant, $command->adder);
        }
    }

    /**
     * Send activation email to the guest with activation link
     *
     * @param User  $adder
     * @param Sheet $sheet
     * @param User  $userAdded
     */
    private function sendActivationEvent(User $adder, Sheet $sheet, User $userAdded): void
    {
        $token = $this->activateAccountTokenGenerator->generate($userAdded, $sheet);
        $activateAccountEvent = new ActivateAccountEvent(
            $userAdded,
            $adder,
            $sheet->getEvent(),
            $token,
            $sheet
        );

        $this->eventDispatcher->dispatch(Events::USER_ACCOUNT_ACTIVATED, $activateAccountEvent);
    }

    /**
     * Send confirm invitation email send to the adder
     *
     * @param Sheet       $sheet
     * @param Participant $guest
     * @param User        $adder
     */
    private function sendActivationConfirmEvent(Sheet $sheet, Participant $guest, User $adder): void
    {
        $event = new SheetAddParticipantEvent($sheet, $guest, $adder);
        $this->eventDispatcher->dispatch(Events::SHEET_ADD_PARTICIPANT_CONFIRMATION, $event);
    }

    /**
     * @param Event       $event
     * @param User        $user
     * @param Participant $participant
     */
    private function sendCompleteProfileEvent(Event $event, User $user, Participant $participant): void
    {
        $completeProfileEvent = new CompleteProfileEvent(
            $user,
            $event,
            $participant,
            $user->getLocale()
        );
        $this->eventDispatcher->dispatch(Events::USER_PROFILE_COMPLETED, $completeProfileEvent);
    }
}
