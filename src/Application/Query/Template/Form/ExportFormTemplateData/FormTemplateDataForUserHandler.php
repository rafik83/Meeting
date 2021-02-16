<?php

namespace Proximum\Vimeet\Application\Query\Template\Form\ExportFormTemplateData;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Template\Form\FormTemplateDataQuery;
use Proximum\Vimeet\Application\Query\Template\Form\FormTemplateDataQueryHandler;
use Proximum\Vimeet\Application\View\Template\Form\ExportFormTemplateData\UserDataView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\User\Sheet\FirstParticipantSheetOfUserGetter;

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

    /** @var FirstParticipantSheetOfUserGetter */
    private $firstParticipantSheetOfUserGetter;

    public function __construct(
        FormTemplateDataQueryHandler $formTemplateDataQueryHandler,
        SheetRepositoryInterface $sheetRepository,
        ParticipantInfoGuesser $participantInfoGuesser,
        SheetInfoGuesser $sheetInfoGuesser,
        FirstParticipantSheetOfUserGetter $firstParticipantSheetOfUserGetter
    ) {
        $this->formTemplateDataQueryHandler = $formTemplateDataQueryHandler;
        $this->sheetRepository = $sheetRepository;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
        $this->firstParticipantSheetOfUserGetter = $firstParticipantSheetOfUserGetter;
    }

    public function handle(FormTemplateDataForUser $query): ?UserDataView
    {
        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($query->user, $query->event);
        $sheet = $this->firstParticipantSheetOfUserGetter->getFirstParticipantSheet($query->user, $sheets);

        if (!$sheet instanceof Sheet) {
            return null;
        }

        $participant = $sheet->getUserParticipant($query->user);

        if (!$participant instanceof Participant) {
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
