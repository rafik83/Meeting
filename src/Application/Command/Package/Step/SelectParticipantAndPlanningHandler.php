<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package\Step;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Package\StepDoneEvent;
use Proximum\Vimeet\Application\Exception\Package\PackageNotFoundException;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\CartRowParticipant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
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
     * @throws \Exception
     */
    public function handle(SelectParticipantAndPlanning $selectParticipantAndPlanning)
    {
        $sheet   = $selectParticipantAndPlanning->sheet;
        $package = $sheet->getPackage();

        if (!$package) {
            throw new PackageNotFoundException('Package not found');
        }

        $cart = $this->cartManager->getCart($sheet, $selectParticipantAndPlanning->currentStep);

        $orders = $sheet->getNotCancelledOrders();

        // Update participant cart row
//        if (count($orders) > 0) {
//            $order = $this->merger->merge($orders);
//
//            if (null !== $order) {
//                $cart->resolveParticipantsQuantity($order, $selectParticipantAndPlanning->participantsProduct);
//            }
//        } else {
//            $cart->resolveParticipantsQuantity();
//        }
        $availableParticipantProductsById = [];
        $participantProductsQuantityById = [];

        foreach ($sheet->getType()->getPackage()->getParticipants() as $product) {
            $availableParticipantProductsById[$product->getId()] = $product;
        }


        foreach ($sheet->getParticipantsArray() as $participant) {
            if (isset($selectParticipantAndPlanning->participantsProduct[$participant->getId()])) {
                $product = $selectParticipantAndPlanning->participantsProduct[$participant->getId()];

                if (!$product instanceof Product) {
                    continue;
                }

                if (isset($participantProductsQuantityById[$product->getId()])) {
                    $participantProductsQuantityById[$product->getId()]['quantity']++;
                    $participantProductsQuantityById[$product->getId()]['participants'][] = $participant;
                    continue;
                }

                $participantProductsQuantityById[$product->getId()]['quantity'] = 1;
                $participantProductsQuantityById[$product->getId()]['participants'] = [];
                $participantProductsQuantityById[$product->getId()]['participants'][] = $participant;
            }
        }

        foreach ($participantProductsQuantityById as $productId => $participantProductQuantity) {
            $cart->setProduct($availableParticipantProductsById[$productId], $participantProductQuantity['quantity']);
        }

        foreach ($cart->getParticipantRows() as $participantCartRow) {
            foreach ($participantProductsQuantityById[$participantCartRow->getProduct()->getId()]['participants'] as $participant) {
                $participantCartRow->addCartRowParticipant(new CartRowParticipant($participantCartRow, $participant));
            }
        }




        if (!$package->getPlanning()) {
            return;
        }

        // Update planning cart row
        $orderPlanningQuantity = 0;

        if (count($orders) > 0) {
            $merged = $this->merger->merge($orders);

            if ($merged !== null ) {
                if ($orderRow = $merged->getRowForProduct($package->getPlanning())) {
                    $orderPlanningQuantity = $orderRow->getQuantity();
                }
            }
        }

        $quantity = (int) $selectParticipantAndPlanning->planningQuantity - $orderPlanningQuantity;

        $cart->setProduct($package->getPlanning(), $quantity);
        $this->cartManager->save($cart);

        $packageStepDone = new StepDoneEvent($selectParticipantAndPlanning->sheet);
        $this->eventDispatcher->dispatch(Events::PACKAGE_STEP_DONE, $packageStepDone);
    }
}
