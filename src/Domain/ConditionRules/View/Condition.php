<?php

namespace Proximum\Vimeet\Domain\ConditionRules\View;

use Proximum\Vimeet\Domain\ConditionRules\View\LogicalOperator\LogicalOperatorInterface;
use Proximum\Vimeet\Domain\Model\Event;

class Condition implements RuleInterface
{
    /** @var Event */
    private $event;

    /** @var string */
    private $locale;

    /** @var LogicalOperatorInterface */
    private $logicalOperator;

    /** @var RuleInterface[] */
    private $rules;

    public function __construct(Event $event, string $locale, LogicalOperatorInterface $logicalOperator, array $rules)
    {
        $this->event = $event;
        $this->locale = $locale;
        $this->logicalOperator = $logicalOperator;
        $this->rules = $rules;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getLogicalOperator(): LogicalOperatorInterface
    {
        return $this->logicalOperator;
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}
