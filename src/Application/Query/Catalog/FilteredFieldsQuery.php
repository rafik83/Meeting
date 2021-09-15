<?php

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\CatalogFilterViewsResult;

class FilteredFieldsQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var array */
    public $filters;

    /** @var array */
    public $currentAggregations;

    /** @var string */
    public $locale;

    /** @var array */
    public $availableSlotIds;

    /** @var array */
    public $sheetsToExclude;

    /** @var int[]|null */
    public $prefilteredSheetIds;

    /** @var CatalogFilterViewsResult */
    public $catalogFilterViewsResult;

    public function __construct(
        Event $event,
        array $filters,
        array $currentAggregations,
        CatalogFilterViewsResult $catalogFilterViewsResult,
        string $locale,
        array $availableSlotIds = [],
        array $sheetsToExclude = [],
        ?array $prefilteredSheetIds = null
    ) {
        $this->event = $event;
        $this->filters = $filters;
        $this->currentAggregations = $currentAggregations;
        $this->catalogFilterViewsResult = $catalogFilterViewsResult;
        $this->locale = $locale;
        $this->availableSlotIds = $availableSlotIds;
        $this->sheetsToExclude = $sheetsToExclude;
        $this->prefilteredSheetIds = $prefilteredSheetIds;
    }
}
