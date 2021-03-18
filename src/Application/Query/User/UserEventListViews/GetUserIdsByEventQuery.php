<?php

namespace Proximum\Vimeet\Application\Query\User\UserEventListViews;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\ConditionRules\View\Condition;
use Proximum\Vimeet\Domain\Model\Event;

class GetUserIdsByEventQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /** @var null|Condition */
    public $condition;

    public function __construct(Event $event, string $locale, ?Condition $condition)
    {
        $this->event = $event;
        $this->locale = $locale;
        $this->condition = $condition;
    }
}
