<?php

namespace Proximum\Vimeet\Application\ThirdParty\Vianeo\Command;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\ThirdParty\Vianeo\Exception\VianeoSheetNotRegisteredException;
use Proximum\Vimeet\Application\ThirdParty\Vianeo\Sheet\VianeoSheetInfoGuesser;
use Proximum\Vimeet\Application\ThirdParty\Vianeo\Sheet\VianeoTemplateTag;
use Proximum\Vimeet\Application\ThirdParty\Vianeo\View\VianeoSheetView;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class VianeoGetSheetDataHandler
{
    /** @var VianeoSheetInfoGuesser */
    private $vianeoSheetInfoGuesser;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var SerializerAdapterInterface */
    private $serializerAdapter;

    /**
     * @param VianeoSheetInfoGuesser     $vianeoSheetInfoGuesser
     * @param ParticipantInfoGuesser     $participantInfoGuesser
     * @param SerializerAdapterInterface $serializerAdapter
     */
    public function __construct(
        VianeoSheetInfoGuesser $vianeoSheetInfoGuesser,
        ParticipantInfoGuesser $participantInfoGuesser,
        SerializerAdapterInterface $serializerAdapter
    ) {
        $this->vianeoSheetInfoGuesser = $vianeoSheetInfoGuesser;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->serializerAdapter = $serializerAdapter;
    }

    /**
     * @param VianeoGetSheetData $vianeoGetSheetData
     *
     * @throws VianeoSheetNotRegisteredException
     *
     * @return string payload in json
     */
    public function handle(VianeoGetSheetData $vianeoGetSheetData): string
    {
        $sheet = $vianeoGetSheetData->sheet;
        $locale = $vianeoGetSheetData->locale;

        $sheetData = $this->vianeoSheetInfoGuesser->handle($sheet, $locale);

        if (true !== $sheetData[VianeoTemplateTag::VIANEO_REGISTRATION]) {
            throw new VianeoSheetNotRegisteredException();
        }

        $firstParticipant = $sheet->getFirstParticipant();
        $participantData = $this->participantInfoGuesser->guessParticipantInfos($firstParticipant, $locale);

        $vianeoSheetView = new VianeoSheetView(
            $sheet->getId(),
            $firstParticipant->getEmail(),
            sprintf(
                '%s %s',
                $participantData[Tag::PARTICIPANT_FIRSTNAME] ?? '',
                $participantData[Tag::PARTICIPANT_LASTNAME] ?? ''
            ),
            $sheetData[Tag::SHEET_TITLE] ?? '',
            $sheetData[VianeoTemplateTag::VIANEO_CATEGORY],
            $sheetData[VianeoTemplateTag::VIANEO_PROJECT_SUMMARY] ?? '',
            $participantData[Tag::PARTICIPANT_GENDER] ?? '',
            $participantData[Tag::PARTICIPANT_FIRSTNAME] ?? '',
            $participantData[Tag::PARTICIPANT_LASTNAME] ?? '',
            $participantData[Tag::PARTICIPANT_POSITION] ?? '',
            $participantData[Tag::PARTICIPANT_PHONE] ?? ''
        );

        return $this->serializerAdapter->serialize($vianeoSheetView, 'json');
    }
}
