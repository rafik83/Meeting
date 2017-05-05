<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\SheetViewed;

use Proximum\Vimeet\Domain\Model\Sheet\SheetViewed;
use Proximum\Vimeet\Domain\Repository\Sheet\SheetViewedRepositoryInterface;
use Proximum\Vimeet\Domain\Service\User\ImpersonatingUserChecker;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AddHandler
{
    /** @var SheetViewedRepositoryInterface */
    private $sheetViewedRepository;

    /** @var ImpersonatingUserChecker */
    private $impersonatingUserChecker;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * AddHandler constructor.
     *
     * @param SheetViewedRepositoryInterface $sheetViewedRepository
     * @param ImpersonatingUserChecker       $impersonatingUserChecker
     * @param \DateTimeInterface             $dateTime
     */
    public function __construct(
        SheetViewedRepositoryInterface $sheetViewedRepository,
        ImpersonatingUserChecker $impersonatingUserChecker,
        \DateTimeInterface $dateTime
    ) {
        $this->sheetViewedRepository    = $sheetViewedRepository;
        $this->impersonatingUserChecker = $impersonatingUserChecker;
        $this->dateTime                 = $dateTime;
    }

    /**
     * Mark specified sheet as viewed by specified user
     *
     * @param Add $command
     */
    public function handle(Add $command)
    {
        if (!$this->impersonatingUserChecker->isImpersonated()) {
            if (!$this->sheetViewedRepository->isSheetAlreadySeenByUser($command->user, $command->sheet)) {
                $sheetViewed = new SheetViewed($command->sheet, $command->user, $this->dateTime);
                $this->sheetViewedRepository->add($sheetViewed);
            }
        }
    }
}
