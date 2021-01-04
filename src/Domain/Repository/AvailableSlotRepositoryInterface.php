<?php

namespace Proximum\Vimeet\Domain\Repository;

interface AvailableSlotRepositoryInterface
{
    /**
     * @param int[] $slotIds
     */
    public function deleteForSlotIds(array $slotIds): void;
}
