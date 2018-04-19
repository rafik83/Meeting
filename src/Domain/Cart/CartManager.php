<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Cart;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Package\Step\OptionRow;
use Proximum\Vimeet\Application\Event\Cart\ParticipantCartRowAddedEvent;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\CartRowParticipant;
use Proximum\Vimeet\Domain\Model\CartStep;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\ProductAttributedToParticipant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Participant\ParticipantProductSetter;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\CartStepRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;
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

    /** @var ParticipantProductSetter */
    private $participantProductSetter;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    /** @var ProductAttributedToParticipantRepositoryInterface */
    private $productAttributedToParticipantRepository;

    /**
     * @param CartRowRepositoryInterface                        $cartRowRepository
     * @param CartStepRepositoryInterface                       $cartStepRepository
     * @param PromotionCodeRowRepositoryInterface               $promotionCodeRowRepository
     * @param Merger                                            $orderMerger
     * @param ParticipantProductSetter                          $participantProductSetter
     * @param ProductAttributedToParticipantRepositoryInterface $productAttributedToParticipantRepository
     * @param DelayedEventDispatcherInterface                   $delayedEventDispatcher
     */
    public function __construct(
        CartRowRepositoryInterface $cartRowRepository,
        CartStepRepositoryInterface $cartStepRepository,
        PromotionCodeRowRepositoryInterface $promotionCodeRowRepository,
        Merger $orderMerger,
        ParticipantProductSetter $participantProductSetter,
        ProductAttributedToParticipantRepositoryInterface $productAttributedToParticipantRepository,
        DelayedEventDispatcherInterface $delayedEventDispatcher
    ) {
        $this->cartRowRepository          = $cartRowRepository;
        $this->cartStepRepository         = $cartStepRepository;
        $this->promotionCodeRowRepository = $promotionCodeRowRepository;
        $this->orderMerger                = $orderMerger;
        $this->participantProductSetter = $participantProductSetter;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
        $this->productAttributedToParticipantRepository = $productAttributedToParticipantRepository;
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
     * @param Cart       $cart
     * @param OptionRow  $optionRow
     * @param Product    $product
     * @param null|Order $order
     * @param array      $attributableOptionsIncludedByProductId
     *
     * @return Cart
     */
    public function updateOptionsQuantity(
        Cart $cart,
        OptionRow $optionRow,
        Product $product,
        ?Order $order,
        array $attributableOptionsIncludedByProductId = []
    ): Cart {
        if (!$product->isAttributable()) {
            $cart->setProduct($product, $optionRow->getQuantity());

            return $cart;
        }

        $orderQuantity = 0;
        $includedQuantity = $attributableOptionsIncludedByProductId[$product->getId()] ?? 0;
        $optionRowParticipants = $optionRow->getParticipants();
        $optionRowParticipantsIndexedByParticipantId = [];

        foreach ($optionRowParticipants as $participant) {
            $optionRowParticipantsIndexedByParticipantId[$participant->getId()] = $participant;
        }

        // handle new order
        if (null !== $order) {
            $orderRow = $order->getRowByProductId($product->getId());

            if (null !== $orderRow) {
                $orderQuantity = $orderRow->getQuantity();
            }
        }

        $quantity = $optionRow->getQuantity();

        // Quantity not changed on first run
        // Or quantity reset to 0
        if ($quantity === 0) {
            // No quantity selected, no previous order and no included quantity, nothing to do
            if ($orderQuantity === 0 && $includedQuantity === 0) {
                return $cart;
            }

            $productAttributedToParticipants = $this->getProductAttributedToParticipants($optionRow, $product);

            // No quantity selected but with included product, we need to check
            // if there is ProductAttributedToParticipant already created to remove them
            if ($includedQuantity > 0 && $orderQuantity === 0) {
                $this->productAttributedToParticipantRepository->remove($productAttributedToParticipants);
            }

            // If there is a previous order quantity, we create a cartRow with this negative quantity
            if ($orderQuantity !== 0) {
                if ($includedQuantity > 0) {
                    if ($includedQuantity >= \count($productAttributedToParticipants)) {
                        $this->productAttributedToParticipantRepository->remove($productAttributedToParticipants);

                        $productAttributedToParticipants = [];
                    } else {
                        $productToRemove = \array_slice(
                            $productAttributedToParticipants,
                            0,
                            $includedQuantity
                        );
                        $productForCartRow = \array_slice(
                            $productAttributedToParticipants,
                            $includedQuantity,
                            \count($productAttributedToParticipants)
                        );

                        $this->productAttributedToParticipantRepository->remove($productToRemove);

                        $productAttributedToParticipants = $productForCartRow;
                    }
                }

                $cart->setProduct(
                    $product,
                    - $orderQuantity,
                    array_map(function (ProductAttributedToParticipant $productAttributedToParticipant) {
                        return $productAttributedToParticipant->getParticipant();
                    }, $productAttributedToParticipants)
                );
            }

            return $cart;
        }

        // If we decrement the quantity
        if ($quantity <= ($orderQuantity + $includedQuantity)) {
            $productAttributedToParticipants = $this->getProductAttributedToParticipants($optionRow, $product);

            // If nothing has change in term of quantity
            // We just check if the participant need to be reassigned
            if ($quantity === ($orderQuantity + $includedQuantity)) {
                $this->reAssignProductAttributedToParticipant(
                    $product,
                    $productAttributedToParticipants,
                    $optionRowParticipantsIndexedByParticipantId
                );

                return $cart;
            }

            // If the included quantity is higher than the selected quantity
            if ($includedQuantity >= $quantity) {
                // we need to check if the participants have changed
                $this->reAssignProductAttributedToParticipant(
                    $product,
                    $productAttributedToParticipants,
                    $optionRowParticipantsIndexedByParticipantId
                );

                // If no quantity ordered
                if ($orderQuantity === 0) {
                    return $cart;
                }

                // In this case, the included quantity is equal or higher and there is previous order Row to remove
                // And no participant need to be removed
                $cart->setProduct(
                    $product,
                    - $orderQuantity,
                    []
                );

                return $cart;
            }

            // If we reach here, it means that the quantity is less than
            // the ordered quantity and the included product
            // And the included product is less than the quantity
            // Therefore, we need remove the participant with a ProductAttributedToParticipant
            // And not anymore in the selected participant
            $cart->setProduct(
                $product,
                - $orderQuantity,
                []
            );

            return $cart;
        }

        return $cart;
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
    ): void {
        $previousCartProductByParticipantId = $this->getPreviousCartProductByParticipantId($cart);
        $this->removeCartRowForParticipantProduct($cart);

        foreach ($participantsByProductId as $productId => $participants) {
            if (!isset($availableParticipantProductsById[$productId])) {
                continue;
            }

            $participantProduct = $availableParticipantProductsById[$productId];
            $quantityToAdd = \count($participants);

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
                        $this->participantProductSetter->setProductOnParticipant(
                            $participant,
                            $availableParticipantProductsById[$productId]
                        );
                    }
                }
            }
        }

        // Add link between participant and participant product added to the cart
        foreach ($cart->getParticipantRows() as $participantCartRow) {
            if (isset($participantsByProductId[$participantCartRow->getProduct()->getId()])) {
                /** @var Participant $participant */
                foreach ($participantsByProductId[$participantCartRow->getProduct()->getId()] as $participant) {
                    $participantCartRow->addCartRowParticipant(
                        new CartRowParticipant($participantCartRow, $participant)
                    );

                    $event = new ParticipantCartRowAddedEvent(
                        $participant,
                        $participant->hasParticipantProduct(),
                        (
                            isset($previousCartProductByParticipantId[$participant->getId()])
                            && $previousCartProductByParticipantId[$participant->getId()] === $participantCartRow->getProduct()
                        )
                    );

                    $this->delayedEventDispatcher->dispatch(Events::PARTICIPANT_CART_ROW_ADDED, $event);
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
    ): void {
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
            $newQuantity = \count($participantsByProductId[$productId]);
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
                        $this->participantProductSetter->setProductOnParticipant(
                            $participant,
                            $availableParticipantProductsById[$productId]
                        );
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
     * @param Cart  $cart
     * @param Order $order
     *
     * @return array of quantity indexed by Attributable Option Product id
     */
    public function getAttributableOptionsIncludedByProductId(Cart $cart, ?Order $order = null): array
    {
        if (null !== $order) {
            $includedAttributableOptionProducts = $order->getIncludedAttributableOptionProducts();
        } else {
            $includedAttributableOptionProducts = $cart->getIncludedAttributableOptionProducts();
        }

        $optionsAttributableIncludedByProductId = [];

        foreach ($includedAttributableOptionProducts as $includedAttributableOptionProduct) {
            $optionsAttributableIncludedByProductId[$includedAttributableOptionProduct->getIncluded()->getId()] = $includedAttributableOptionProduct->getQuantity();
        }

        return $optionsAttributableIncludedByProductId;
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
    public function save(Cart $cart): void
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

    /**
     * @param Sheet $sheet
     */
    public function emptyCart(Sheet $sheet): void
    {
        $this->cartStepRepository->deleteForSheet($sheet);
        $this->cartRowRepository->deleteForSheet($sheet);
    }

    /**
     * This methods return an array indexed by participant id of the product in cart for this participant before
     *
     * @param Cart $cart
     *
     * @return array of [ 1(participantId) => Product]
     */
    private function getPreviousCartProductByParticipantId(Cart $cart): array
    {
        $previousProductInCartByParticipantId = [];

        foreach ($cart->getParticipantRows() as $participantRow) {
            foreach ($participantRow->getParticipants() as $participant) {
                $previousProductInCartByParticipantId[$participant->getId()] = $participantRow->getProduct();
            }
        }

        return $previousProductInCartByParticipantId;
    }

    /**
     * @param Cart $cart
     */
    private function removeCartRowForParticipantProduct(Cart $cart): void
    {
        foreach ($cart->getParticipantRows() as $participantCartRow) {
            $cart->removeRow($participantCartRow);
        }
    }

    /**
     * @param OptionRow $optionRow
     * @param Product   $product
     *
     * @return ProductAttributedToParticipant[] indexed by Participant id
     */
    private function getProductAttributedToParticipants(OptionRow $optionRow, Product $product): array
    {
        $productAttributedToParticipants = $this->productAttributedToParticipantRepository->findByProductAndParticipants($product, $optionRow->getParticipants());

        $productAttributedToParticipantsIndexByParticipantId = [];

        foreach ($productAttributedToParticipants as $productAttributedToParticipant) {
            $productAttributedToParticipantsIndexByParticipantId[$productAttributedToParticipant->getParticipant()->getId()] = $productAttributedToParticipant;
        }

        return $productAttributedToParticipantsIndexByParticipantId;
    }

    /**
     * @param Product                          $product
     * @param ProductAttributedToParticipant[] $productAttributedToParticipants
     * @param Participant[]                    $optionRowParticipantsIndexedByParticipantId
     */
    private function reAssignProductAttributedToParticipant(
        Product $product,
        array $productAttributedToParticipants,
        array $optionRowParticipantsIndexedByParticipantId
    ): void {
        $productAttributedToParticipantsToCreate = $this->getProductAttributedToParticipantToCreate(
            $product,
            $productAttributedToParticipants,
            $optionRowParticipantsIndexedByParticipantId
        );
        $productAttributedToParticipantsToRemove = $this->getProductAttributedToParticipantToRemove(
            $productAttributedToParticipants,
            $optionRowParticipantsIndexedByParticipantId
        );

        foreach ($productAttributedToParticipantsToCreate as $productAttributedToParticipantToCreate) {
            $this->productAttributedToParticipantRepository->add($productAttributedToParticipantToCreate);
        }

        $this->productAttributedToParticipantRepository->remove($productAttributedToParticipantsToRemove);
    }

    /**
     * @param ProductAttributedToParticipant[] $productAttributedToParticipants
     * @param Participant[]                    $optionRowParticipantsIndexedByParticipantId
     *
     * @return ProductAttributedToParticipant[]
     */
    private function getProductAttributedToParticipantToRemove(
        array $productAttributedToParticipants,
        array $optionRowParticipantsIndexedByParticipantId
    ): array {
        $productAttributedToParticipantsToRemove = [];

        foreach ($productAttributedToParticipants as $participantId => $productAttributedToParticipant) {
            if (!isset($optionRowParticipantsIndexedByParticipantId[$participantId])) {
                $productAttributedToParticipantsToRemove[$participantId] = $productAttributedToParticipant;
            }
        }

        return $productAttributedToParticipantsToRemove;
    }

    /**
     * @param Product                          $product
     * @param ProductAttributedToParticipant[] $productAttributedToParticipants
     * @param Participant[]                    $optionRowParticipants
     *
     * @return ProductAttributedToParticipant[]
     */
    private function getProductAttributedToParticipantToCreate(
        Product $product,
        array $productAttributedToParticipants,
        array $optionRowParticipants
    ): array {
        $productAttributedToParticipantsToCreate = [];

        foreach ($optionRowParticipants as $participant) {
            // Already set
            if (isset($productAttributedToParticipants[$participant->getId()])) {
                continue;
            }

            // Not set
            if (!isset($productAttributedToParticipants[$participant->getId()])) {
                $productAttributedToParticipantsToCreate[$participant->getId()] = new ProductAttributedToParticipant(
                    $product,
                    $participant,
                    new \DateTime() //@todo inject $dateTime
                );
            }
        }

        return $productAttributedToParticipantsToCreate;
    }
}
