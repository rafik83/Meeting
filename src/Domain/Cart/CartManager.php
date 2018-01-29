<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Cart;

use Proximum\Vimeet\Domain\Model\CartRowParticipant;
use Proximum\Vimeet\Domain\Model\CartStep;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\CartStepRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRowRepositoryInterface;

class CartManager
{
    /** @var CartRowRepositoryInterface */
    private $cartRowRepository;

    /** @var CartStepRepositoryInterface */
    private $cartStepRepository;

    /** @var PromotionCodeRowRepositoryInterface */
    private $promotionCodeRowRepository;

    /** @var Merger */
    private $orderMerger;

    /**
     * @param CartRowRepositoryInterface          $cartRowRepository
     * @param CartStepRepositoryInterface         $cartStepRepository
     * @param PromotionCodeRowRepositoryInterface $promotionCodeRowRepository
     * @param Merger                              $orderMerger
     */
    public function __construct(
        CartRowRepositoryInterface $cartRowRepository,
        CartStepRepositoryInterface $cartStepRepository,
        PromotionCodeRowRepositoryInterface $promotionCodeRowRepository,
        Merger $orderMerger
    ) {
        $this->cartRowRepository          = $cartRowRepository;
        $this->cartStepRepository         = $cartStepRepository;
        $this->promotionCodeRowRepository = $promotionCodeRowRepository;
        $this->orderMerger                = $orderMerger;
    }

    /**
     * @param Sheet $sheet
     * @param int   $currentStep
     *
     * @return Cart
     */
    public function getCart(Sheet $sheet, $currentStep = null)
    {
        return new Cart(
            $sheet,
            $this->cartRowRepository->findBySheet($sheet),
            $this->promotionCodeRowRepository->findBySheet($sheet),
            $currentStep
        );
    }

    /***
     * @param Cart  $cart
     * @param array $productByParticipantId array of participantId => Product
     *
     * @return Cart
     */
    public function updateParticipantsQuantity(Cart $cart, array &$productByParticipantId): Cart
    {
        $sheet = $cart->getSheet();

        $participantsByProductId = $this->getParticipantsByProductId($sheet, $productByParticipantId);

        // Reset cart row for participant product
        foreach($cart->getParticipantRows() as $participantCartRow) {
            $cart->removeRow($participantCartRow);
        }

        $availableParticipantProductsById = $this->getAvailableParticipantProductsById($sheet);

        $mergedOrder = $this->orderMerger->getMergedOrders($sheet);
        $participantsIncludedByProductId = $this->getParticipantsIncludedByProductId($cart, $mergedOrder);

        $this->saveNeededParticipantProductsToCart(
            $cart,
            $mergedOrder,
            $availableParticipantProductsById,
            $participantsByProductId,
            $participantsIncludedByProductId
        );

        $this->saveUnNeededParticipantProductsToCart(
            $cart,
            $mergedOrder,
            $availableParticipantProductsById,
            $participantsByProductId
        );

        return $cart;
    }

    /**
     * @param Cart       $cart
     * @param null|Order $order
     * @param array      $availableParticipantProductsById
     * @param array      $participantsByProductId
     * @param array      $participantsIncludedByProductId
     */
    private function saveNeededParticipantProductsToCart(
        Cart $cart,
        ?Order $order = null,
        array &$availableParticipantProductsById,
        array &$participantsByProductId,
        array &$participantsIncludedByProductId
    ) {
        foreach ($participantsByProductId as $productId => $participants) {
            $participantProduct = $availableParticipantProductsById[$productId];
            $quantityToAdd = count($participants);

            // Take account of previous ordered Participant products
            if (null !== $order) {
                $row = $order->getRowByProductId($productId);

                if (null !== $row) {
                    // Take account of previous ordered quantity for this product
                    $quantityToAdd = $quantityToAdd - $row->getQuantity();
                }
            }

            if (isset($participantsIncludedByProductId[$productId])) {
                // Get included quantity for this product
                $quantityToAdd = $quantityToAdd - $participantsIncludedByProductId[$productId];
            }

            if ($quantityToAdd > 0) {
                $cart->setProduct($participantProduct, $quantityToAdd);

                continue;
            }

            if ($quantityToAdd <= 0) {
                foreach ($participants as $participant) {
                    if ($participant instanceof Participant) {
                        $participant->setParticipantProduct($availableParticipantProductsById[$productId]);
                    }
                }
            }
        }

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

    /**
     * @param Cart       $cart
     * @param null|Order $order
     * @param array      $availableParticipantProductsById
     * @param array      $participantsByProductId
     */
    private function saveUnNeededParticipantProductsToCart(
        Cart $cart,
        ?Order $order = null,
        array &$availableParticipantProductsById,
        array &$participantsByProductId
    ) {
        if (null === $order) {
            return;
        }

        $participantRows = $order->getRowsProductOfParticipantType();

        foreach ($participantRows as $participantRow) {
            if (!isset($participantsByProductId[$participantRow->getProduct()->getId()])) {
                // Remove all previous ordered quantity for this product
                $cart->setProduct($participantRow->getProduct(), -1 * $participantRow->getQuantity());

                continue;
            }

            $productId = $participantRow->getProduct()->getId();
            $previousQuantity = $participantRow->getQuantity();
            $newQuantity = count($participantsByProductId[$productId]);
            $quantityToRemove = $previousQuantity - $newQuantity;

            // Add a link between Participant and Product of type 'participant'
            // when no participant's product where added or removed in the cart.
            // It allows to remove a participant, add another one then re-assign the same participant's product
            // to the new participant.
            // It allows also to switch products between participants.
            if (0 === $quantityToRemove) {
                foreach ($participantsByProductId[$productId] as $participant) {
                    if ($participant instanceof Participant
                        && isset($availableParticipantProductsById[$productId])
                        && $availableParticipantProductsById[$productId] instanceof Product
                    ) {
                        $participant->setParticipantProduct($availableParticipantProductsById[$productId]);
                    }
                }
            }

            // Remove quantity for a participant product and add a row in the cart
            if ($quantityToRemove > 0) {
                $cart->setProduct($participantRow->getProduct(), -1 * $quantityToRemove);
            }
        }
    }

    /**
     * @param Cart  $cart
     * @param Order $order
     *
     * @return array of quantity indexed by Participant Product id
     */
    private function getParticipantsIncludedByProductId(Cart $cart, ?Order $order = null): array
    {
        if (null !== $order) {
            $includedParticipantProducts = $order->getIncludedParticipantProducts();
        } else {
            $includedParticipantProducts = $cart->getIncludedParticipantProducts();
        }

        $participantsIncludedByProductId = [];

        foreach ($includedParticipantProducts as $includedParticipantProduct) {
            $participantsIncludedByProductId[$includedParticipantProduct->getIncluded()->getId()] = $includedParticipantProduct->getQuantity();
        }

        return $participantsIncludedByProductId;
    }

    /**
     * @param Sheet $sheet
     *
     * @return array
     */
    private function getAvailableParticipantProductsById(Sheet $sheet): array
    {
        $availableParticipantProductsById = [];
        $package = $sheet->getPackage();

        foreach ($package->getParticipants() as $product) {
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
    private function getParticipantsByProductId(Sheet $sheet, array &$productByParticipantId): array
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

    /**
     * @param Cart $cart
     */
    public function save(Cart $cart)
    {
        // Save / add rows
        foreach ($cart->getRows() as $row) {
            if ($row->getId()) {
                $this->cartRowRepository->set($row);
            } else {
                $this->cartRowRepository->add($row);
            }
        }

        // Save / add promotion code rows
        foreach ($cart->getPromotionCodeRows() as $promotionCodeRow) {
            if ($promotionCodeRow->getId()) {
                // Remove promotionCodeRow if the discount is not usable anymore
                if (0 > $cart->getDiscount($promotionCodeRow->getPromotionCode())) {
                    $this->promotionCodeRowRepository->set($promotionCodeRow);
                } else {
                    $this->promotionCodeRowRepository->delete($promotionCodeRow);
                }
            } else {
                $this->promotionCodeRowRepository->add($promotionCodeRow);
            }
        }

        // Remove deleted rows
        $this->cartRowRepository->deleteWhereNotIn($cart->getSheet(), $cart->getRows());

        // Increment current step
        $cartStep = $this->cartStepRepository->findBySheet($cart->getSheet());

        if (null !== $cart->getCurrentStep()
            && null !== $cartStep
            && $cartStep->getCurrentStep() === $cart->getCurrentStep()
        ) {
            $cartStep->setCurrentStep($cartStep->getCurrentStep() + 1);
            $this->cartStepRepository->set($cartStep);
        } elseif (null !== $cart->getCurrentStep() && null === $cartStep) {
            $cartStep = new CartStep($cart->getSheet(), 2);
            $this->cartStepRepository->add($cartStep);
        }
    }

    /**
     * @param Cart $cart
     */
    public function deleteCartStep(Cart $cart)
    {
        $this->cartStepRepository->deleteForSheet($cart->getSheet());
    }
}
