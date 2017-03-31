<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Tip;

use Proximum\Vimeet\Application\View\Tip\TipListView;
use Proximum\Vimeet\Application\View\Tip\TipView;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class TipViewQueryHandler
{
    /** @var TipRepositoryInterface */
    private $tipRepository;
    
    /**
     * TipViewQueryHandler constructor.
     *
     * @param TipRepositoryInterface $tipRepository
     */
    public function __construct(TipRepositoryInterface $tipRepository)
    {
        $this->tipRepository = $tipRepository;
    }
    
    /**
     * @param TipViewQuery $query
     *
     * @return TipListView
     */
    public function handle(TipViewQuery $query)
    {
        $tips = $this->tipRepository->paginate($query->page);
        
        $tipListView = new TipListView();
        
        foreach($tips as $tip) {
            $tipListView->tipListView[] = new TipView(
                $tip->getId(),
                $tip->getTitle(),
                $tip->isOnMeetingManagement(),
                $tip->isOnCatalog(),
                $tip->isOnPrintPlanning()
            );
        }
        
        $tipListView->results = $tips;
        
        return $tipListView;
    }
}
