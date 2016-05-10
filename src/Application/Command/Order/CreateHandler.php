<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Application\Components\Participant\ParticipantManager;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Repository\CartRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Application\Components\Sheet\StateSetter;

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
     * @var ParticipantManager
     */
    private $participantManager;

    /**
     * @var StateSetter
     */
    private $stateSetter;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SheetRepositoryInterface $sheetRepository
     * @param CartRepositoryInterface  $cartRepository
     * @param ParticipantManager       $participantManager
     * @param StateSetter              $stateSetter
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SheetRepositoryInterface $sheetRepository,
        CartRepositoryInterface $cartRepository,
        ParticipantManager $participantManager,
        StateSetter $stateSetter
    ) {
        $this->orderRepository    = $orderRepository;
        $this->sheetRepository    = $sheetRepository;
        $this->cartRepository     = $cartRepository;
        $this->participantManager = $participantManager;
        $this->stateSetter        = $stateSetter;
    }

    /**
     * @param Create $create
     */
    public function handle(Create $create)
    {
        $order = new Order(
            $create->sheet,
            $create->state,
            $create->packageData,
            $create->packageTemplate,
            $create->billingData,
            $create->billingTemplate,
            $create->createdAt,
            $create->paymentMode
        );

        $this->orderRepository->add($order);

        $this->cartRepository->delete($create->cart);

        // @todo: fix this
        $sheetData = [];
        $create->sheet->setPackageData($sheetData);

        $this->stateSetter->setState($create->sheet);

        $this->sheetRepository->set($create->sheet);
    }
}
