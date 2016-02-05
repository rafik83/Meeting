<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Happening;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class HappeningListViewFactory
{
    /**
     * @var HappeningRepositoryInterface
     */
    private $happeningRepository;

    /**
     * @var HappeningPermissionManager
     */
    private $happeningPermissionManager;

    /**
     * HappeningListViewFactory constructor.
     *
     * @param HappeningRepositoryInterface $happeningRepository
     * @param HappeningPermissionManager   $happeningPermissionManager
     */
    public function __construct(
        HappeningRepositoryInterface $happeningRepository,
        HappeningPermissionManager $happeningPermissionManager
    ) {
        $this->happeningRepository        = $happeningRepository;
        $this->happeningPermissionManager = $happeningPermissionManager;
    }

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return HappeningListView[]
     */
    public function getListByEventAndLocale(Event $event, $locale)
    {
        // Get happenings
        $happenings = $this->happeningRepository->findListByEvent($event, $locale);

        // Load permissions
        $this->happeningPermissionManager->loadAllowedToBeModified($happenings);

        return array_map(function (Happening $happening) use ($locale) {
            return new HappeningListView(
                $happening->getId(),
                $happening->getBegin(),
                $happening->getEnd(),
                $happening->getTitle($locale),
                array_map(function (Speaker $speaker) { return $speaker->getName(); }, $happening->getSpeakers()),
                $this->happeningPermissionManager->isAllowedToBeModified($happening)
            );
        }, $happenings);
    }
}
