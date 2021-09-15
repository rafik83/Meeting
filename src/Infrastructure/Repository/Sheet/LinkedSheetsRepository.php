<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Sheet;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\LinkedSheets;
use Proximum\Vimeet\Domain\Repository\Sheet\LinkedSheetsRepositoryInterface;

class LinkedSheetsRepository implements LinkedSheetsRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getByEvent(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('linkedSheets, sheets')
            ->from(LinkedSheets::class, 'linkedSheets')
            ->leftJoin('linkedSheets.sheets', 'sheets')
            ->where('linkedSheets.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function add(LinkedSheets $linkedSheets): void
    {
        $this->entityManager->persist($linkedSheets);
        $this->entityManager->flush($linkedSheets);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(LinkedSheets $linkedSheets): void
    {
        $this->entityManager->remove($linkedSheets);
        $this->entityManager->flush($linkedSheets);
    }
}
