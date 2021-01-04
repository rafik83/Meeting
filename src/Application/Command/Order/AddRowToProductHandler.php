<?php

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Order\OrderUpdatedEvent;
use Proximum\Vimeet\Domain\Model\Order\Row;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class AddRowToProductHandler
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param DelayedEventDispatcher   $eventDispatcher
     */
    public function __construct(OrderRepositoryInterface $orderRepository, DelayedEventDispatcher $eventDispatcher)
    {
        $this->orderRepository = $orderRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param AddRowToProduct $addRow
     */
    public function handle(AddRowToProduct $addRow): void
    {
        $customRow = Row::createCustomRowToProduct(
            $addRow->order,
            $addRow->row,
            $addRow->label,
            $addRow->quantity,
            $addRow->price,
            $addRow->order->getVatRate()
        );

        $addRow->order->addCustomRow($customRow);
        $this->orderRepository->set($addRow->order);

        $this->eventDispatcher->dispatch(Events::ORDER_UPDATED, new OrderUpdatedEvent($addRow->order));
    }
}
