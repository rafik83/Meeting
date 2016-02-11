<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Command\BaseHandler;
use Proximum\Vimeet\Application\Components\Participant\ParticipantManager;
use Proximum\Vimeet\Application\Components\Token\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Application\Event\ActivateAccountEvent;
use Proximum\Vimeet\Application\Exception\Data\RequiredDataEmptyException;
use Proximum\Vimeet\Application\Exception\Participant\EmailCanNotBeNullException;
use Proximum\Vimeet\Application\Exception\Sheet\ParticipantAlreadyExistException;
use Proximum\Vimeet\Bundle\InfrastructureBundle\Repository\ActivateAccountTokenRepository;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AddHandler extends BaseHandler
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var ParticipantManager
     */
    private $participantManager;

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var ActivateAccountTokenGenerator
     */
    private $activateAccountTokenGenerator;

    /**
     * @var ActivateAccountTokenRepository
     */
    private $activateAccountTokenRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param UserRepositoryInterface        $userRepository
     * @param ParticipantManager             $participantManager
     * @param ParticipantRepositoryInterface $participantRepository
     * @param ActivateAccountTokenGenerator  $activateAccountTokenGenerator
     * @param ActivateAccountTokenRepository $activateAccountTokenRepository
     * @param EventDispatcherInterface       $eventDispatcher
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        ParticipantManager $participantManager,
        ParticipantRepositoryInterface $participantRepository,
        ActivateAccountTokenGenerator $activateAccountTokenGenerator,
        ActivateAccountTokenRepository $activateAccountTokenRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->userRepository                 = $userRepository;
        $this->participantManager             = $participantManager;
        $this->participantRepository          = $participantRepository;
        $this->activateAccountTokenGenerator  = $activateAccountTokenGenerator;
        $this->activateAccountTokenRepository = $activateAccountTokenRepository;
        $this->eventDispatcher                = $eventDispatcher;
    }

    /**
     * @param Add $add
     *
     * @throws EmailCanNotBeNullException
     * @throws ParticipantAlreadyExistException
     * @throws RequiredDataEmptyException
     */
    public function handle(Add $add)
    {
        $addNewUser = false;

        // Check the constraint on the data (required) before
        $this->checkDataConstraint($add->data, $add->sheet->getType()->getParticipantTemplate());

        if ($add->email === null) {
            throw new EmailCanNotBeNullException();
        }

        // Try to find user
        $user = $this->userRepository->findByEmail($add->email);

        // Create user if not exists
        if (null === $user) {
            $user = new User($add->email, '', '', $add->locale);
            $this->userRepository->add($user);

            $addNewUser = true;
        }

        if ($add->sheet->hasUser($user)) {
            throw new ParticipantAlreadyExistException('User already linked to this sheet');
        }

        $participant = new Participant($add->sheet, $user, $add->data, $add->owner, false);

        // Find an order to attach the participant
        $orderToAttach = $this->participantManager->findOrdertoAttach($add->sheet);

        // If there is an order, attach it and active the participant
        if ($orderToAttach) {
            $participant->setOrder($orderToAttach);
            $participant->setActive(true);
        }

        // Add the new participant
        $this->participantRepository->add($participant);

        $add->participant = $participant;

        // Send activation event
        if ($addNewUser) {
            $this->sendActivationEvent($add, $user);
        }
    }

    /**
     * @param Add $add
     * @param User $user
     */
    private function sendActivationEvent(Add $add, User $user)
    {
        $activateAccountToken = $this->activateAccountTokenGenerator->generate($user, $add->sheet);

        $this->activateAccountTokenRepository->deleteAllForUser($user);
        $this->activateAccountTokenRepository->create($activateAccountToken);

        $activateAccountEvent = new ActivateAccountEvent(
            $user,
            $add->sheet->getEvent(),
            $activateAccountToken,
            $add->locale
        );

        $this->eventDispatcher->dispatch('activate_account', $activateAccountEvent);
    }
}
