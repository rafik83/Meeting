<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog\CatalogFilterViewsResult;

class FilteredFieldsQuery
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

    /** @var CatalogFilterViewsResult */
    public $catalogFilterViewsResult;

    /**
     * @param Event                    $event
     * @param array                    $filters
     * @param array                    $currentAggregations
     * @param CatalogFilterViewsResult $catalogFilterViewsResult
     * @param string                   $locale
     * @param array                    $availableSlotIds
     * @param array                    $sheetsToExclude
     */
    public function __construct(
        Event $event,
        array $filters,
        array $currentAggregations,
        CatalogFilterViewsResult $catalogFilterViewsResult,
        string $locale,
        array $availableSlotIds = [],
        array $sheetsToExclude = []
    ) {
        $this->event               = $event;
        $this->filters             = $filters;
        $this->currentAggregations = $currentAggregations;
        $this->locale              = $locale;
        $this->availableSlotIds    = $availableSlotIds;
        $this->sheetsToExclude     = $sheetsToExclude;
        $this->catalogFilterViewsResult = $catalogFilterViewsResult;
    }
}
