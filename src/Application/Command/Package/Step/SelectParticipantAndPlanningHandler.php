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

        $orders = $sheet->getNotCancelledOrders();

        // Update planning cart row
        $orderPlanningQuantity = 0;

        if (count($orders) > 0) {
            $merged = $this->merger->merge($orders);

            if ($merged !== null ) {
                if ($orderRow = $merged->getRowForProduct($planningProduct)) {
                    $orderPlanningQuantity = $orderRow->getQuantity();
                }
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

        // @todo: take account of previous ordered Participant products

        // Reset cart row for participant product
        foreach($cart->getParticipantRows() as $participantCartRow) {
            $cart->removeRow($participantCartRow);
        }

        $availableParticipantProductsById = $this->getAvailableParticipantProductsById($sheet);

        // Set participant products in cart
        foreach ($participantsByProductId as $productId => $participants) {
            $cart->setProduct($availableParticipantProductsById[$productId], count($participants));
        }

        // Add link between participant and participant product added to the cart
        foreach ($cart->getParticipantRows() as $participantCartRow) {
            foreach ($participantsByProductId[$participantCartRow->getProduct()->getId()] as $participant) {
                $participantCartRow->addCartRowParticipant(new CartRowParticipant($participantCartRow, $participant));
            }
        }
    }
}
