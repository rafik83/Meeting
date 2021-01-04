<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Order;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Order\OrderConfirmEvent;
use Proximum\Vimeet\Domain\Happening\ParticipateToHappeningsByProduct;
use Proximum\Vimeet\Domain\Sheet\Product\SelectedProductAssigner;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderEventSubscriber implements EventSubscriberInterface
{
    /** @var ParticipateToHappeningsByProduct */
    private $participateToHappeningsByProduct;

    /** @var SelectedProductAssigner */
    private $selectedProductAssigner;

    public function __construct(
        ParticipateToHappeningsByProduct $participateToHappeningsByProduct,
        SelectedProductAssigner $selectedProductAssigner
    ) {
        $this->participateToHappeningsByProduct = $participateToHappeningsByProduct;
        $this->selectedProductAssigner = $selectedProductAssigner;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::ORDER_CONFIRMED => 'onOrderConfirmed',
        ];
    }

    public function onOrderConfirmed(OrderConfirmEvent $orderConfirmEvent): void
    {
        $this->selectedProductAssigner->handle($orderConfirmEvent->getOrder()->getSheet());
        $this->participateToHappeningsByProduct->handle($orderConfirmEvent->getOrder()->getSheet());
    }
}
