<?php

namespace Proximum\Vimeet\Application\Query\User\UserEventListViews;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\ConditionRules\View\RuleInterface;
use Proximum\Vimeet\Domain\Model\Event;

class GetUserEventListViewsQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var string */
    public $page;

    /** @var string */
    public $locale;

    /** @var null|RuleInterface */
    public $rule;

    public function __construct(Event $event, int $page, string $locale, ?RuleInterface $rule)
    {
        $this->event = $event;
        $this->page = $page;
        $this->locale = $locale;
        $this->rule = $rule;
    }
}
