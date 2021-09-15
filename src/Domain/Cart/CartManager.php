<?php

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
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Participant\ParticipantProductSetter;
use Proximum\Vimeet\Domain\ProductAttributedToParticipant\ProductAttributedToParticipantSetter;
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

    /** @var ProductAttributedToParticipantSetter */
    private $productAttributedToParticipantSetter;

    public function __construct(
        CartRowRepositoryInterface $cartRowRepository,
        CartStepRepositoryInterface $cartStepRepository,
        PromotionCodeRowRepositoryInterface $promotionCodeRowRepository,
        Merger $orderMerger,
        ParticipantProductSetter $participantProductSetter,
        ProductAttributedToParticipantRepositoryInterface $productAttributedToParticipantRepository,
        ProductAttributedToParticipantSetter $productAttributedToParticipantSetter,
        DelayedEventDispatcherInterface $delayedEventDispatcher
    ) {
        $this->cartRowRepository = $cartRowRepository;
        $this->cartStepRepository = $cartStepRepository;
        $this->promotionCodeRowRepository = $promotionCodeRowRepository;
        $this->orderMerger = $orderMerger;
        $this->participantProductSetter = $participantProductSetter;
        $this->productAttributedToParticipantRepository = $productAttributedToParticipantRepository;
        $this->productAttributedToParticipantSetter = $productAttributedToParticipantSetter;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
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
     */
    public function updateOptionsQuantity(
        Cart $cart,
        OptionRow $optionRow,
        Product $product,
        ?Order $order,
        array $attributableOptionsIncludedByProductId = []
    ): void {
        $orderQuantity = $this->getPreviousOrderedQuantity($order, $product);

        if (!$product->isAttributable()) {
            $cart->setProduct($product, $optionRow->getQuantity() - $orderQuantity);

            return;
        }

        $this->updateAttributableProductQuantity(
            $cart,
            $optionRow,
            $product,
            $order,
            $orderQuantity,
            $attributableOptionsIncludedByProductId
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

    private function getQuantityToAddInCart(int $quantity, int $orderQuantity, int $includedQuantity): int
    {
        if ($quantity - $orderQuantity < 0) {
            // We must not take account of included quantity when $quantity - $orderQuantity is already negative.
            return $quantity - $orderQuantity;
        }

        return $quantity - $orderQuantity - $includedQuantity;
    }

    private function updateAttributableProductQuantity(
        Cart $cart,
        OptionRow $optionRow,
        Product $product,
        ?Order $order,
        int $orderQuantity,
        array $attributableOptionsIncludedByProductId = []
    ): void {
        $includedQuantity = $attributableOptionsIncludedByProductId[$product->getId()] ?? 0;
        $optionRowParticipants = $optionRow->getParticipants();
        $optionRowParticipantsIndexedByParticipantId = [];
        $sheetParticipants = $cart->getSheet()->getParticipantsArray();

        foreach ($optionRowParticipants as $participant) {
            $optionRowParticipantsIndexedByParticipantId[$participant->getId()] = $participant;
        }

        $quantity = $optionRow->getQuantity();
        $quantityToAddInCart = $this->getQuantityToAddInCart($quantity, $orderQuantity, $includedQuantity);

        // Quantity not changed on first run
        // Or quantity reset to 0
        if (0 === $quantity && 0 === $orderQuantity) {
            // No quantity selected but with included product, we need to check
            // if there is ProductAttributedToParticipant already created to remove them
            $this->productAttributedToParticipantSetter->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(
                $product,
                $sheetParticipants,
                [] // remove for all participants
            );

            return;
        }

        if (0 === ($quantity - $orderQuantity - $includedQuantity)) {
            $this->productAttributedToParticipantSetter->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(
                $product,
                $sheetParticipants,
                $optionRowParticipants
            );

            return;
        }

        // If the orderedQuantity is higher than 0
        // Then we do not change anything, we just set the new cartRow with the selected participant
        // The cart Converter will do the work to convert the new ProductAttributedToParticipant
        if ($orderQuantity > 0) {
            $cart->setProduct(
                $product,
                $quantityToAddInCart,
                $optionRowParticipants
            );

            return;
        }

        // If we decrement the quantity
        if ($quantity <= $includedQuantity) {
            // If nothing has change in term of quantity
            // We just check if the participant need to be reassigned
            // If the included quantity is higher than the selected quantity
            // And no quantity ordered
            $this->productAttributedToParticipantSetter->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(
                $product,
                $sheetParticipants,
                $optionRowParticipants
            );

            return;
        }

        // No previous ordered quantity
        // no included quantity
        // we just set the quantity selected to the cartRow
        if (0 === $includedQuantity) {
            $cart->setProduct(
                $product,
                $quantityToAddInCart,
                $optionRowParticipants
            );

            return;
        }

        // If there is a previous order
        // And the included quantity is not null
        // But the quantity is higher
        // We do not modify anything
        // we will let the Cart Converter do it stuff
        // We just create the cartRow
        if (null !== $order) {
            $cart->setProduct(
                $product,
                $quantityToAddInCart,
                $optionRowParticipants
            );

            return;
        }

        // If we reach here, it means that
        // There is no previous orderedQuantity
        // The included quantity is less than the selected quantity
        // The quantity is positive
        // No previous order
        $participantToProductAttributedToParticipantsToCreate = \array_slice(
            $optionRowParticipants,
            0,
            $includedQuantity
        );

        $this->productAttributedToParticipantSetter->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(
            $product,
            $sheetParticipants,
            $participantToProductAttributedToParticipantsToCreate
        );

        $cart->setProduct(
            $product,
            $quantityToAddInCart,
            $optionRowParticipants
        );
    }

    private function getPreviousOrderedQuantity(?Order $order, Product $product): int
    {
        if (null === $order) {
            return 0;
        }

        $orderRow = $order->getRowByProductId($product->getId());

        if (null === $orderRow) {
            return 0;
        }

        return $orderRow->getQuantity();
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
}
