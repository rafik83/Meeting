<?php

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\ConditionRules\View\RuleInterface;
use Proximum\Vimeet\Domain\Model\Event;

class GetSheetIdsByFiltersQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var array */
    public $filters;

    /** @var string */
    public $locale;

    /** @var null|RuleInterface */
    public $condition;

    public function __construct(
        Event $event,
        array $filters,
        string $locale,
        ?RuleInterface $condition = null
    ) {
        $this->event = $event;
        $this->filters = $filters;
        $this->locale = $locale;
        $this->condition = $condition;
    }
}
