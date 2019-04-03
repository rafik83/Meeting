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
use Proximum\Vimeet\Domain\Sheet\LinkedSheets\RemovableLinkedSheetsFilter;

class LinkedSheetsListViewQueryHandler
{
    /**
     * @var LinkedSheetsRepositoryInterface
     */
    private $linkedSheetsRepository;

    /** @var RemovableLinkedSheetsFilter */
    private $removableLinkedSheetsFilter;

    public function __construct(
        LinkedSheetsRepositoryInterface $linkedSheetsRepository,
        RemovableLinkedSheetsFilter $removableLinkedSheetsFilter
    ) {
        $this->linkedSheetsRepository = $linkedSheetsRepository;
        $this->removableLinkedSheetsFilter = $removableLinkedSheetsFilter;
    }

    public function handle(LinkedSheetsListViewQuery $query): LinkedSheetsListView
    {
        $linkedSheetsViews = [];
        $someLinkedSheets = $this->linkedSheetsRepository->getByEvent($query->event);

        $removableLinkedSheets = $this->removableLinkedSheetsFilter->isSatisfiedBy($someLinkedSheets);

        foreach ($someLinkedSheets as $linkedSheets) {
            $titles = [];
            foreach ($linkedSheets->getSheets() as $sheet) {
                $titles[] = $sheet->getTitle();
            }
            $isRemovable = in_array($linkedSheets, $removableLinkedSheets, true);
            $linkedSheetsViews[] = new LinkedSheetsView($titles, $linkedSheets->getCreatedAt(), $isRemovable);
        }

        return new LinkedSheetsListView($linkedSheetsViews);
    }
}
