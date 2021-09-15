<?php

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Order\OrderUpdatedEvent;
use Proximum\Vimeet\Application\Exception\Order\RemoveProductNotAllowedException;
use Proximum\Vimeet\Domain\Repository\Order\RowRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class RemoveRowHandler
{
    /**
     * @var RowRepositoryInterface
     */
    private $rowRepository;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @param RowRepositoryInterface $rowRepository
     * @param DelayedEventDispatcher $eventDispatcher
     */
    public function __construct(RowRepositoryInterface $rowRepository, DelayedEventDispatcher $eventDispatcher)
    {
        $this->rowRepository   = $rowRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param RemoveRow $removeRow
     *
     * @throws RemoveProductNotAllowedException
     */
    public function handle(RemoveRow $removeRow)
    {
        if ($removeRow->row->isProduct()) {
            throw new RemoveProductNotAllowedException('Delete a product row is not allowed');
        }

        $order = $removeRow->row->getOrder();

        $this->rowRepository->remove($removeRow->row);

        $this->eventDispatcher->dispatch(Events::ORDER_UPDATED, new OrderUpdatedEvent($order));
    }
}
