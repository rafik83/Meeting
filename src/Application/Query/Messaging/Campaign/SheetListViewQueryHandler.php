<?php

namespace Proximum\Vimeet\Application\Query\Messaging\Campaign;

use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;

class SheetListViewQueryHandler
{
    /** @var SheetSearchAdapterInterface */
    private $sheetSearchAdapter;

    public function __construct(SheetSearchAdapterInterface $sheetSearchAdapter)
    {
        $this->sheetSearchAdapter = $sheetSearchAdapter;
    }

    /**
     * @param SheetListViewQuery $query
     *
     * @return SheetListView[]
     */
    public function handle(SheetListViewQuery $query): array
    {
        if (empty($query->filters)) {
            return [];
        }

        $filters = array_merge($query->filters, $this->getDefaultFilters());

        return $this->sheetSearchAdapter->getSheetListView($query->event, $filters, $query->locale, $query->condition);
    }

    /**
     * @return array
     */
    private function getDefaultFilters(): array
    {
        return [
            'enabled' => true,
        ];
    }
}
