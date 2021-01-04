<?php

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Sheet\ChangeType\CancelPackage;
use Proximum\Vimeet\Application\Command\Sheet\ChangeType\CancelPackageHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\Order\OrdersCancelledEvent;
use Proximum\Vimeet\Domain\Exception\Order\OrderCanNotBeCancelledException;
use Proximum\Vimeet\Domain\Model\Order;

class CancelAllHandler
{
    /** @var CancelPackageHandler */
    private $cancelPackageHandler;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    public function __construct(
        CancelPackageHandler $cancelPackageHandler,
        DelayedEventDispatcherInterface $delayedEventDispatcher
    ) {
        $this->cancelPackageHandler = $cancelPackageHandler;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
    }

    public function handle(CancelAll $command): void
    {
        $orders = $command->sheet->getNotCancelledOrders();

        if (empty($orders)) {
            return;
        }

        $isInvoiced = !empty(array_filter($orders, function (Order $order) {
            return $order->hasInvoice();
        }));

        if ($isInvoiced) {
            throw new OrderCanNotBeCancelledException('Orders have been invoiced');
        }

        $this->cancelPackageHandler->handle(new CancelPackage($command->sheet));

        $this->delayedEventDispatcher->dispatch(
            Events::SHEET_ORDERS_CANCELLED,
            new OrdersCancelledEvent($command->sheet, $command->admin)
        );
    }
}
