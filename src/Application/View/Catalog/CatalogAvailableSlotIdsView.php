<?php

namespace Proximum\Vimeet\Application\View\Catalog;

class CatalogAvailableSlotIdsView
{
    /** @var array */
    public $availableSlotIds;

    /** @var array */
    public $sheetsToExclude;

    /**
     * @param array $availableSlotIds
     * @param array $sheetsToExclude
     */
    public function __construct(array $availableSlotIds = [], array $sheetsToExclude = [])
    {
        $this->availableSlotIds = $availableSlotIds;
        $this->sheetsToExclude = $sheetsToExclude;
    }
}
