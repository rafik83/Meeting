<?php

namespace Proximum\Vimeet\Application\Query\PromotionCode\Batch\PromotionCodeGroupList;

use Proximum\Vimeet\Domain\Model\Event;

class GetListView
{
    /** @var Event */
    public $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
