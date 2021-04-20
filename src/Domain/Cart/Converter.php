<?php

namespace Proximum\Vimeet\Domain\Cart;

use Proximum\Vimeet\Application\Command\PromotionCode\DecrementStock;
use Proximum\Vimeet\Application\Command\PromotionCode\DecrementStockHandler;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\PromotionCodeRow;
use Proximum\Vimeet\Domain\Participant\ParticipantProductSetter;
use Proximum\Vimeet\Domain\ProductAttributedToParticipant\ProductAttributedToParticipantSetter;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\CartStepRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRowRepositoryInterface;

/**
 * Cart Converter to:
 * - Order
 */
class Converter
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var CartRowRepositoryInterface */
    private $cartRowRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var CartStepRepositoryInterface */
    private $cartStepRepository;

    /** @var PromotionCodeRowRepositoryInterface */
    private $promotionCodeRowRepository;

    /** @var ParticipantProductSetter */
    private $participantProductSetter;

    /** @var ProductAttributedToParticipantSetter */
    private $productAttributedToParticipantSetter;

    /** @var DecrementStockHandler */
    private $decrementStockHandler;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CartRowRepositoryInterface $cartRowRepository,
        CartStepRepositoryInterface $cartStepRepository,
        PromotionCodeRowRepositoryInterface $promotionCodeRowRepository,
        ParticipantProductSetter $participantProductSetter,
        ProductAttributedToParticipantSetter $productAttributedToParticipantSetter,
        DecrementStockHandler $decrementStockHandler,
        \DateTimeInterface $dateTime
    ) {
        $this->orderRepository = $orderRepository;
        $this->cartRowRepository = $cartRowRepository;
        $this->cartStepRepository = $cartStepRepository;
        $this->promotionCodeRowRepository = $promotionCodeRowRepository;
        $this->participantProductSetter = $participantProductSetter;
        $this->productAttributedToParticipantSetter = $productAttributedToParticipantSetter;
        $this->decrementStockHandler = $decrementStockHandler;
        $this->dateTime = $dateTime;
    }

    /**
     * @param Cart $cart
     *
     * @return Order
     */
    public function toOrder(Cart $cart): Order
    {
        $sheet = $cart->getSheet();
        $groupsData = $sheet->getPackage()->serializeData();

        $order = new Order(
            $sheet,
            $groupsData,
            $this->dateTime
        );

        foreach ($cart->getRows() as $cartRow) {
            $order->addRow($this->convertToRow($order, $cartRow));
        }

        foreach ($cart->getPromotionCodeRows() as $promotionCodeRow) {
            $promotionCodeOrderRows = $this->convertToPromotionCode($order, $cart, $promotionCodeRow);

            foreach ($promotionCodeOrderRows as $promotionCodeOrderRow) {
                $order->addPromotionCode($promotionCodeOrderRow);
            }

            $this->decrementStockHandler->handle(new DecrementStock($promotionCodeRow->getPromotionCode()));
        }

        $sheet->addOrder($order);
        $this->orderRepository->add($order);
        $this->emptyCart($cart);

        return $order;
    }

    /**
     * @param Order   $order
     * @param CartRow $cartRow
     *
     * @return Order\Row
     */
    private function convertToRow(Order $order, CartRow $cartRow): Order\Row
    {
        $group   = $order->getSheet()->getPackage()->getGroupOfProduct($cartRow->getProduct());
        $groupId = null;

        if (null !== $group) {
            $groupId = $group->getId();
        }

        // Add a link between Participant and Product of type 'participant'
        if ($cartRow->getProduct()->isParticipant()) {
            foreach ($cartRow->getParticipants() as $participant) {
                $this->participantProductSetter->setProductOnParticipant($participant, $cartRow->getProduct());
            }
        }

        // Attribute Product to Participant
        if ($cartRow->getProduct()->isAttributable()) {
            $this->productAttributedToParticipantSetter->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(
                $cartRow->getProduct(),
                $cartRow->getSheet()->getParticipantsArray(),
                $cartRow->getParticipants()
            );
        }

        return new Order\Row(
            $order,
            $cartRow->getQuantity(),
            $cartRow->getProduct()->getVat(),
            $cartRow->getProduct(),
            $groupId
        );
    }

    /**
     * @param Order            $order
     * @param Cart             $cart
     * @param PromotionCodeRow $promotionCodeRow
     *
     * @return Order\PromotionCode[]
     */
    private function convertToPromotionCode(Order $order, Cart $cart, PromotionCodeRow $promotionCodeRow): array
    {
        $promotionCodeRows = [];
        foreach ($promotionCodeRow->getPromotionCode()->getPromotions() as $promotion) {
            $discount = $cart->getDiscountForProduct($promotionCodeRow->getPromotionCode(), $promotion->getProduct());

            if ($discount < 0) {
                $promotionCodeRows[] =  new Order\PromotionCode(
                    $order,
                    $promotionCodeRow->getPromotionCode(),
                    $discount,
                    $promotion->getProduct(),
                    $promotion->getProduct()->getVat()
                );
            }
        }

        return $promotionCodeRows;
    }

    /**
     * @param Cart $cart
     */
    private function emptyCart(Cart $cart): void
    {
        $this->cartRowRepository->deleteForSheet($cart->getSheet());
        $this->cartStepRepository->deleteForSheet($cart->getSheet());
        $this->promotionCodeRowRepository->deleteForSheet($cart->getSheet());
    }
}
