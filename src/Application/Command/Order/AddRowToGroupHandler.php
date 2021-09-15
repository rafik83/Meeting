<?php

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Order\OrderUpdatedEvent;
use Proximum\Vimeet\Domain\Model\Order\Row;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class AddRowToGroupHandler
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
     * @param AddRowToGroup $addRow
     * @throws \InvalidArgumentException
     */
    public function handle(AddRowToGroup $addRow): void
    {
        // check that provided groupId is valid for order
        if (!isset($addRow->order->getGroups()[$addRow->groupId])) {
            throw new \InvalidArgumentException(sprintf('Group %d is not valid for order %d', $addRow->groupId, $addRow->order->getId()));
        }

        $customRow = Row::createCustomRowToGroup(
            $addRow->order,
            $addRow->quantity,
            $addRow->groupId,
            $addRow->label,
            $addRow->price,
            $addRow->order->getVatRate()
        );

        $addRow->order->addCustomRow($customRow);
        $this->orderRepository->set($addRow->order);

        $this->eventDispatcher->dispatch(Events::ORDER_UPDATED, new OrderUpdatedEvent($addRow->order));
    }
}
