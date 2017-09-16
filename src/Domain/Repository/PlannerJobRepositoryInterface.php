<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PlannerJob;

interface PlannerJobRepositoryInterface
{
    /**
     * @param PlannerJob $plannerJob
     */
    public function add(PlannerJob $plannerJob): void;

    /**
     * @param Event $event
     *
     * @return PlannerJob[]
     */
    public function findPendingByEvent(Event $event): array;
}
