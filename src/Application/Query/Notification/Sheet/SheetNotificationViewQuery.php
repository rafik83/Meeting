<?php

namespace Proximum\Vimeet\Application\Query\Notification\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;

class SheetNotificationViewQuery
{
    /** @var Sheet */
    public $sheet;

    public function __construct(Sheet $sheet)
    {
        $this->sheet = $sheet;
    }
}
