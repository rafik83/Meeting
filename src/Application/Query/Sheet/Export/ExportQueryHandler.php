<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Export;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;

class ExportQueryHandler
{
    /** @var SerializerAdapterInterface */
    private $serializerAdapter;

    /** @var SheetSearchAdapterInterface */
    private $sheetSearchAdapter;

    public function __construct(
        SerializerAdapterInterface $serializerAdapter,
        SheetSearchAdapterInterface $sheetSearchAdapter
    ) {
        $this->serializerAdapter  = $serializerAdapter;
        $this->sheetSearchAdapter = $sheetSearchAdapter;
    }

    public function handle(ExportQuery $exportQuery): string
    {
        $sheetIdsView = $this->sheetSearchAdapter->getSheetIdsView(
            $exportQuery->event,
            $exportQuery->filters,
            $exportQuery->locale,
            $exportQuery->condition
        );

        return $this->serializerAdapter->serialize($sheetIdsView, 'csv', [
            'locale' => $exportQuery->locale,
            'charset' => $exportQuery->charset,
            'event' => $exportQuery->event,
            'displayNomenclatureIds' => $exportQuery->displayNomenclatureIds,
            'csv_delimiter' => ';',
        ]);
    }
}
