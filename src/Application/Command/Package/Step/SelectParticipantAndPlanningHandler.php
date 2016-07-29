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
     *
     * @throws \Exception
     */
    public function handle(SelectParticipantAndPlanning $selectParticipantAndPlanning)
    {
        $sheet   = $selectParticipantAndPlanning->sheet;
        $package = $sheet->getPackage();

        if (!$package) {
            throw new \Exception('Package not found');
        }

        $cart = $this->cartManager->getCart($sheet, $selectParticipantAndPlanning->currentStep);

        // Update participant cart row
        $cart->resolveParticipantsQuantity();

        if (!$package->getPlanning()) {
            return;
        }

        // Update planning cart row
        $orderPlanningQuantity = 0;

        if ($sheet->hasOrders()) {
            $merged = $this->merger->merge($sheet->getOrders());
            if ($orderRow = $merged->getRowForProduct($package->getPlanning())) {
                $orderPlanningQuantity = $orderRow->getQuantity();
            }
        }

        $quantity = (int) $selectParticipantAndPlanning->planningQuantity - $orderPlanningQuantity;

        $cart->setProduct($package->getPlanning(), $quantity);
        $this->cartManager->save($cart);
    }
}
