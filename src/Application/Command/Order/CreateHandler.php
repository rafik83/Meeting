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
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Repository\CartRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class CreateHandler
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
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    private $orderManager;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SheetRepositoryInterface $sheetRepository
     * @param CartRepositoryInterface  $cartRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SheetRepositoryInterface $sheetRepository,
        CartRepositoryInterface $cartRepository,
        OrderManager $orderManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->sheetRepository = $sheetRepository;
        $this->cartRepository  = $cartRepository;
        $this->orderManager    = $orderManager;
    }

    public function handle(Create $create)
    {
        $this->orderRepository->add(new Order(
            $create->sheet,
            $create->state,
            $create->proFormaTemplate,
            $create->packageData,
            $create->packageTemplate,
            $create->billingData,
            $create->billingTemplate,
            $create->createdAt,
            $create->paymentMode
        ));

        $this->cartRepository->delete($create->cart);

        $sheetData = $this->orderManager->mergeTwoPackageData($create->sheet->getPackageData(), $create->packageData);

        $create->sheet->setPackageData($sheetData);
        $this->sheetRepository->set($create->sheet);
    }
}
