<?php

namespace Proximum\Vimeet\Application\Query\Notification\Package;

use Proximum\Vimeet\Domain\Model\Sheet;

class NoOrderNotificationViewQuery
{
    /** @var Sheet */
    public $sheet;

    public function __construct(Sheet $sheet)
    {
        $this->sheet  = $sheet;
    }
}
