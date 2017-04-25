<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Group;

use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class CreateHandler
{
    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * CreateHandler constructor.
     *
     * @param GroupRepositoryInterface  $groupRepository
     * @param SheetRepositoryInterface  $sheetRepository
     * @param \DateTimeInterface        $dateTime
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        SheetRepositoryInterface $sheetRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->groupRepository = $groupRepository;
        $this->sheetRepository = $sheetRepository;
        $this->dateTime        = $dateTime;
    }

    /**
     * @param Create $command
     */
    public function handle(Create $command)
    {
        $group = new Group($command->event, $command->user, $command->title, $this->dateTime);

        foreach ($command->sheetViews['sheetViews'] as $sheetView) {
            $sheet = $this->sheetRepository->getSheetById($sheetView->id);
            $sheet->setGroup($group);
        }

        $this->groupRepository->add($group);
    }
}
