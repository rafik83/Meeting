<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet\Group\Admin;

use Proximum\Vimeet\Application\Adapter\ImpersonateUrlGeneratorInterface;
use Proximum\Vimeet\Application\View\Sheet\Group\Admin\GroupView as AdminGroupView;
use Proximum\Vimeet\Application\View\Sheet\Group\SheetView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class AdminGroupViewQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ImpersonateUrlGeneratorInterface */
    private $impersonateUrlGenerator;

    /**
     * @param SheetRepositoryInterface         $sheetRepository
     * @param ImpersonateUrlGeneratorInterface $impersonateUrlGenerator
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ImpersonateUrlGeneratorInterface $impersonateUrlGenerator
    ) {
        $this->sheetRepository         = $sheetRepository;
        $this->impersonateUrlGenerator = $impersonateUrlGenerator;
    }

    /**
     * @param AdminGroupViewQuery $groupViewQuery
     *
     * @return AdminGroupView
     */
    public function handle(AdminGroupViewQuery $groupViewQuery)
    {
        $sheets = $this->sheetRepository->getByGroup($groupViewQuery->group);

        $sheetViews = array_map(function (Sheet $sheet) {
            return new SheetView($sheet->getId(), $sheet->getTitle());
        }, $sheets);

        usort($sheetViews, function (SheetView $one, SheetView $other) {
            return strcasecmp($one->title, $other->title);
        });

        $impersonateUrl = $this->impersonateUrlGenerator->generate(
            $groupViewQuery->admin,
            $groupViewQuery->group->getManager(),
            $groupViewQuery->group->getEvent(),
            'event_sheet_group_index',
            ['sheetGroup' => $groupViewQuery->group->getId()]
        );

        return new AdminGroupView(
            $groupViewQuery->group->getId(),
            $groupViewQuery->group->getTitle(),
            $groupViewQuery->group->getManager()->getEmail(),
            $groupViewQuery->group->getManager()->getId(),
            $sheetViews,
            $groupViewQuery->group->getCreatedAt()
        );
    }
}
