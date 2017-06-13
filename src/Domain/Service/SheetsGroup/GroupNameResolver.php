<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Service\SheetsGroup;

use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class GroupNameResolver
{
    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * GroupNameResolver constructor.
     *
     * @param GroupRepositoryInterface $groupRepository
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(GroupRepositoryInterface $groupRepository, SheetRepositoryInterface $sheetRepository)
    {
        $this->groupRepository = $groupRepository;
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param Event $event
     * @param User  $user
     *
     * @return string
     * @throws SheetNotFoundException
     */
    public function resolve(Event $event, User $user)
    {
        $group = $this->groupRepository->getByUserAndEvent($user, $event);

        if ($group !== null) {
            return $group->getTitle();
        }

        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $event);

        if (empty($sheets)) {
            throw new SheetNotFoundException('Sheet not found.');
        }

        $sheet = reset($sheets);

        if (!$sheet instanceof Sheet) {
            throw new SheetNotFoundException('Sheet not found.');
        }

        return $sheet->getTitle();
    }
}
