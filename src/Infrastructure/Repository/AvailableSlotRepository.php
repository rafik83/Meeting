<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Sheet\AvailableSlot;
use Proximum\Vimeet\Domain\Repository\AvailableSlotRepositoryInterface;

class AvailableSlotRepository implements AvailableSlotRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param int[] $slotIds
     */
    public function deleteForSlotIds(array $slotIds): void
    {
        $this->entityManager
            ->createQueryBuilder()
            ->delete()
            ->from(AvailableSlot::class, 'available_slot')
            ->where('available_slot.slot IN (:slots)')
            ->setParameter('slots', $slotIds)
            ->getQuery()
            ->execute()
        ;
    }
}
