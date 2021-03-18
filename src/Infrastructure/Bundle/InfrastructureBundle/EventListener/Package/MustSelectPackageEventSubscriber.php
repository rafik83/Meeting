<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Package;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Order\OrderConfirmEvent;
use Proximum\Vimeet\Application\Event\Package\MustSelectPackageEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetChangedTypeEvent;
use Proximum\Vimeet\Domain\Package\Orders\OrdersChecker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MustSelectPackageEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var OrdersChecker
     */
    private $ordersChecker;

    /**
     * @param OrdersChecker $ordersChecker
     */
    public function __construct(OrdersChecker $ordersChecker)
    {
        $this->ordersChecker = $ordersChecker;
    }

    /**
     * @param MustSelectPackageEvent $ordersUpdatedEvent
     */
    public function onSheetCreation(MustSelectPackageEvent $ordersUpdatedEvent)
    {
        $this->ordersChecker->check($ordersUpdatedEvent->getSheet());
    }

    /**
     * @param OrderConfirmEvent $orderConfirmEvent
     */
    public function onOrderCreation(OrderConfirmEvent $orderConfirmEvent)
    {
        $this->ordersChecker->check($orderConfirmEvent->getOrder()->getSheet());
    }

    /**
     * @param SheetChangedTypeEvent $sheetChangedTypeEvent
     */
    public function onTypeChanged(SheetChangedTypeEvent $sheetChangedTypeEvent)
    {
        $this->ordersChecker->check($sheetChangedTypeEvent->getSheet());
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::MUST_SELECT_PACKAGE => 'onSheetCreation',
            Events::SHEET_CHANGED_TYPE  => 'onTypeChanged',
            Events::ORDER_CONFIRMED     => 'onOrderCreation',
        ];
    }
}
