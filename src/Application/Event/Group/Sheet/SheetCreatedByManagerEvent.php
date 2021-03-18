<?php

namespace Proximum\Vimeet\Application\Event\Group\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\EventDispatcher;

class SheetCreatedByManagerEvent extends EventDispatcher\Event
{
    /** @var Sheet */
    public $sheet;

    /** @var \DateTimeInterface */
    public $date;

    /**
     * @param Sheet              $sheet
     * @param \DateTimeInterface $date
     */
    public function __construct(Sheet $sheet, \DateTimeInterface $date)
    {
        $this->sheet = $sheet;
        $this->date  = $date;
    }
}
