<?php

namespace Proximum\Vimeet\Application\Query\User\UserEventListViews;

use Proximum\Vimeet\Domain\Model\PaginatedResult;

class UserEventListViews
{
    /** @var PaginatedResult */
    public $paginatedResult;

    /** @var bool */
    public $eventHasCategories;

    public function __construct(PaginatedResult $paginatedResult, bool $eventHasCategories)
    {
        $this->paginatedResult = $paginatedResult;
        $this->eventHasCategories = $eventHasCategories;
    }
}
