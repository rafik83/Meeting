<?php

namespace Proximum\Vimeet\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Application\Components\Paginator\Paginator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Numero\OrderNumeroView;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * @param EntityManager $entityManager
     * @param Paginator     $paginator
     */
    public function __construct(EntityManager $entityManager, Paginator $paginator)
    {
        $this->entityManager = $entityManager;
        $this->paginator = $paginator;
    }

    /**
     * {@inheritdoc}
     */
    public function add(Order $order)
    {
        $this->entityManager->persist($order);
        $this->entityManager->flush($order);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Order $order)
    {
        $this->entityManager->flush($order);
    }

    /**
     * {@inheritdoc}
     */
    public function findBySheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('_order')
            ->from(Order::class, '_order', '_order.id')
            ->where('_order.sheet = :sheet')
            ->setParameter('sheet', $sheet)
            ->orderBy('_order.createdAt', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findNotCancelledBySheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('_order, row, promotionCode')
            ->from(Order::class, '_order', '_order.id')
            ->join('_order.rows', 'row', 'WITH', '_order.sheet = :sheet AND _order.cancelled = false')
            ->leftJoin('_order.promotionCodes', 'promotionCode')
            ->setParameter('sheet', $sheet)
            ->orderBy('_order.createdAt', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findAndPaginateByEvent(Event $event, array $filters, $page, $limit)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('_order')
            ->from(Order::class, '_order', '_order.id')
            ->join('_order.sheet', 'sheet', 'WITH', 'sheet.event = :event')
            ->setParameter('event', $event)
            ->orderBy('_order.createdAt', 'DESC');

        if (isset($filters['product']) && $filters['product'] instanceof Product) {
            $queryBuilder
                ->join('_order.rows', 'rows', 'WITH', 'rows.product = :product')
                ->setParameter('product', $filters['product']);
        }

        if (isset($filters['enabled'])) {
            $queryBuilder
                ->andWhere('sheet.enable = :enable')
                ->setParameter('enable', $filters['enabled']);
        }

        if (isset($filters['cancelled']) && $filters['cancelled'] !== 'all') {
            $queryBuilder
                ->andWhere('_order.cancelled = :cancelled')
                ->setParameter('cancelled', $filters['cancelled'] === 'cancelled');
        }

        return $this->paginator->paginate($queryBuilder, $page, $limit, '_order', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventAndEnabledSheets(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('_order, sheet, row, promotionCode')
            ->from(Order::class, '_order', '_order.id')
            ->join('_order.sheet', 'sheet', 'WITH', 'sheet.event = :event AND sheet.enable = true')
            ->join('_order.rows', 'row')
            ->leftJoin('_order.promotionCodes', 'promotionCode')
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByEventAndSheetIds(Event $event, array $sheetIds)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('_order, sheet, row, promotionCode')
            ->from(Order::class, '_order', '_order.id')
            ->join(
                '_order.sheet',
                'sheet',
                'WITH',
                'sheet.id IN (:sheetIds) AND sheet.event = :event AND sheet.enable = true'
            )
            ->join('_order.rows', 'row')
            ->leftJoin('_order.promotionCodes', 'promotionCode')
            ->setParameter('event', $event)
            ->setParameter('sheetIds', $sheetIds)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findNotCancelledByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('_order, sheet, row')
            ->from(Order::class, '_order', '_order.id')
            ->join('_order.sheet', 'sheet', 'WITH', 'sheet.event = :event AND sheet.enable = true AND _order.cancelled = false')
            ->join('_order.rows', 'row')
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findNotCancelledWithJoinRowAndPromotionCodeByEvent(Event $event)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('_order, sheet, row, promotionCode')
            ->from(Order::class, '_order', '_order.id')
            ->join('_order.sheet', 'sheet', 'WITH', 'sheet.event = :event AND sheet.enable = true AND _order.cancelled = false')
            ->leftJoin('_order.rows', 'row')
            ->leftJoin('_order.promotionCodes', 'promotionCode')
            ->setParameter('event', $event)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByNumero(OrderNumeroView $orderNumeroView)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('_order')
            ->from(Order::class, '_order')
            ->join('_order.sheet', 'sheet')
            ->where('_order.id = :orderId')
            ->andWhere('_order.sheet = :sheetId')
            ->andWhere('sheet.event = :eventId')
            ->setParameter('eventId', $orderNumeroView->eventId)
            ->setParameter('sheetId', $orderNumeroView->sheetId)
            ->setParameter('orderId', $orderNumeroView->orderId)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findNotCancelledAndNotInvoicedBySheet(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('_order, row')
            ->from(Order::class, '_order', '_order.id')
            ->join('_order.rows', 'row')
            ->where('_order.sheet = :sheet')
            ->andWhere('_order.cancelled = false')
            ->andWhere('_order.invoice IS NULL')
            ->setParameter('sheet', $sheet)
            ->orderBy('_order.createdAt', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function hasInvoice(Sheet $sheet)
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('_order.id')
            ->from(Order::class, '_order', '_order.id')
            ->where('_order.sheet = :sheet')
            ->andWhere('_order.invoice IS NOT NULL')
            ->setParameter('sheet', $sheet)
            ->setMaxResults(1);

        return (null === $queryBuilder->getQuery()->getOneOrNullResult()) ? false : true;
    }

    public function hasOrderWithPromotionCode(PromotionCode $promotionCode): bool
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('_order.id')
            ->from(Order::class, '_order', '_order.id')
            ->innerJoin('_order.promotionCodes',
                'orderPromotionCode',
                'WITH',
                'orderPromotionCode.promotionCode = :promotionCode AND _order.cancelled = false'
            )
            ->setParameter('promotionCode', $promotionCode)
            ->setMaxResults(1);

        return (null === $queryBuilder->getQuery()->getOneOrNullResult()) ? false : true;
    }

    /**
     * {@inheritdoc}
     */
    public function findWithPromotion(): array
    {
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('_order', 'promotionCodes')
            ->from(Order::class, '_order')
            ->join('_order.promotionCodes', 'promotionCodes')
            ->where('_order.cancelled = false')
            ->andWhere('EXISTS(SELECT 1
                                FROM Entity:Order\PromotionCode promo
                                WHERE promo.order = _order)');

        return $queryBuilder->getQuery()->getResult();
    }

    public function findByIds(array $ids): array
    {
        return $this
            ->entityManager
            ->createQueryBuilder()
            ->select('_order')
            ->from(Order::class, '_order')
            ->where('_order IN(:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }
}
