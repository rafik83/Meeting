<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\SheetViewed;

use Proximum\Vimeet\Domain\Model\Sheet\SheetViewed;
use Proximum\Vimeet\Domain\Repository\Sheet\SheetViewedRepositoryInterface;

class AddHandler
{
    /** @var SheetViewedRepositoryInterface */
    private $sheetViewedRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * AddHandler constructor.
     *
     * @param SheetViewedRepositoryInterface $sheetViewedRepository
     * @param \DateTimeInterface             $dateTime
     */
    public function __construct(
        SheetViewedRepositoryInterface $sheetViewedRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->sheetViewedRepository = $sheetViewedRepository;
        $this->dateTime              = $dateTime;
    }

    /**
     * Mark specified sheet as viewed by specified user
     *
     * @param Add $command
     */
    public function handle(Add $command)
    {
        if (!$this->sheetViewedRepository->isSheetAlreadySeenByUser($command->user, $command->sheet)) {
            $sheetViewed = new SheetViewed($command->sheet, $command->user, $this->dateTime);
            $this->sheetViewedRepository->add($sheetViewed);
        }
    }
}
