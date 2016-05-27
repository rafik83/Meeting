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
use Proximum\Vimeet\Application\Event\User\ActivateAccountEvent;
use Proximum\Vimeet\Application\Exception\Participant\AlreadyLinkedToASheetOfThisEventException;
use Proximum\Vimeet\Application\Exception\Participant\EmailCanNotBeNullException;
use Proximum\Vimeet\Application\Exception\Sheet\ParticipantAlreadyExistException;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\ActivateAccountTokenRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
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
     * @var ActivateAccountTokenRepositoryInterface
     */
    private $activateAccountTokenRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * AddHandler constructor.
     *
     * @param UserRepositoryInterface                 $userRepository
     * @param ParticipantRepositoryInterface          $participantRepository
     * @param SheetRepositoryInterface                $sheetRepository
     * @param TemplateDataFactory                     $templateDataFactory
     * @param ActivateAccountTokenGenerator           $activateAccountTokenGenerator
     * @param ActivateAccountTokenRepositoryInterface $activateAccountTokenRepository
     * @param EventDispatcherInterface                $eventDispatcher
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        ParticipantRepositoryInterface $participantRepository,
        SheetRepositoryInterface $sheetRepository,
        TemplateDataFactory $templateDataFactory,
        ActivateAccountTokenGenerator $activateAccountTokenGenerator,
        ActivateAccountTokenRepositoryInterface $activateAccountTokenRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->userRepository                 = $userRepository;
        $this->participantRepository          = $participantRepository;
        $this->sheetRepository                = $sheetRepository;
        $this->templateDataFactory            = $templateDataFactory;
        $this->activateAccountTokenGenerator  = $activateAccountTokenGenerator;
        $this->activateAccountTokenRepository = $activateAccountTokenRepository;
        $this->eventDispatcher                = $eventDispatcher;
    }

    /**
     * @param Add $add
     *
     * @throws EmailCanNotBeNullException
     * @throws ParticipantAlreadyExistException
     * @throws AlreadyLinkedToASheetOfThisEventException
     */
    public function handle(Add $add)
    {
        $addNewUser = false;

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

        if (false === $addNewUser && $add->sheet->hasUser($user)) {
            throw new ParticipantAlreadyExistException('User already linked to this sheet');
        }

        if (false === $addNewUser) {
            $sheets = $this->sheetRepository->getSheetByUserAndEvent($user, $add->sheet->getEvent());

            if (!empty($sheets)) {
                throw new AlreadyLinkedToASheetOfThisEventException('User already linked to a sheet on this event');
            }
        }


        $templateData = $this->templateDataFactory->createRegistrationFromType($add->sheet->getType(), $add->locale);

        foreach ($templateData->getObjects() as $object) {
            if ($object->hasTag(Tag::PARTICIPANT_FIRSTNAME) && $object instanceof Template\Object\EditableText) {
                $object->setContent($add->firstName);
            }

            if ($object->hasTag(Tag::PARTICIPANT_LASTNAME) && $object instanceof Template\Object\EditableText) {
                $object->setContent($add->lastName);
            }
        }

        $participant = new Participant($add->sheet, $user, $templateData->getData(), $add->owner, false);

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

        $this->eventDispatcher->dispatch('user_activate_account', $activateAccountEvent);
    }
}
