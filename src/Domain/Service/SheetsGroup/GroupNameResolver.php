<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
     * If we found a Group return the group's title
     * Else we return the sheet title
     *
     * @param Event   $event
     * @param User    $user
     * @param Sheet[] $sheets - Optional parameters to preload sheets
     *
     * @throws SheetNotFoundException
     *
     * @return string
     */
    public function resolve(Event $event, User $user, array $sheets = [])
    {
        if (empty($sheets)) {
            $sheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $event);

            if (empty($sheets)) {
                throw new SheetNotFoundException('Sheet not found.');
            }
        }

        foreach ($sheets as $sheet) {
            if (null !== $sheet->getGroup()) {
                return $sheet->getGroup()->getTitle();
            }
        }

        $sheet = reset($sheets);

        if (!$sheet instanceof Sheet) {
            throw new SheetNotFoundException('Sheet not found.');
        }

        return $sheet->getTitle();
    }

    /**
     * @param Event $event
     * @param User  $user
     *
     * @return null|string
     */
    private function getGroupByUserAndEvent(Event $event, User $user)
    {
        $group = $this->groupRepository->getByUserAndEvent($user, $event);

        if (null !== $group) {
            return $group->getTitle();
        }

        return null;
    }
}
