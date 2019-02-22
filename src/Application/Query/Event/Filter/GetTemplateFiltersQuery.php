<?php

namespace Proximum\Vimeet\Application\Query\Event\Filter;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class GetTemplateFiltersQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var string */
    public $informationType;

    public function __construct(Event $event, string $informationType)
    {
        $this->event = $event;
        $this->informationType = $informationType;
    }
}
