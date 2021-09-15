<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\PromotionCodeRow;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRowRepositoryInterface;

class PromotionCodeRowRepository implements PromotionCodeRowRepositoryInterface
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
    public function add(PromotionCodeRow $promotionCodeRow)
    {
        $this->entityManager->persist($promotionCodeRow);
        $this->entityManager->flush($promotionCodeRow);
    }

    /**
     * {@inheritdoc}
     */
    public function set(PromotionCodeRow $promotionCodeRow)
    {
        $this->entityManager->flush($promotionCodeRow);
    }

    /**
     * {@inheritdoc}
     */
    public function findBySheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('promotionCodeRow')
            ->from(PromotionCodeRow::class, 'promotionCodeRow')
            ->where('promotionCodeRow.sheet = :sheet')
            ->setParameter('sheet', $sheet);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(PromotionCodeRow $promotionCodeRow)
    {
        $this->entityManager->remove($promotionCodeRow);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteForSheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->delete()
            ->from(PromotionCodeRow::class, 'promotionCodeRow')
            ->where('promotionCodeRow.sheet = :sheet')
            ->setParameter('sheet', $sheet);

        $queryBuilder->getQuery()->execute();
    }
}
