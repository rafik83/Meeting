<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Components\Token\User\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetAddParticipantEvent;
use Proximum\Vimeet\Application\Event\User\ActivateAccountEvent;
use Proximum\Vimeet\Application\Event\User\CompleteProfileEvent;
use Proximum\Vimeet\Application\Exception\Participant\AlreadyLinkedToASheetOfThisEventException;
use Proximum\Vimeet\Application\Exception\Participant\EmailCanNotBeNullException;
use Proximum\Vimeet\Application\Exception\Sheet\ParticipantAlreadyExistException;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AddHandler
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @var ActivateAccountTokenGenerator
     */
    private $activateAccountTokenGenerator;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * AddHandler constructor.
     *
     * @param UserRepositoryInterface        $userRepository
     * @param ParticipantRepositoryInterface $participantRepository
     * @param SheetRepositoryInterface       $sheetRepository
     * @param TemplateDataFactory            $templateDataFactory
     * @param ActivateAccountTokenGenerator  $activateAccountTokenGenerator
     * @param EventDispatcherInterface       $eventDispatcher
     * @param CartManager                    $cartManager
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        ParticipantRepositoryInterface $participantRepository,
        SheetRepositoryInterface $sheetRepository,
        TemplateDataFactory $templateDataFactory,
        ActivateAccountTokenGenerator $activateAccountTokenGenerator,
        EventDispatcherInterface $eventDispatcher,
        CartManager $cartManager
    ) {
        $this->userRepository                = $userRepository;
        $this->participantRepository         = $participantRepository;
        $this->sheetRepository               = $sheetRepository;
        $this->templateDataFactory           = $templateDataFactory;
        $this->activateAccountTokenGenerator = $activateAccountTokenGenerator;
        $this->eventDispatcher               = $eventDispatcher;
        $this->cartManager                   = $cartManager;
    }

    /**
     * @param Add $add
     *
     * @return AddResult
     * @throws AlreadyLinkedToASheetOfThisEventException
     * @throws EmailCanNotBeNullException
     * @throws ParticipantAlreadyExistException
     */
    public function handle(Add $add)
    {
        if ($add->email === null) {
            throw new EmailCanNotBeNullException();
        }

        $user = $this->findOrCreateUser($add);

        if ($add->sheet->hasUserParticipant($user)) {
            throw new ParticipantAlreadyExistException('User already linked to this sheet');
        }

        if (!empty($this->sheetRepository->getSheetsByUserAndEventWhereUserIsParticipant(
            $user,
            $add->sheet->getEvent()
        ))) {
            throw new AlreadyLinkedToASheetOfThisEventException('User already linked to a sheet on this event');
        }

        // Create participant
        $participant = $this->createAndFillParticipant($add, $user);

        // Update cart
        $this->cartManager->updateParticipantsQuantity($add->sheet);

        if (!$add->sheet->isOwner($user)) {
            if ($user->isActive()) {
                $this->sendCompleteProfileEvent($add, $user, $participant);
            } else {
                $this->sendActivationEvent($add, $user); // send to the guest
                $this->sendActivationConfirmEvent($add, $user); // send to the owner
            }
        }

        return new AddResult($participant);
    }

    /**
     * Send activation email to the guest with activation link
     *
     * @param Add  $add
     * @param User $user
     */
    private function sendActivationEvent(Add $add, User $user)
    {
        $token = $this->activateAccountTokenGenerator->generate($user, $add->sheet);
        $event = new ActivateAccountEvent($user, $add->sheet->getEvent(), $token, $add->locale, $add->adder);
        $this->eventDispatcher->dispatch(Events::USER_ACCOUNT_ACTIVATED, $event);
    }

    /**
     * Send confirm invitation email send to the sheet owner
     *
     * @param Add  $add
     * @param User $guest
     */
    private function sendActivationConfirmEvent(Add $add, User $guest)
    {
        $event = new SheetAddParticipantEvent($add->sheet, $guest, $add->adder);
        $this->eventDispatcher->dispatch(Events::SHEET_ADD_PARTICIPANT_CONFIRMATION, $event);
    }

    /**
     * @param Add         $add
     * @param User        $user
     * @param Participant $participant
     */
    private function sendCompleteProfileEvent(Add $add, User $user, Participant $participant)
    {
        $event = new CompleteProfileEvent($user, $add->event, $participant, $add->locale);
        $this->eventDispatcher->dispatch(Events::USER_PROFILE_COMPLETED, $event);
    }

    /**
     * @param Add $add
     *
     * @return User
     */
    private function findOrCreateUser(Add $add)
    {
        $user = $this->userRepository->findByEmail($add->email);

        if (null === $user) {
            $user = new User($add->email, '', '', $add->locale);
            $this->userRepository->add($user);
        }

        return $user;
    }

    /**
     * @param Add  $add
     * @param User $user
     *
     * @return Participant
     */
    protected function createAndFillParticipant(Add $add, User $user)
    {
        $templateData = $this->templateDataFactory->createRegistrationFromType($add->sheet->getType(), $add->locale);
        $templateData->setTaggedData([
            Tag::PARTICIPANT_FIRSTNAME => $add->firstName,
            Tag::PARTICIPANT_LASTNAME  => $add->lastName,
        ]);

        $participant = new Participant($add->sheet, $user, $templateData->getData(), false);
        $this->participantRepository->add($participant);

        $add->sheet->addParticipant($participant);

        return $participant;
    }
}
