<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\View\Sheet\SheetIdsView;

class GetSheetIdsByFiltersQueryHandler
{
    /** @var SheetSearchAdapterInterface */
    private $sheetSearchAdapter;

    public function __construct(SheetSearchAdapterInterface $sheetSearchAdapter)
    {
        $this->sheetSearchAdapter = $sheetSearchAdapter;
    }

    public function handle(GetSheetIdsByFiltersQuery $query): SheetIdsView
    {
        return $this->sheetSearchAdapter->getSheetIdsView(
            $query->event,
            $query->filters,
            $query->locale,
            $query->condition
        );
    }
}
