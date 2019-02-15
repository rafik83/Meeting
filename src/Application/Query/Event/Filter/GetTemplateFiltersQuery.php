<?php

namespace Proximum\Vimeet\Application\Query\Event\Filter;

use Proximum\Vimeet\Application\Query\Query;

class GetTemplateFiltersQuery implements Query
{
    /** @var int */
    public $eventId;

    /** @var string */
    public $informationType;

    public function __construct(int $eventId, string $informationType)
    {
        $this->eventId = $eventId;
        $this->informationType = $informationType;
    }
}
