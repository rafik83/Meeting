<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\CartStep;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\CartStepRepositoryInterface;

class CartStepRepository implements CartStepRepositoryInterface
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
    public function add(CartStep $cartStep)
    {
        $this->entityManager->persist($cartStep);
        $this->entityManager->flush($cartStep);
    }

    /**
     * {@inheritdoc}
     */
    public function set(CartStep $cartStep)
    {
        $this->entityManager->flush($cartStep);
    }

    /**
     * {@inheritdoc}
     */
    public function findBySheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('cartStep')
            ->from(CartStep::class, 'cartStep')
            ->where('cartStep.sheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(CartStep $cartStep)
    {
        $this->entityManager->remove($cartStep);
        $this->entityManager->flush($cartStep);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteForSheet(Sheet $sheet)
    {
        $this
            ->entityManager
            ->createQueryBuilder()
            ->delete(CartStep::class, 'cartStep')
            ->where('cartStep.sheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->getQuery()
            ->execute()
        ;

        $this->entityManager->flush();
    }
}
