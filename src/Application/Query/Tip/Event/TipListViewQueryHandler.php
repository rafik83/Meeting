<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Tip\Event;

use Proximum\Vimeet\Application\View\Tip\Event\TipView;
use Proximum\Vimeet\Application\View\Tip\TipListView;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class TipListViewQueryHandler
{
    /** @var TipRepositoryInterface */
    private $tipRepository;

    /**
     * TipListViewQueryHandler constructor.
     *
     * @param TipRepositoryInterface $tipRepository
     */
    public function __construct(TipRepositoryInterface $tipRepository)
    {
        $this->tipRepository = $tipRepository;
    }

    /**
     * @param TipListViewQuery $query
     *
     * @return TipListView
     */
    public function handle(TipListViewQuery $query)
    {
        $tips = $this->tipRepository->paginateByEvent($query->event, $query->page, $query->limit);

        $tipListView = new TipListView();

        foreach($tips as $tip) {
            $tipListView->tipListView[] = new TipView(
                $tip->getId(),
                $tip->getTitle(),
                $tip->getTypes(),
                $tip->getPagesTranslations()
            );
        }

        $tipListView->results = $tips;

        return $tipListView;
    }
}
