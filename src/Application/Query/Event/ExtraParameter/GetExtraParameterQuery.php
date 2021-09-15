<?php

namespace Proximum\Vimeet\Application\Query\Event\ExtraParameter;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class GetExtraParameterQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var string */
    public $type;

    public function __construct(Event $event, string $type)
    {
        $this->event = $event;
        $this->type = $type;
    }
}
