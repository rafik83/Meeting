<?php

namespace Proximum\Vimeet\Application\Adapter\ElasticSearch\UserEventView;

use Proximum\Vimeet\Domain\ConditionRules\View\Condition;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;

interface GetUserEventListViewsByEventInterface
{
    public const RESULTS_NUMBER_BY_PAGE = 100;

    public function handle(Event $event, int $page, string $locale, ?Condition $condition): PaginatedResult;
}
