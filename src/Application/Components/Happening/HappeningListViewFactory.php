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
     * HappeningPermissionManager constructor.
     *
     * @param HappeningRepositoryInterface $happeningRepository
     */
    public function __construct(HappeningRepositoryInterface $happeningRepository)
    {
        $this->happeningRepository = $happeningRepository;
    }

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return HappeningListView[]
     */
    public function getListByEventAndLocale(Event $event, $locale)
    {
        $happenings             = $this->happeningRepository->findListByEvent($event, $locale);
        $idsAllowedToBeModified = $this->happeningRepository->findIdsWithoutParticipationByEvent($event, $happenings);

        return array_map(function (Happening $happening) use ($locale, $idsAllowedToBeModified) {
            return new HappeningListView(
                $happening->getId(),
                $happening->getBegin(),
                $happening->getEnd(),
                $happening->getTitle($locale),
                array_map(function (Speaker $speaker) { return $speaker->getName(); }, $happening->getSpeakers()),
                in_array($happening->getId(), $idsAllowedToBeModified)
            );
        }, $happenings);
    }
}
