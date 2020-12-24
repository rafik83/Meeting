<?php

namespace Proximum\Vimeet\Application\Query\ConditionRules\Rules;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class GetConditionRulesQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /** @var array */
    public $rules;

    public function __construct(Event $event, string $locale, array $rules)
    {
        $this->event = $event;
        $this->locale = $locale;
        $this->rules = $rules;
    }
}
