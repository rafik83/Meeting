<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
            $this->getUserEventViewsByEvent->handle($query->event, $query->page, $query->locale),
            $this->categoryRepository->eventHasCategories($query->event)
        );
    }
}
