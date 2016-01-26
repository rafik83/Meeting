<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Application\Components\Order\OrderManager;
use Proximum\Vimeet\Application\Components\Payment\PaymentMode;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class AddNegativeHandler
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var OrderManager
     */
    private $orderManager;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SheetRepositoryInterface $sheetRepository
     * @param OrderManager             $orderManager
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SheetRepositoryInterface $sheetRepository,
        OrderManager $orderManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->sheetRepository = $sheetRepository;
        $this->orderManager    = $orderManager;
    }

    /**
     * @param AddNegative $addNegative
     */
    public function handle(AddNegative $addNegative)
    {
        $order = new Order(
            $addNegative->sheet,
            Order::STATE_PAID,
            $addNegative->packageData,
            $addNegative->sheet->getTypePackageTemplate(),
            $addNegative->sheet->getBillingData(),
            $addNegative->sheet->getEvent()->getBillingTemplate(),
            $addNegative->createdAt,
            PaymentMode::NOPAYMENT
        );

        $this->orderRepository->add($order);

        $sheetData = $this->orderManager->mergeTwoPackageData(
            $addNegative->sheet->getPackageData(),
            $addNegative->packageData
        );

        $addNegative->sheet->setPackageData($sheetData);
        $this->sheetRepository->set($addNegative->sheet);
    }
}
