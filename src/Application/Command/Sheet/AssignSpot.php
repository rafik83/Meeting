<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class AssignSpot
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var string
     */
    public $spotCode;

    /**
     * @param Event  $event
     * @param Sheet  $sheet
     * @param string $spotCode
     */
    public function __construct(Event $event, Sheet $sheet, $spotCode)
    {
        $this->event    = $event;
        $this->sheet    = $sheet;
        $this->spotCode = $spotCode;
    }
}
