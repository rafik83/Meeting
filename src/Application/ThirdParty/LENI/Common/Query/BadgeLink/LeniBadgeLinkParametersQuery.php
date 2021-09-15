<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\BadgeLink;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class LeniBadgeLinkParametersQuery implements Query
{
    /** @var Event */
    public $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
