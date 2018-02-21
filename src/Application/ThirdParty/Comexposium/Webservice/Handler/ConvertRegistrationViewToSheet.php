<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Sheet\SheetExtraDataType;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\RegistrationView;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Helper\StringHelper;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\UserEvent;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Sheet\ExtraDataRepositoryInterface as SheetExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface as UserEventExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserEventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type as ExtraDataType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConvertRegistrationViewToSheet
{
    /** @var SheetAndParticipantTemplateDataHandler */
    private $sheetAndParticipantTemplateDataHandler;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var SheetExtraDataRepositoryInterface */
    private $sheetExtraDataRepository;

    /** @var UserEventExtraDataRepositoryInterface */
    private $userEventExtraDataRepository;

    /** @var UserEventRepositoryInterface */
    private $userEventRepository;

    /** @var Synchronizer */
    private $synchronizer;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        SheetAndParticipantTemplateDataHandler $sheetAndParticipantTemplateDataHandler,
        UserRepositoryInterface $userRepository,
        SheetRepositoryInterface $sheetRepository,
        ParticipantRepositoryInterface $participantRepository,
        SheetExtraDataRepositoryInterface $sheetExtraDataRepository,
        UserEventExtraDataRepositoryInterface $userEventExtraDataRepository,
        UserEventRepositoryInterface $userEventRepository,
        Synchronizer $synchronizer,
        EventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        $this->sheetAndParticipantTemplateDataHandler = $sheetAndParticipantTemplateDataHandler;
        $this->userRepository = $userRepository;
        $this->sheetRepository = $sheetRepository;
        $this->participantRepository = $participantRepository;
        $this->sheetExtraDataRepository = $sheetExtraDataRepository;
        $this->userEventExtraDataRepository = $userEventExtraDataRepository;
        $this->userEventRepository = $userEventRepository;
        $this->synchronizer = $synchronizer;
        $this->eventDispatcher = $eventDispatcher;
        $this->dateTime = $dateTime;
    }

    /**
     * @param Event            $event
     * @param Type             $type
     * @param RegistrationView $registrationView
     * @param TemplateData     $registrationTemplateData
     *
     * @return Sheet
     */
    public function handle(
        Event $event,
        Type $type,
        RegistrationView $registrationView,
        TemplateData $registrationTemplateData
    ): Sheet {
        $email = StringHelper::trimSpacesAndNonBreakSpaces($registrationView->participantView->email);

        $user = $this->userRepository->findByEmail($email);

        if (!$user instanceof User) {
            $user = $this->createUser($event, $email, $registrationView->participantView->locale);
        }

        $sheet = $this->createSheetAndParticipant($event, $type, $user, $registrationView, $registrationTemplateData);

        $this->synchronizer->set($registrationTemplateData, $user);

        $sheetUpdatedEvent = new SheetUpdatedEvent($sheet);
        $this->eventDispatcher->dispatch(Events::SHEET_UPDATED, $sheetUpdatedEvent);

        return $sheet;
    }

    /**
     * @param Event  $event
     * @param string $email
     * @param string $locale
     *
     * @return User
     */
    private function createUser(Event $event, string $email, string $locale): User
    {
        $user = new User($email, '', '', $locale);
        $user->setAccount(new User\Account());
        $user->welcome();

        $this->userRepository->add($user);

        $this->userEventExtraDataRepository->add(
            new User\Event\ExtraData(
                $user, $event, ExtraDataType::IMPORTED_FROM_COMEXPOSIUM, null, $this->dateTime
            )
        );

        return $user;
    }

    /**
     * @param Event            $event
     * @param Type             $type
     * @param User             $user
     * @param RegistrationView $registrationView
     * @param TemplateData     $registrationTemplateData
     *
     * @return Sheet
     */
    private function createSheetAndParticipant(
        Event $event,
        Type $type,
        User $user,
        RegistrationView $registrationView,
        TemplateData $registrationTemplateData
    ): Sheet {
        $sheetAndParticipantTemplateDataView = $this->sheetAndParticipantTemplateDataHandler->handle(
            $registrationView,
            $registrationTemplateData
        );

        $sheet = $this->createSheet(
            $sheetAndParticipantTemplateDataView->sheetTitle,
            $event,
            $type,
            $user,
            $sheetAndParticipantTemplateDataView->sheetRegistrationData
        );

        $participant = $this->createParticipant($sheet, $user, $sheetAndParticipantTemplateDataView->participantRegistrationData);

        $this->save($registrationView->reference, $sheet, $participant);

        return $sheet;
    }

    /**
     * @param string $sheetTitle
     * @param Event  $event
     * @param Type   $type
     * @param User   $user
     * @param array  $sheetRegistrationData
     *
     * @return Sheet
     */
    private function createSheet(
        string $sheetTitle,
        Event $event,
        Type $type,
        User $user,
        array &$sheetRegistrationData
    ): Sheet {
        $sheet = new Sheet(
            $event,
            $type,
            [],
            $user,
            $this->dateTime
        );
        $sheet->setRegistrationData($sheetRegistrationData);
        $sheet->setTitle($sheetTitle);
        $sheet->setImported(true);

        return $sheet;
    }

    /**
     * @param string      $reference Comexposium reference
     * @param Sheet       $sheet
     * @param Participant $participant
     */
    private function save(string $reference, Sheet $sheet, Participant $participant): void
    {
        $this->sheetRepository->add($sheet);

        $this->sheetExtraDataRepository->add(
            new Sheet\ExtraData(
                $sheet,
                SheetExtraDataType::COMEXPOSIUM_REGISTRATION_REFERENCE,
                $reference,
                $this->dateTime
            )
        );

        $this->participantRepository->add($participant);
        $this->userEventRepository->add(new UserEvent($participant->getUser(), $sheet->getEvent(), $sheet->getType()));
    }

    /**
     * @param Sheet $sheet
     * @param User  $user
     * @param array $participantRegistrationData
     *
     * @return Participant
     */
    private function createParticipant(Sheet $sheet, User $user, array &$participantRegistrationData): Participant
    {
        $participant = new Participant(
            $sheet,
            $user,
            $participantRegistrationData,
            false
        );
        $participant->setImported(true);

        return $participant;
    }
}
