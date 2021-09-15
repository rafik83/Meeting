<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Batch;

use Proximum\Vimeet\Application\Command\Sheet\AbstractBatch;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class PrintPlanning extends AbstractBatch
{
    /** @var Admin */
    public $admin;

    /** @var string */
    public $orderBy;

    /** @var string */
    public $locale;

    /** @var Event */
    public $event;

    /** @var string */
    public $printOption;

    public function __construct(
        Event $event,
        array $sheetIds,
        Admin $admin,
        string $orderBy,
        string $locale,
        string $printOption
    ) {
        $this->event = $event;
        $this->ids = $sheetIds;
        $this->admin = $admin;
        $this->orderBy = $orderBy;
        $this->locale = $locale;
        $this->printOption = $printOption;
    }
}
