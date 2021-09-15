<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Export;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\View\Sheet\SheetIdsView;

class ExportQueryHandler
{
    /** @var SerializerAdapterInterface */
    private $serializerAdapter;

    public function __construct(
        SerializerAdapterInterface $serializerAdapter
    ) {
        $this->serializerAdapter  = $serializerAdapter;
    }

    public function handle(ExportQuery $exportQuery): string
    {
        $sheetIdsView = new SheetIdsView($exportQuery->sheetIds);

        return $this->serializerAdapter->serialize($sheetIdsView, 'csv', [
            'locale' => $exportQuery->locale,
            'charset' => Charset::WINDOWS_1252,
            'event' => $exportQuery->event,
            'displayNomenclatureIds' => $exportQuery->displayNomenclatureIds,
            'csv_delimiter' => ';',
        ]);
    }
}
