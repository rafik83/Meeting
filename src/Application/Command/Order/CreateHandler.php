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
use Proximum\Vimeet\Application\Components\Participant\ParticipantManager;
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

    /**
     * @var OrderManager
     */
    private $orderManager;

    /**
     * @var ParticipantManager
     */
    private $participantManager;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SheetRepositoryInterface $sheetRepository
     * @param CartRepositoryInterface  $cartRepository
     * @param OrderManager             $orderManager
     * @param ParticipantManager       $participantManager
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SheetRepositoryInterface $sheetRepository,
        CartRepositoryInterface $cartRepository,
        OrderManager $orderManager,
        ParticipantManager $participantManager
    ) {
        $this->orderRepository    = $orderRepository;
        $this->sheetRepository    = $sheetRepository;
        $this->cartRepository     = $cartRepository;
        $this->orderManager       = $orderManager;
        $this->participantManager = $participantManager;
    }

    public function handle(Create $create)
    {
        $order = new Order(
            $create->sheet,
            $create->state,
            $create->proFormaTemplate,
            $create->packageData,
            $create->packageTemplate,
            $create->billingData,
            $create->billingTemplate,
            $create->createdAt,
            $create->paymentMode
        );

        $this->orderRepository->add($order);

        $this->participantManager->convertInactiveParticipantAfterOrderCreation($create->sheet, $order);

        $sheetData = $this->orderManager->mergeTwoPackageData($create->sheet->getPackageData(), $create->packageData);

        $this->cartRepository->delete($create->cart);

        $create->sheet->setPackageData($sheetData);
        $this->sheetRepository->set($create->sheet);
    }
}
