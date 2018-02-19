<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler;

use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Sheet\SheetExtraDataType;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\RegistrationView;
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

class ImportSheetHandler
{
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

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        UserRepositoryInterface $userRepository,
        SheetRepositoryInterface $sheetRepository,
        ParticipantRepositoryInterface $participantRepository,
        SheetExtraDataRepositoryInterface $sheetExtraDataRepository,
        UserEventExtraDataRepositoryInterface $userEventExtraDataRepository,
        UserEventRepositoryInterface $userEventRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->dateTime = $dateTime;
        $this->sheetRepository = $sheetRepository;
        $this->sheetExtraDataRepository = $sheetExtraDataRepository;
        $this->userEventExtraDataRepository = $userEventExtraDataRepository;
        $this->userRepository = $userRepository;
        $this->userEventRepository = $userEventRepository;
        $this->participantRepository = $participantRepository;
    }

    /**
     * @param Event            $event
     * @param Type             $type
     * @param RegistrationView $registrationView
     *
     * @param TemplateData     $templateData
     *
     * @return Sheet
     */
    public function handle(
        Event $event,
        Type $type,
        RegistrationView $registrationView,
        TemplateData $templateData
    ): Sheet {
        $email = StringHelper::trimSpacesAndNonBreakSpaces($registrationView->participantView->email);

        $user = $this->userRepository->findByEmail($email);

        if (!$user instanceof User) {
            $user = $this->createUser($event, $email, $registrationView->participantView->locale);
        }

        return $this->createSheet($event, $type, $user, $registrationView, $templateData);
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
     * @param TemplateData     $templateData
     *
     * @return Sheet
     */
    private function createSheet(
        Event $event,
        Type $type,
        User $user,
        RegistrationView $registrationView,
        TemplateData $templateData
    ): Sheet {
        // todo
        $data = [];

        $sheet = new Sheet($event, $type, $data, $user, $this->dateTime);
        $sheet->setImported(true);
        $this->sheetRepository->add($sheet);

        // todo
        $participantData = [];

        $participant = new Participant($sheet, $user, $participantData, false);
        $participant->setImported(true);
        $this->participantRepository->add($participant);

        $this->sheetExtraDataRepository->add(
            new Sheet\ExtraData(
                $sheet,
                SheetExtraDataType::COMEXPOSIUM_REGISTRATION_REFERENCE,
                $registrationView->reference,
                $this->dateTime
            )
        );

        $this->userEventRepository->add(new UserEvent($user, $event, $type));

        return $sheet;
    }
}
