<?php

namespace Proximum\Vimeet\Application\Query\Catalog\Export;

use Elastica\Result;
use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Catalog\GetViewedSheetsFromFilters;
use Proximum\Vimeet\Application\Components\Sheet\Nomenclature\NomenclatureItemsGetter;
use Proximum\Vimeet\Application\View\Catalog\Export\SheetListView;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\View\Sheet\SheetIdView;

class SheetsViewQueryHandler
{
    const TRANS_COL_TYPE = 'export.catalog.sheet.col.type';
    const TRANS_COL_CATEGORY = 'export.catalog.sheet.col.category';
    const TRANS_COL_PARTICIPANT_POSITION = 'export.catalog.sheet.col.participantPosition';

    /** @var SheetSearchAdapterInterface */
    private $sheetSearchAdapter;

    /** @var NomenclatureItemsGetter */
    private $nomenclatureItemsGetter;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var SheetViewQueryHandler */
    private $sheetViewQueryHandler;

    /** @var TranslatorInterface */
    private $translator;

    /** @var GetViewedSheetsFromFilters */
    private $getViewedSheetsFromFilters;

    public function __construct(
        TranslatorInterface $translator,
        SheetSearchAdapterInterface $sheetSearchAdapter,
        NomenclatureItemsGetter $nomenclatureItemsGetter,
        SheetRepositoryInterface $sheetRepository,
        SheetViewQueryHandler $sheetViewQueryHandler,
        GetViewedSheetsFromFilters $getViewedSheetsFromFilters
    ) {
        $this->translator = $translator;
        $this->sheetSearchAdapter = $sheetSearchAdapter;
        $this->nomenclatureItemsGetter = $nomenclatureItemsGetter;
        $this->sheetRepository = $sheetRepository;
        $this->sheetViewQueryHandler = $sheetViewQueryHandler;
        $this->getViewedSheetsFromFilters = $getViewedSheetsFromFilters;
    }

    /**
     * @param SheetsViewQuery $query
     *
     * @return SheetListView
     */
    public function handle(SheetsViewQuery $query): SheetListView
    {
        $results = $this->sheetSearchAdapter->find(
            $query->event,
            $query->filters,
            $query->filters['orderBy'],
            $query->locale,
            $this->nomenclatureItemsGetter->getNomenclatureItems(
                $query->sheet,
                $query->locale
            ),
            $query->availableSlotIds,
            $query->sheetsToExclude,
            $this->getViewedSheetsFromFilters->getFilteredByVisitSheetIds($query->filters, $query->user, $query->sheet)
        );

        $sheetIds = array_map(function (Result $result) {
            return new SheetIdView($result->getId());
        }, $results);

        $sheets = $this->sheetRepository->findSheets($sheetIds);

        $sheetViews = [];

        foreach ($sheets as $sheet) {
            $sheetViews[] = $this->sheetViewQueryHandler->handle(
                new SheetViewQuery(
                    $sheet,
                    $query->sheet,
                    $query->locale,
                    $query->event->getLocaleFallback(),
                    $query->isTypeColumn
                )
            );
        }

        foreach ($sheetViews as $sheetView) {
            $this->sheetViewQueryHandler->reMapFields($sheetView);
        }

        $sheetRegistrationFields = $this->sheetViewQueryHandler->getSheetRegistrationFields();
        $sheetFields             = $this->sheetViewQueryHandler->getSheetFields();

        $colParticipantPositionTranslation = $this->translator->trans(
            self::TRANS_COL_PARTICIPANT_POSITION,
            [],
            'exports',
            $query->locale
        );

        if ($query->isTypeColumn) {
            $colTranslation = $this->translator->trans(self::TRANS_COL_TYPE, [], 'exports', $query->locale);
        } else {
            $colTranslation = $this->translator->trans(self::TRANS_COL_CATEGORY, [], 'exports', $query->locale);
        }

        $sheetListView = new SheetListView(
            $sheetViews,
            $sheetRegistrationFields,
            $sheetFields,
            $colParticipantPositionTranslation,
            $colTranslation,
            $query->isTypeColumn
        );

        return $sheetListView;
    }
}
