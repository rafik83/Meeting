<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class OrderManager
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var SheetManager */
    private $sheetManager;

    /** @var BillingInfoManager */
    private $billingInfoManager;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SheetManager             $sheetManager
     * @param BillingInfoManager       $billingInfoManager
     * @param \DateTimeInterface       $dateTime
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SheetManager $sheetManager,
        BillingInfoManager $billingInfoManager,
        \DateTimeInterface $dateTime
    ) {
        $this->orderRepository = $orderRepository;
        $this->sheetManager = $sheetManager;
        $this->billingInfoManager = $billingInfoManager;
        $this->dateTime = $dateTime;
    }

    /**
     * @param Event      $event
     * @param float      $total
     * @param bool       $isVatApplicable
     * @param Sheet|null $sheet
     *
     * @throws \Exception
     *
     * @return Order
     */
    public function createOrderOfGivenTotal(
        Event $event,
        $total,
        bool $isVatApplicable,
        Sheet $sheet = null
    ): Order {
        if (null === $sheet) {
            $sheet = $this->sheetManager->create($event);

            if ('FR' !== $event->getCountry()) {
                throw new \Exception('Event must have country=FR in order to manage is VAT applicable or not.');
            }

            if (true === $isVatApplicable) {
                $this->billingInfoManager->create($sheet, 'FR');
            } else {
                $this->billingInfoManager->create($sheet, 'US');
            }
        }

        $order = new Order($sheet, '', $this->dateTime);
        $this->createOrderRow($order, 'My product label', $total, 1, $sheet->getEvent()->getVat());
        $this->orderRepository->add($order);

        return $order;
    }

    /**
     * @param Order  $order
     * @param string $label
     * @param float  $price
     * @param int    $quantity
     * @param float  $vatRate
     *
     * @return Order\Row
     */
    public function createOrderRow(Order $order, string $label, float $price, int $quantity, float $vatRate): Order\Row
    {
        $orderRow = new Order\Row($order, $quantity, $vatRate, null, null, $label, $price);
        $order->addRow($orderRow);

        return $orderRow;
    }
}
