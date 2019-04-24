<?php

namespace Proximum\Vimeet\Application\Query\Badge\ScannedUserEventProfile;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class GetScannedUserEventProfileQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var string */
    public $identifier;

    public function __construct(Event $event, string $identifier)
    {
        $this->event = $event;
        $this->identifier = $identifier;
    }
}
