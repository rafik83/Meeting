<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Cart;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Package\StepDoneEvent;
use Proximum\Vimeet\Domain\Happening\ParticipateToHappeningsByProduct;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CartEventSubscriber implements EventSubscriberInterface
{
    /** @var ParticipateToHappeningsByProduct */
    private $participateToHappeningsByProduct;

    public function __construct(ParticipateToHappeningsByProduct $participateToHappeningsByProduct)
    {
        $this->participateToHappeningsByProduct = $participateToHappeningsByProduct;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::PACKAGE_STEP_DONE => 'onPackageStepDone',
        ];
    }

    public function onPackageStepDone(StepDoneEvent $stepDoneEvent)
    {
        if ($stepDoneEvent->isOptionsStep()) {
            $this->participateToHappeningsByProduct->handle($stepDoneEvent->getSheet());
        }
    }
}
