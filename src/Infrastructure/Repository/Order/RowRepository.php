<?php

namespace Proximum\Vimeet\Infrastructure\Repository\Order;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Order\Row;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\Order\RowRepositoryInterface;

class RowRepository implements RowRepositoryInterface
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
    public function remove(Row $row)
    {
        $this->entityManager->remove($row);
        $this->entityManager->flush($row);
    }

    /**
     * @param Row $row
     */
    public function set(Row $row)
    {
        $this->entityManager->flush($row);
    }

    /**
     * {@inheritdoc}
     */
    public function findByProduct(Product $product)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('orderRow')
            ->from(Row::class, 'orderRow')
            ->where('orderRow.product = :product')
            ->setParameter('product', $product);

        return $queryBuilder->getQuery()->getResult();
    }

    public function boughtByProduct(Product $product): int
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('sum(orderRow.quantity)')
            ->from(Row::class, 'orderRow')
            ->join('orderRow.order', '_order', 'WITH', 'orderRow.product = :product AND _order.cancelled = false')
            ->setParameter('product', $product)
            ->setMaxResults(1)
        ;

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }
}
