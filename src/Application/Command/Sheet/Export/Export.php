<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Export;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class Export
{
    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /** @var bool */
    public $displayNomenclatureIds;

    /** @var int[] */
    public $sheetIds;

    /** @var Admin */
    public $admin;

    public function __construct(
        Event $event,
        Admin $admin,
        string $locale,
        array $sheetIds,
        bool $displayNomenclatureIds = false
    ) {
        $this->event = $event;
        $this->locale  = $locale;
        $this->sheetIds = $sheetIds;
        $this->displayNomenclatureIds = $displayNomenclatureIds;
        $this->admin = $admin;
    }
}
