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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Sheet\ExtraDataRepositoryInterface as SheetExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface as UserEventExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type as ExtraDataType;

class ImportSheetHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var SheetExtraDataRepositoryInterface */
    private $sheetExtraDataRepository;

    /** @var UserEventExtraDataRepositoryInterface */
    private $userEventExtraDataRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetExtraDataRepositoryInterface $sheetExtraDataRepository,
        UserEventExtraDataRepositoryInterface $userEventExtraDataRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->dateTime = $dateTime;
        $this->sheetRepository = $sheetRepository;
        $this->sheetExtraDataRepository = $sheetExtraDataRepository;
        $this->userEventExtraDataRepository = $userEventExtraDataRepository;
    }

    /**
     * @param Event            $event
     * @param Type             $type
     * @param RegistrationView $registrationView
     */
    public function handle(Event $event, Type $type, RegistrationView $registrationView): void
    {
        $user = new User(
            $registrationView->participantView->email,
            '',
            '',
            $registrationView->participantView->locale
        );

        $this->userEventExtraDataRepository->add(
            new User\Event\ExtraData(
                $user, $event, ExtraDataType::IMPORTED_FROM_COMEXPOSIUM, null, $this->dateTime
            )
        );

        // todo
        $data = [];

        $sheet = new Sheet($event, $type, $data, $user, $this->dateTime);
        $this->sheetRepository->add($sheet);

        $this->sheetExtraDataRepository->add(
            new Sheet\ExtraData(
                $sheet,
                SheetExtraDataType::COMEXPOSIUM_REGISTRATION_REFERENCE,
                $registrationView->reference,
                $this->dateTime
            )
        );
    }
}
