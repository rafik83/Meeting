<?php

namespace Proximum\Vimeet\Application\Command\Package\Step;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Package\StepDoneEvent;
use Proximum\Vimeet\Application\Exception\Package\PackageNotFoundException;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Package\Funnel\Step;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class SelectParticipantAndPlanningHandler
{
    /** @var CartManager */
    private $cartManager;

    /** @var Merger */
    private $merger;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    public function __construct(
        CartManager $cartManager,
        Merger $merger,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->cartManager = $cartManager;
        $this->merger = $merger;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param SelectParticipantAndPlanning $selectParticipantAndPlanning
     *
     * @throws PackageNotFoundException
     */
    public function handle(SelectParticipantAndPlanning $selectParticipantAndPlanning): void
    {
        $sheet = $selectParticipantAndPlanning->sheet;
        $package = $sheet->getPackage();

        if (!$package) {
            throw new PackageNotFoundException('Package not found');
        }

        $cart = $this->cartManager->getCart($sheet, $selectParticipantAndPlanning->currentStep);

        $cart = $this->cartManager->updateParticipantsQuantity($cart, $selectParticipantAndPlanning->participantsProduct);

        $this->handlePlanning($cart, $selectParticipantAndPlanning->planningQuantity->getQuantity());
        $this->cartManager->save($cart);

        $packageStepDone = new StepDoneEvent($selectParticipantAndPlanning->sheet, Step::TYPE_PARTICIPANT_PLANNING);
        $this->eventDispatcher->dispatch(Events::PACKAGE_STEP_DONE, $packageStepDone);
    }

    private function handlePlanning(Cart $cart, int $planningQuantity): void
    {
        $sheet = $cart->getSheet();

        $planningProduct = $sheet->getPackage()->getPlanning();

        if (null === $planningProduct) {
            return;
        }

        // Update planning cart row
        $orderPlanningQuantity = 0;

        $mergedOrder = $this->merger->getMergedOrders($sheet);

        if (null !== $mergedOrder) {
            if ($orderRow = $mergedOrder->getRowForProduct($planningProduct)) {
                $orderPlanningQuantity = $orderRow->getQuantity();
            }
        }

        $quantity = $planningQuantity - $orderPlanningQuantity;

        $cart->setProduct($planningProduct, $quantity);
    }
}
