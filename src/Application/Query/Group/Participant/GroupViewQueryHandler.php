<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Group\Participant;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Sheet\Group\Participant\GroupView;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class GroupViewQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /** @var SheetsViewQueryHandler $sheetsViewQueryHandler */
    private $sheetsViewQueryHandler;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param SheetInfoGuesser         $sheetInfoGuesser
     * @param SheetsViewQueryHandler   $sheetsViewQueryHandler
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        SheetsViewQueryHandler $sheetsViewQueryHandler
    ) {
        $this->sheetRepository        = $sheetRepository;
        $this->sheetInfoGuesser       = $sheetInfoGuesser;
        $this->sheetsViewQueryHandler = $sheetsViewQueryHandler;
    }

    /**
     * @param GroupViewQuery $groupViewQuery
     *
     * @return GroupView
     */
    public function handle(GroupViewQuery $groupViewQuery)
    {
        $sheets     = $this->sheetRepository->getByGroup($groupViewQuery->group);
        $sheetViews = $this->sheetsViewQueryHandler->handle(new SheetsViewQuery($sheets));

        return new GroupView($groupViewQuery->group->getId(), $groupViewQuery->group->getTitle(), $sheetViews);
    }
}
