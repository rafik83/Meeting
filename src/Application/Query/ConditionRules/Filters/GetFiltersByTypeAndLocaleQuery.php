<?php

namespace Proximum\Vimeet\Application\Query\ConditionRules\Filters;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class GetFiltersByTypeAndLocaleQuery implements Query
{
    /** @var null|Event */
    public $event;

    /** @var string */
    public $type;

    /** @var string */
    public $locale;

    public function __construct(Event $event, string $type, string $locale)
    {
        $this->event = $event;
        $this->type = $type;
        $this->locale = $locale;
    }
}
