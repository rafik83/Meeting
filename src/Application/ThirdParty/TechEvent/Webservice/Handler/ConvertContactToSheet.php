<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Handler;

use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipant;
use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipantHandler;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Data\Type as DataType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type as ExtraDataType;

class ConvertContactToSheet
{
    /** @var ExtraDataRepositoryInterface */
    private $userEventExtraDataRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var ConvertToParticipantHandler */
    private $convertToParticipantHandler;

    public function __construct(
        ExtraDataRepositoryInterface $userEventExtraDataRepository,
        ConvertToParticipantHandler $convertToParticipantHandler,
        \DateTimeInterface $dateTime
    ) {
        $this->userEventExtraDataRepository = $userEventExtraDataRepository;
        $this->dateTime = $dateTime;
        $this->convertToParticipantHandler = $convertToParticipantHandler;
    }

    public function handle(
        Event $event,
        Type $type,
        TemplateData $registrationTemplate,
        TemplateData $sheetTemplate,
        array $contact
    ): void {
        $registrationTemplate->clear();
        $sheetTemplate->clear();

        $participant = $this->convertToParticipantHandler->handle(new ConvertToParticipant(
            $event,
            $type,
            $contact[DataType::EMAIL],
            $event->getFallback(),
            [
                Tag::SHEET_TITLE => $contact[DataType::SHEET_TITLE] ?? null,
                Tag::PARTICIPANT_FIRSTNAME => $contact[DataType::FIRST_NAME] ?? null,
                Tag::PARTICIPANT_LASTNAME  => $contact[DataType::LAST_NAME] ?? null,
            ],
            $registrationTemplate,
            $sheetTemplate,
            ExtraDataType::IMPORTED_FROM_TECH_EVENT
        ));

        if ($participant instanceof Participant) {
            $this->userEventExtraDataRepository->add(
                new User\Event\ExtraData(
                    $participant->getUser(),
                    $event,
                    ExtraDataType::IMPORTED_FROM_TECH_EVENT,
                    $contact[DataType::ID_CONTACT],
                    $this->dateTime
                )
            );
        }
    }
}
