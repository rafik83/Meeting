<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;

class CartRowRepository implements CartRowRepositoryInterface
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
    public function add(CartRow $cartRow)
    {
        $this->entityManager->persist($cartRow);
        $this->entityManager->flush($cartRow);
    }

    /**
     * {@inheritdoc}
     */
    public function set(CartRow $cartRow)
    {
        $this->entityManager->flush($cartRow);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteWhereNotIn(Sheet $sheet, array $cartRows)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->delete()
            ->from(CartRow::class, 'cartRow')
            ->where('cartRow.sheet = :sheet')
            ->setParameter('sheet', $sheet);

        if (!empty($cartRows)) {
            $queryBuilder
                ->andWhere('cartRow NOT IN (:cartRows)')
                ->setParameter('cartRows', $cartRows);
        }

        $queryBuilder->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findBySheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('cartRow')
            ->from(CartRow::class, 'cartRow')
            ->where('cartRow.sheet = :sheet')
            ->setParameter('sheet', $sheet);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(CartRow $cartRow)
    {
        $this->entityManager->remove($cartRow);
        $this->entityManager->flush($cartRow);
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
            ->from(CartRow::class, 'cartRow')
            ->where('cartRow.sheet = :sheet')
            ->setParameter('sheet', $sheet);

        $queryBuilder->getQuery()->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findByProduct($product)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('cartRow')
            ->from(CartRow::class, 'cartRow')
            ->where('cartRow.product = :product')
            ->setParameter('product', $product);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function hasProducts(Sheet $sheet)
    {
        return count($this->findBySheet($sheet)) > 0;
    }
}
