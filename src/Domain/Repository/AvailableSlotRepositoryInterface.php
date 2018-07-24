<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

interface AvailableSlotRepositoryInterface
{
    /**
     * @param int[] $slotIds
     */
    public function deleteForSlotIds(array $slotIds): void;
}
