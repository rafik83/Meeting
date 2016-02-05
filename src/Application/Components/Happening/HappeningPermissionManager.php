<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class HappeningPermissionManager
{
    /**
     * @var HappeningRepositoryInterface
     */
    private $happeningRepository;

    /**
     * @var array
     */
    private $ids = [];

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
     * @param Happening $happening
     * @param array     $happenings
     *
     * @return bool
     */
    public function isAllowedToBeModified(Happening $happening, array $happenings = [])
    {
        $this->updateAllowedIds(empty($happenings) ? [$happening] : $happenings);

        return in_array($happening->getId(), $this->ids);
    }

    /**
     * @param array $happenings
     */
    private function updateAllowedIds(array $happenings)
    {
        $ids  = array_map(function (Happening $happening) { return $happening->getId(); }, $happenings);
        $diff = array_diff($ids, $this->ids);

        if (!empty($diff)) {
            $this->ids = array_merge($this->ids, $this->happeningRepository->findIdsWithoutParticipation($diff));
        }
    }
}
