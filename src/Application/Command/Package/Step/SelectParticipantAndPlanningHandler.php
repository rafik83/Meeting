<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package\Step;

use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Order\Merger;

class SelectParticipantAndPlanningHandler
{
    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * @var Merger
     */
    private $merger;

    /**
     * @param CartManager $cartManager
     * @param Merger      $merger
     */
    public function __construct(CartManager $cartManager, Merger $merger)
    {
        $this->cartManager = $cartManager;
        $this->merger      = $merger;
    }

    /**
     * @param SelectParticipantAndPlanning $selectParticipantAndPlanning
     */
    public function handle(SelectParticipantAndPlanning $selectParticipantAndPlanning)
    {
        $sheet   = $selectParticipantAndPlanning->sheet;
        $package = $sheet->getPackage();

        $cart = $this->cartManager->getCart($sheet, $selectParticipantAndPlanning->currentStep);
        $cart->resolveParticipantsQuantity();

        if (count($sheet->getOrders()) > 0) {
            $merged = $this->merger->merge($sheet->getOrders());
            if ($orderRow = $merged->getRowForProduct($package->getPlanning())) {
                $orderQuantity = $orderRow->getQuantity();
            }
        }

        $quantity = (isset($orderQuantity)) ?
            $selectParticipantAndPlanning->planningQuantity - $orderQuantity :
            $selectParticipantAndPlanning->planningQuantity;

        if ($package && $package->getPlanning()) {
            $cart->setProduct($package->getPlanning(), $quantity);
        }

        $this->cartManager->save($cart);
    }
}
