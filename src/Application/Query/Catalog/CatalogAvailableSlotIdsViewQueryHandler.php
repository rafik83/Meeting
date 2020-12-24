<?php

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantQuery;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantQueryHandler;
use Proximum\Vimeet\Application\View\Catalog\CatalogAvailableSlotIdsView;
use Proximum\Vimeet\Domain\Catalog\SearchFields;
use Proximum\Vimeet\Domain\Model\Catalog\Internal\CatalogConstant;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class CatalogAvailableSlotIdsViewQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var AvailableSlotsByParticipantQueryHandler */
    private $availableSlotsByParticipantQueryHandler;

    /**
     * @param SheetRepositoryInterface                $sheetRepository
     * @param AvailableSlotsByParticipantQueryHandler $availableSlotsByParticipantQueryHandler
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        AvailableSlotsByParticipantQueryHandler $availableSlotsByParticipantQueryHandler
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->availableSlotsByParticipantQueryHandler = $availableSlotsByParticipantQueryHandler;
    }

    /**
     * @param CatalogAvailableSlotIdsViewQuery $query
     *
     * @return CatalogAvailableSlotIdsView
     */
    public function handle(CatalogAvailableSlotIdsViewQuery $query): CatalogAvailableSlotIdsView
    {
        $availableSlotIds = [];
        $sheetsToExclude  = [];

        if ($query->sheet->hasUserParticipant($query->user)
            && isset($query->filters[SearchFields::FILTER_AVAILABLE_SLOT_IDS])
            && !empty($query->filters[SearchFields::FILTER_AVAILABLE_SLOT_IDS])
            && (
                CatalogConstant::AVAILABLE_SLOT_IDS_FILTER_AVAILABLE === $query->filters[SearchFields::FILTER_AVAILABLE_SLOT_IDS]
                || CatalogConstant::AVAILABLE_SLOT_IDS_FILTER_SLOT === $query->filters[SearchFields::FILTER_AVAILABLE_SLOT_IDS]
            )
        ) {
            $availableSlotIds = $this->availableSlotsByParticipantQueryHandler->handle(
                new AvailableSlotsByParticipantQuery(
                    $query->event,
                    $query->sheet->getUserParticipant($query->user)
                )
            );
            $sheetsToExclude = $this->sheetRepository->getSheetsWithRequestWithSheet($query->sheet);

            // Add the current Sheet to the list of sheet to exclude as it is not possible to ask yourself to meet
            $sheetsToExclude[] = $query->sheet;
        }

        return new CatalogAvailableSlotIdsView($availableSlotIds, $sheetsToExclude);
    }
}
