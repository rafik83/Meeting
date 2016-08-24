<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Cart;

use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Address;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Model\PromotionCodeRow;
use Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException;
use Proximum\Vimeet\Domain\Package\Specification\VatApplicable;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\CartStepRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRowRepositoryInterface;

/**
 * Cart Converter to:
 * - Order
 */
class Converter
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CartRowRepositoryInterface
     */
    private $cartRowRepository;

    /**
     * @var VatApplicable
     */
    private $vatApplicable;

    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * @var BillingInfoRepositoryInterface
     */
    private $billingInfoRepository;

    /**
     * @var CartStepRepositoryInterface
     */
    private $cartStepRepository;

    /**
     * @var PromotionCodeRowRepositoryInterface
     */
    private $promotionCodeRowRepository;

    /**
     * @var PromotionCodeRepositoryInterface
     */
    private $promotionCodeRepository;

    /**
     * @param OrderRepositoryInterface            $orderRepository
     * @param CartRowRepositoryInterface          $cartRowRepository
     * @param CartStepRepositoryInterface         $cartStepRepository
     * @param BillingInfoRepositoryInterface      $billingInfoRepository
     * @param PromotionCodeRowRepositoryInterface $promotionCodeRowRepository
     * @param PromotionCodeRepositoryInterface    $promotionCodeRepository
     * @param VatApplicable                       $vatApplicable
     * @param \DateTimeInterface                  $datetime
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CartRowRepositoryInterface $cartRowRepository,
        CartStepRepositoryInterface $cartStepRepository,
        BillingInfoRepositoryInterface $billingInfoRepository,
        PromotionCodeRowRepositoryInterface $promotionCodeRowRepository,
        PromotionCodeRepositoryInterface $promotionCodeRepository,
        VatApplicable $vatApplicable,
        \DateTimeInterface $datetime
    ) {
        $this->orderRepository            = $orderRepository;
        $this->cartRowRepository          = $cartRowRepository;
        $this->cartStepRepository         = $cartStepRepository;
        $this->billingInfoRepository      = $billingInfoRepository;
        $this->promotionCodeRowRepository = $promotionCodeRowRepository;
        $this->promotionCodeRepository    = $promotionCodeRepository;
        $this->vatApplicable              = $vatApplicable;
        $this->datetime                   = $datetime;
    }

    /**
     * @param Cart $cart
     *
     * @throws MissingBillingInfoException
     *
     * @return Order
     */
    public function toOrder(Cart $cart)
    {
        $sheet = $cart->getSheet();

        $billingInfo = $this->billingInfoRepository->getBySheet($sheet);

        if (null === $billingInfo) {
            throw new MissingBillingInfoException('Can not convert cart to order, missing billing info');
        }

        $orderBillingInfo = new Order\BillingInfo(
            $billingInfo->getGender(),
            $billingInfo->getLastname(),
            $billingInfo->getFirstname(),
            $billingInfo->getFunction(),
            $billingInfo->getPhone(),
            $billingInfo->getMobile(),
            $billingInfo->getEmail(),
            $billingInfo->getCompany(),
            new Address(
                $billingInfo->getAddress()->getStreet(),
                $billingInfo->getAddress()->getZipcode(),
                $billingInfo->getAddress()->getCity(),
                $billingInfo->getAddress()->getCountry()
            ),
            $billingInfo->getVatNumber()
        );

        $groupsData = $sheet->getPackage()->serializeData();

        $order = new Order(
            $sheet,
            $this->vatApplicable->onCart($cart),
            $orderBillingInfo,
            $groupsData,
            $this->datetime
        );

        foreach ($cart->getRows() as $cartRow) {
            $order->addRow($this->convertToRow($order, $cartRow));
        }

        foreach ($cart->getPromotionCodeRows() as $promotionCodeRow) {
            $order->addPromotionCode(
                $this->convertToPromotionCode($order, $cart, $promotionCodeRow)
            );
            $this->decrementStockPromotionCode($promotionCodeRow->getPromotionCode());
        }

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
    private function convertToRow(Order $order, CartRow $cartRow)
    {
        $group   = $order->getSheet()->getPackage()->getGroupOfProduct($cartRow->getProduct());
        $groupId = null;

        if (null !== $group) {
            $groupId = $group->getId();
        }

        return new Order\Row(
            $order,
            $cartRow->getQuantity(),
            $cartRow->getProduct(),
            $groupId
        );
    }

    /**
     * @param Order            $order
     * @param Cart             $cart
     * @param PromotionCodeRow $promotionCodeRow
     *
     * @return Order\PromotionCode
     */
    private function convertToPromotionCode(Order $order, Cart $cart, PromotionCodeRow $promotionCodeRow)
    {
        $discount = $cart->getDiscount($promotionCodeRow->getPromotionCode());

        return new Order\PromotionCode(
            $order,
            $promotionCodeRow->getPromotionCode(),
            $discount
        );
    }

    /**
     * @param Cart $cart
     */
    private function emptyCart(Cart $cart)
    {
        $this->cartRowRepository->deleteForSheet($cart->getSheet());
        $this->cartStepRepository->deleteForSheet($cart->getSheet());
        $this->promotionCodeRowRepository->deleteForSheet($cart->getSheet());
    }

    /**
     * @param PromotionCode $promotionCode
     */
    private function decrementStockPromotionCode(PromotionCode $promotionCode)
    {
        $stock = $promotionCode->getStock();

        if (null !== $stock && $stock > 0) {
            $promotionCode->setStock($stock - 1);
            $this->promotionCodeRepository->set($promotionCode);
        }
    }
}
