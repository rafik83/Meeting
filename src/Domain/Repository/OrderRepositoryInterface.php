<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Numero\OrderNumeroView;

interface OrderRepositoryInterface
{
    /**
     * @param Order $order
     */
    public function add(Order $order);

    /**
     * @param Order $order
     */
    public function set(Order $order);

    /**
     * @param Sheet $sheet
     *
     * @return Order[]
     */
    public function findBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return Order[]
     */
    public function findNotCancelledBySheet(Sheet $sheet);

    /**
     * @param Sheet $sheet
     *
     * @return Order[]
     */
    public function findNotCancelledAndNotInvoicedBySheet(Sheet $sheet);

    /**
     * @param Event $event
     * @param array $filters
     * @param int   $page
     * @param int   $limit
     *
     * @return PaginatedResult
     */
    public function findAndPaginateByEvent(Event $event, array $filters, $page, $limit);

    /**
     * @param OrderNumeroView $orderNumeroView
     *
     * @return Order|null
     */
    public function findByNumero(OrderNumeroView $orderNumeroView);

    /**
     * @param Event $event
     *
     * @return Order[]
     */
    public function findByEventAndEnabledSheets(Event $event);

    /**
     * @param Event $event
     * @param int[] $sheetIds
     *
     * @return Order[]
     */
    public function findByEventAndSheetIds(Event $event, array $sheetIds);

    /**
     * @param Event $event
     *
     * @return Order[]
     */
    public function findNotCancelledByEvent(Event $event);

    /**
     * @param Event $event
     *
     * @return Order[]
     */
    public function findNotCancelledWithJoinRowAndPromotionCodeByEvent(Event $event);

    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function hasInvoice(Sheet $sheet);

    /**
     * @return Order[]
     */
    public function findWithPromotion(): array;

    public function hasOrderWithPromotionCode(PromotionCode $promotionCode): bool;
}
