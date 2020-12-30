<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Catalog;

use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\Query\Catalog\SheetPreviewExternalViewQuery;
use Proximum\Vimeet\Application\Query\Catalog\SheetPreviewExternalViewQueryHandler;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;

class PaginatedSheetExternalViewQueryHandler
{
    /**
     * @var SheetSearchAdapterInterface
     */
    private $sheetSearchAdapter;

    /**
     * @var SheetPreviewExternalViewQueryHandler
     */
    private $sheetPreviewExternalViewQueryHandler;

    /**
     * PaginatedSheetExternalViewQueryHandler constructor.
     *
     * @param SheetSearchAdapterInterface          $sheetSearchAdapter
     * @param SheetPreviewExternalViewQueryHandler $sheetPreviewExternalViewQueryHandler
     */
    public function __construct(
        SheetSearchAdapterInterface $sheetSearchAdapter,
        SheetPreviewExternalViewQueryHandler $sheetPreviewExternalViewQueryHandler
    ) {
        $this->sheetSearchAdapter                   = $sheetSearchAdapter;
        $this->sheetPreviewExternalViewQueryHandler = $sheetPreviewExternalViewQueryHandler;
    }

    /**
     * @param PaginatedSheetExternalViewQuery $query
     *
     * @return PaginatedResult
     */
    public function handle(PaginatedSheetExternalViewQuery $query)
    {
        $paginatedResult = $this->sheetSearchAdapter->paginate(
            $query->event,
            $query->filters,
            Sheet\Constant::ORDER_BY_ALPHABETICAL,
            $query->page,
            $query->limit,
            $query->locale,
            true,
            []
        );

        $paginatedResult->results = array_map(
            $this->getSheetPreview($query),
            $paginatedResult->results
        );

        return $paginatedResult;
    }

    /**
     * @param PaginatedSheetExternalViewQuery $query
     *
     * @return \Closure
     */
    private function getSheetPreview(PaginatedSheetExternalViewQuery $query): \Closure
    {
        return function (Sheet $sheet) use ($query) {
            return $this->sheetPreviewExternalViewQueryHandler->handle(
                new SheetPreviewExternalViewQuery($sheet, $query->locale, $query->event, $query->showCategory)
            );
        };
    }
}
