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
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\CartRowParticipant;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
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

    /** @var array indexed by sheet id of null|Order */
    private $mergedOrder = [];

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

        $this->handleParticipants($cart, $selectParticipantAndPlanning->participantsProduct);
        $this->handlePlanning($cart, $selectParticipantAndPlanning->planningQuantity);
        $this->cartManager->save($cart);

        $packageStepDone = new StepDoneEvent($selectParticipantAndPlanning->sheet);
        $this->eventDispatcher->dispatch(Events::PACKAGE_STEP_DONE, $packageStepDone);
    }

    /**
     * @param Sheet $sheet
     *
     * @return null|Order
     */
    private function getMergedOrder(Sheet $sheet): ?Order
    {
        if (isset($this->mergedOrder[$sheet->getId()])) {
            return $this->mergedOrder[$sheet->getId()];
        }

        $orders = $sheet->getNotCancelledOrders();

        if (0 === count($orders)) {
            return null;
        }

        $this->mergedOrder[$sheet->getId()] = $this->merger->merge($orders);

        return $this->mergedOrder[$sheet->getId()];
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

        $mergedOrder = $this->getMergedOrder($sheet);

        if (null !== $mergedOrder) {
            if ($orderRow = $mergedOrder->getRowForProduct($planningProduct)) {
                $orderPlanningQuantity = $orderRow->getQuantity();
            }
        }

        $quantity = $planningQuantity - $orderPlanningQuantity;

        $cart->setProduct($planningProduct, $quantity);
    }

    /**
     * @param Sheet $sheet
     *
     * @return array
     */
    private function getAvailableParticipantProductsById(Sheet $sheet): array
    {
        $availableParticipantProductsById = [];

        foreach ($sheet->getPackage()->getParticipants() as $product) {
            $availableParticipantProductsById[$product->getId()] = $product;
        }

        return $availableParticipantProductsById;
    }

    /**
     * @param Sheet $sheet
     * @param array $productByParticipantId
     *
     * @return array
     */
    private function getParticipantsByProductId(Sheet $sheet, array $productByParticipantId): array
    {
        $participantsByProductId = [];

        foreach ($sheet->getParticipantsArray() as $participant) {
            if (!isset($productByParticipantId[$participant->getId()])) {
                continue;
            }

            $product = $productByParticipantId[$participant->getId()];

            if (!$product instanceof Product) {
                continue;
            }

            if (isset($participantsByProductId[$product->getId()])) {
                $participantsByProductId[$product->getId()][] = $participant;
                continue;
            }

            $participantsByProductId[$product->getId()] = [];
            $participantsByProductId[$product->getId()][] = $participant;
        }

        return $participantsByProductId;
    }

    /***
     * @param Cart  $cart
     * @param array $productByParticipantId
     */
    private function handleParticipants(Cart $cart, array $productByParticipantId): void
    {
        $sheet = $cart->getSheet();

        $participantsByProductId = $this->getParticipantsByProductId($sheet, $productByParticipantId);

        // Reset cart row for participant product
        foreach($cart->getParticipantRows() as $participantCartRow) {
            $cart->removeRow($participantCartRow);
        }

        $availableParticipantProductsById = $this->getAvailableParticipantProductsById($sheet);

        $mergedOrder = $this->getMergedOrder($sheet);

        foreach ($participantsByProductId as $productId => $participants) {
            $quantityToAdd = count($participants);

            // take account of previous ordered Participant products
            if (null !== $mergedOrder) {
                $row = $mergedOrder->getRowByProductId($productId);

                if (null !== $row) {
                    $quantityToAdd = $quantityToAdd - $row->getQuantity();
                }
            }

            if ($quantityToAdd > 0) {
                // Set participant products in cart
                $cart->setProduct($availableParticipantProductsById[$productId], $quantityToAdd);
            }
        }

        // Add a negative quantity for participant product not used anymore
        if (null !== $mergedOrder) {
            $participantRows = $mergedOrder->getRowsProductOfParticipantType();

            foreach ($participantRows as $participantRow) {
                if (!isset($participantsByProductId[$participantRow->getProduct()->getId()])) {
                    $cart->setProduct($participantRow->getProduct(), -1 * $participantRow->getQuantity());
                } else {
                    $previousQuantity = $participantRow->getQuantity();
                    $newQuantity = count($participantsByProductId[$participantRow->getProduct()->getId()]);
                    $quantityToRemove = $previousQuantity - $newQuantity;

                    if ($quantityToRemove > 0) {
                        $cart->setProduct($participantRow->getProduct(), -1 * $quantityToRemove);
                    }
                }
            }
        }

        // Add a link between Participant and Product of type 'participant'
        // when no participant's product where added or removed in the cart.
        // It allows to remove a participant, add another one then re-assign the same participant's product
        // to the new participant.
        // It allows also to witch products between participants.
        if (empty($cart->getParticipantRows())) {
            foreach ($participantsByProductId as $productId => $participants) {
                if (isset($availableParticipantProductsById[$productId])
                    && $availableParticipantProductsById[$productId] instanceof Product
                ) {
                    foreach ($participants as $participant) {
                        if ($participant instanceof Participant) {
                            $participant->setParticipantProduct($availableParticipantProductsById[$productId]);
                        }
                    }
                }
            }
        } else {
            // Add link between participant and participant product added to the cart
            foreach ($cart->getParticipantRows() as $participantCartRow) {
                if (isset($participantsByProductId[$participantCartRow->getProduct()->getId()])) {
                    foreach ($participantsByProductId[$participantCartRow->getProduct()->getId()] as $participant) {
                        $participantCartRow->addCartRowParticipant(
                            new CartRowParticipant($participantCartRow, $participant)
                        );
                    }
                }
            }
        }
    }
}
