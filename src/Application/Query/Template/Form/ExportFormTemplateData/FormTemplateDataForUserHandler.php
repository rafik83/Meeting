<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Template\Form\ExportFormTemplateData;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Template\Form\FormTemplateDataQuery;
use Proximum\Vimeet\Application\Query\Template\Form\FormTemplateDataQueryHandler;
use Proximum\Vimeet\Application\View\Template\Form\ExportFormTemplateData\UserDataView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class FormTemplateDataForUserHandler
{
    /** @var FormTemplateDataQueryHandler */
    private $formTemplateDataQueryHandler;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    public function __construct(
        FormTemplateDataQueryHandler $formTemplateDataQueryHandler,
        SheetRepositoryInterface $sheetRepository,
        ParticipantInfoGuesser $participantInfoGuesser,
        SheetInfoGuesser $sheetInfoGuesser
    ) {
        $this->formTemplateDataQueryHandler = $formTemplateDataQueryHandler;
        $this->sheetRepository = $sheetRepository;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
    }

    public function handle(FormTemplateDataForUser $query): ?UserDataView
    {
        $sheet = null;
        $participant = null;
        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($query->user, $query->event);

        foreach ($sheets as $userSheet) {
            $userParticipant = $userSheet->getUserParticipant($query->user);

            // We need to find the oldest participant of the user (the original Participant)
            if ($userParticipant instanceof Participant
                && (!$participant instanceof Participant || $participant->getId() < $userParticipant->getId()))
            {
                $sheet = $userSheet;
                $participant = $userParticipant;
            }
        }

        if ($sheet === null || $participant === null) {
            return null;
        }

        $formTemplateData = $this->formTemplateDataQueryHandler->handle(new FormTemplateDataQuery(
            $query->formTemplate,
            $sheet,
            $participant,
            $query->locale
        ));

        $participantInfo = $this->participantInfoGuesser->guessParticipantInfos($participant, $query->locale);
        $sheetInfo = $this->sheetInfoGuesser->guessSheetInfos($sheet, $query->locale);

        $userData = [];

        foreach ($formTemplateData->getExportableObjects() as $object) {
            $userData[$object->getKey()] = $object->getExportableContent();
        }

        return new UserDataView(
            $query->user->getId(),
            $query->user->getEmail(),
            $participantInfo[Tag::PARTICIPANT_FIRSTNAME] ?? '',
            $participantInfo[Tag::PARTICIPANT_LASTNAME] ?? '',
            $participantInfo[Tag::PARTICIPANT_PHONE] ?? '',
            $participantInfo[Tag::PARTICIPANT_MOBILE] ?? '',
            $sheet->getId(),
            $sheet->getTitle(),
            $sheet->getTypeTitle($query->locale),
            $sheet->getCategoriesTitles($query->locale),
            $sheetInfo[Tag::SHEET_ADDRESS] ?? '',
            $sheetInfo[Tag::SHEET_ZIPCODE] ?? '',
            $sheetInfo[Tag::SHEET_CITY] ?? '',
            $sheetInfo[Tag::SHEET_COUNTRY] ?? '',
            $userData
        );
    }
}
