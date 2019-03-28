<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet\LinkedSheets\Admin;

use Proximum\Vimeet\Domain\Repository\Sheet\LinkedSheetsRepositoryInterface;

class LinkedSheetsListViewQueryHandler
{
    /**
     * @var LinkedSheetsRepositoryInterface
     */
    private $linkedSheetsRepository;

    public function __construct(LinkedSheetsRepositoryInterface $linkedSheetsRepository)
    {
        $this->linkedSheetsRepository = $linkedSheetsRepository;
    }

    public function handle(LinkedSheetsListViewQuery $query): LinkedSheetsListView
    {
        $linkedSheetsViews = [];
        $someLinkedSheets = $this->linkedSheetsRepository->getByEvent($query->event);
        foreach ($someLinkedSheets as $linkedSheets) {
            $titles = [];
            foreach ($linkedSheets->getSheets() as $sheet) {
                $titles[] = $sheet->getTitle();
            }
            $linkedSheetsViews[] = new LinkedSheetsView($titles, $linkedSheets->getCreatedAt());
        }

        return new LinkedSheetsListView($linkedSheetsViews);
    }
}
