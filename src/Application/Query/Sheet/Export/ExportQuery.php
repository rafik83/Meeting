<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Export;

use Proximum\Vimeet\Domain\Model\Event;

class ExportQuery
{
    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /** @var bool */
    public $displayNomenclatureIds;

    /** @var int[] */
    public $sheetIds;

    public function __construct(
        Event $event,
        string $locale,
        array $sheetIds,
        bool $displayNomenclatureIds = false
    ) {
        $this->event = $event;
        $this->locale  = $locale;
        $this->sheetIds = $sheetIds;
        $this->displayNomenclatureIds = $displayNomenclatureIds;
    }
}
