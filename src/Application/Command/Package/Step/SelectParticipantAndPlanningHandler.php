<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package\Step;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Package\StepDoneEvent;
use Proximum\Vimeet\Application\Exception\Package\PackageNotFoundException;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class SelectParticipantAndPlanningHandler
{
    /** @var CartManager */
    private $cartManager;

    /** @var Merger */
    private $merger;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    /**
     * @param CartManager            $cartManager
     * @param Merger                 $merger
     * @param DelayedEventDispatcher $eventDispatcher
     */
    public function __construct(
        CartManager $cartManager,
        Merger $merger,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->cartManager     = $cartManager;
        $this->merger          = $merger;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param SelectParticipantAndPlanning $selectParticipantAndPlanning
     *
     * @throws PackageNotFoundException
     */
    public function handle(SelectParticipantAndPlanning $selectParticipantAndPlanning)
    {
        $sheet   = $selectParticipantAndPlanning->sheet;
        $package = $sheet->getPackage();

        if (!$package) {
            throw new PackageNotFoundException('Package not found');
        }

        $cart = $this->cartManager->getCart($sheet, $selectParticipantAndPlanning->currentStep);

        $cart = $this->cartManager->updateParticipantsQuantity($cart, $selectParticipantAndPlanning->participantsProduct);
        $this->handlePlanning($cart, $selectParticipantAndPlanning->planningQuantity);
        $this->cartManager->save($cart);

        $packageStepDone = new StepDoneEvent($selectParticipantAndPlanning->sheet);
        $this->eventDispatcher->dispatch(Events::PACKAGE_STEP_DONE, $packageStepDone);
    }

    /**
     * @param Cart $cart
     * @param int  $planningQuantity
     */
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
