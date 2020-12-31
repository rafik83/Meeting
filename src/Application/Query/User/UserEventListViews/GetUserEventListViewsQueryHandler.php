<?php

namespace Proximum\Vimeet\Application\Query\User\UserEventListViews;

use Proximum\Vimeet\Application\Adapter\ElasticSearch\UserEventView\GetUserEventListViewsByEventInterface;
use Proximum\Vimeet\Domain\Repository\CategoryRepositoryInterface;

class GetUserEventListViewsQueryHandler
{
    /** @var GetUserEventListViewsByEventInterface */
    private $getUserEventViewsByEvent;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    public function __construct(
        GetUserEventListViewsByEventInterface $getUserEventViewsByEvent,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->getUserEventViewsByEvent = $getUserEventViewsByEvent;
        $this->categoryRepository = $categoryRepository;
    }

    public function handle(GetUserEventListViewsQuery $query): UserEventListViews
    {
        return new UserEventListViews(
            $this->getUserEventViewsByEvent->handle($query->event, $query->page, $query->locale, $query->rule),
            $this->categoryRepository->eventHasCategories($query->event)
        );
    }
}
