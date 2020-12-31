<?php

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Order\OrderUpdatedEvent;
use Proximum\Vimeet\Domain\Repository\Order\RowRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class UpdateRowHandler
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
     * @param UpdateRow $updateRow
     */
    public function handle(UpdateRow $updateRow)
    {
        $updateRow->row->update($updateRow->label, $updateRow->price, $updateRow->quantity);
        $this->rowRepository->set($updateRow->row);

        $this->eventDispatcher->dispatch(Events::ORDER_UPDATED, new OrderUpdatedEvent($updateRow->row->getOrder()));
    }
}
