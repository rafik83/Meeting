<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Helper\StringHelper;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\UserEvent;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface as UserEventExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserEventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type as ExtraDataType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConvertToParticipant
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

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
        UserRepositoryInterface $userRepository,
        SheetRepositoryInterface $sheetRepository,
        ParticipantRepositoryInterface $participantRepository,
        UserEventExtraDataRepositoryInterface $userEventExtraDataRepository,
        UserEventRepositoryInterface $userEventRepository,
        Synchronizer $synchronizer,
        EventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        $this->userRepository = $userRepository;
        $this->sheetRepository = $sheetRepository;
        $this->participantRepository = $participantRepository;
        $this->userEventExtraDataRepository = $userEventExtraDataRepository;
        $this->userEventRepository = $userEventRepository;
        $this->synchronizer = $synchronizer;
        $this->eventDispatcher = $eventDispatcher;
        $this->dateTime = $dateTime;
    }

    /**
     * @param Event $event
     * @param Type  $type
     * @param array $rawUser
     *
     * @return Participant
     */
    public function handle(Event $event, Type $type, array &$rawUser): ?Participant
    {
        $email = StringHelper::trimSpacesAndNonBreakSpaces($rawUser[LeniConstants::LENI_COL_EMAIL]);

        $user = $this->userRepository->findByEmail($email);

        if (null !== $this->userEventExtraDataRepository->getExtraDataForEventNameAndUser(
                $event,
                ExtraDataType::LENI_USER_ID,
                $user
            )
        ) {
            return null;
        }

        if (!$user instanceof User) {
            $locale = $event->getAvailableLocale($rawUser[LeniConstants::LENI_COL_LOCALE]);
            $user = $this->createUser($email, $locale);
        }

        $participant = $this->createSheetAndParticipant(
            $event,
            $type,
            $user
        );

        //$this->synchronizer->set($registrationTemplateData, $user);

        $this->eventDispatcher->dispatch(Events::SHEET_UPDATED, new SheetUpdatedEvent($participant->getSheet()));

        return $participant;
    }

    /**
     * @param string $email
     * @param string $locale
     *
     * @return User
     */
    private function createUser(string $email, string $locale): User
    {
        $user = new User($email, '', '', $locale);
        $user->setAccount(new User\Account());
        $this->userRepository->add($user);

        return $user;
    }

    /**
     * @param Event $event
     * @param Type  $type
     * @param User  $user
     *
     * @return Participant
     */
    private function createSheetAndParticipant(
        Event $event,
        Type $type,
        User $user
    ): Participant {
        $sheetTitle = '';
        $sheetTemplateData = [];
        $sheetRegistrationData = [];
        $participantRegistrationData = [];

        $sheet = $this->createSheet(
            $event,
            $type,
            $user,
            $sheetTitle,
            $sheetTemplateData,
            $sheetRegistrationData
        );

        $participant = $this->createParticipant($sheet, $user, $participantRegistrationData);

        $this->save($sheet, $participant);

        return $participant;
    }

    /**
     * @param Event  $event
     * @param Type   $type
     * @param User   $user
     * @param string $sheetTitle
     * @param array  $sheetTemplateData
     * @param array  $sheetRegistrationData
     *
     * @return Sheet
     */
    private function createSheet(
        Event $event,
        Type $type,
        User $user,
        string $sheetTitle,
        array $sheetTemplateData,
        array $sheetRegistrationData
    ): Sheet {
        $sheet = new Sheet(
            $event,
            $type,
            $sheetTemplateData,
            $user,
            $this->dateTime
        );
        $sheet->setRegistrationData($sheetRegistrationData);
        $sheet->setTitle($sheetTitle);
        $sheet->setImported(true);

        return $sheet;
    }

    /**
     * @param Sheet       $sheet
     * @param Participant $participant
     */
    private function save(Sheet $sheet, Participant $participant): void
    {
        $this->sheetRepository->add($sheet);
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
