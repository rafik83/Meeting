<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\ChangeType;

use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Participant\ParticipantProductSetter;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;

class CancelPackageHandler
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var CartManager */
    private $cartManager;

    /** @var ParticipantProductSetter */
    private $participantProductSetter;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CartManager $cartManager,
        ParticipantProductSetter $participantProductSetter
    ) {
        $this->orderRepository = $orderRepository;
        $this->cartManager = $cartManager;
        $this->participantProductSetter = $participantProductSetter;
    }

    public function handle(CancelPackage $cancelPackage): void
    {
        $orders = $this->orderRepository->findBySheet($cancelPackage->sheet);

        if (\count($orders)) {
            array_map(
                function (Order $order) {
                    $order->cancel();
                    $this->orderRepository->set($order);
                },
                $orders
            );
        }

        $this->cartManager->emptyCart($cancelPackage->sheet);

        foreach ($cancelPackage->sheet->getParticipantsArray() as $participant) {
            $this->participantProductSetter->setProductOnParticipant($participant, null);
        }
    }
}
