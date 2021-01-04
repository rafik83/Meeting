<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order\Row;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
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
    public function add(Product $product)
    {
        $this->entityManager->persist($product);
        $this->entityManager->flush($product);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Product $product)
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush($product);
    }

    /**
     * {@inheritdoc}
     */
    public function findByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('product')
            ->from(Product::class, 'product')
            ->where('product.event = :event')
            ->setParameter('event', $event);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventOrderedByProductTypeAndProductName(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('product')
            ->from(Product::class, 'product')
            ->where('product.event = :event')
            ->setParameter('event', $event)
            ->orderBy('product.type, product.name');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function countByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('product, SUM(row.quantity) AS bought')
            ->from(Product::class, 'product')
            ->leftJoin(Row::class, 'row', 'WITH', 'row.product = product')
            ->where('product.event = :event')
            ->setParameter('event', $event)
            ->groupBy('product')
            ->orderBy('product.type, product.name');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventAndTypes(Event $event, array $types)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('product')
            ->from(Product::class, 'product')
            ->where('product.event = :event')
            ->setParameter('event', $event)
            ->andWhere('product.type IN (:types)')
            ->setParameter('types', $types);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param Product $product
     */
    public function update(Product $product)
    {
        $this->entityManager->flush($product);
    }

    /**
     * @param Event $event
     *
     * @return Product[]
     */
    public function findOptionsByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('product')
            ->from(Product::class, 'product')
            ->where('product.event = :event')
            ->andWhere('product.type = :type')
            ->setParameter('event', $event)
            ->setParameter('type', Product::TYPE_OPTION);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param array $productIds
     *
     * @return Product[]
     */
    public function findProductByIds(array $productIds)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('product')
            ->from(Product::class, 'product')
            ->where('product.id IN (:productIds)')
            ->setParameter('productIds', $productIds);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param int $productId
     *
     * @return null|Product
     */
    public function findById($productId)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('product')
            ->from(Product::class, 'product')
            ->where('product.id = :productId')
            ->setParameter('productId', $productId);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findRemovableProductsForEvent(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('product')
            ->from(Product::class, 'product', 'product.id')
            ->where('product.event = :event')
            ->setParameter('event', $event);

        $this->addRemovableCondition($queryBuilder);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function isProductRemovable(Product $product): bool
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('product.id')
            ->from(Product::class, 'product')
            ->where('product.event = :event')
            ->andWhere('product = :product')
            ->setParameter('product', $product)
            ->setParameter('event', $product->getEvent());

        $this->addRemovableCondition($queryBuilder);

        return null !== $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findParticipantAndAttributableByEvent($event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('product')
            ->from(Product::class, 'product')
            ->where('product.event = :event')
            ->andWhere('(
                product.type = :typeParticipant
                OR (product.type = :typeOption AND product.attributable = TRUE)
            )')
            ->setParameter('event', $event)
            ->setParameter('typeParticipant', Product::TYPE_PARTICIPANT)
            ->setParameter('typeOption', Product::TYPE_OPTION)
            ->orderBy('product.type', 'DESC')
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function findProductsBoughtByEvent(Event $event): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('product')
            ->from(Product::class, 'product')
            ->innerJoin(Row::class, 'row', 'WITH', 'row.product = product.id AND product.event = :event')
            ->innerJoin('row.order', '_order', 'WITH', '_order.cancelled = false')
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    private function addRemovableCondition(QueryBuilder $queryBuilder)
    {
        $queryBuilder
            ->andWhere('(
                NOT EXISTS(
                    SELECT _order.id
                    FROM Entity:Order _order
                    JOIN _order.sheet sheet WITH sheet.event = :event
                    JOIN _order.rows row
                    WHERE row.product = product
                )
                AND NOT EXISTS(
                    SELECT plan.id
                    FROM Entity:Product plan
                    JOIN plan.productIncluded productIncluded
                    WHERE plan.event = :event AND productIncluded.included = product
                )
                AND NOT EXISTS(
                    SELECT package.id
                    FROM Entity:Package package
                    LEFT JOIN package.participantRanks participantRank
                    LEFT JOIN package.planning planning
                    LEFT JOIN package.groups group
                    LEFT JOIN group.optionRanks optionRank
                    LEFT JOIN package.planRanks packagePlanRank
                    WHERE participantRank.productParticipant = product
                        OR planning = product
                        OR packagePlanRank.plan = product
                        OR optionRank.option = product
                )
            )')
        ;
    }
}
