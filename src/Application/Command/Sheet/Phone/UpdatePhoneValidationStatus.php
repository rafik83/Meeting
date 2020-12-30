<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Phone;

use Proximum\Vimeet\Domain\Model\Event;

class UpdatePhoneValidationStatus
{
    /** @var Event */
    public $event;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
