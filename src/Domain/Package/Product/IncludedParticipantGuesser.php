<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Package\Product;

use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Product\ProductIncluded;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\View\Package\Product\IncludedParticipantView;

class IncludedParticipantGuesser
{
    /** @var CartManager */
    private $cartManager;

    /** @var Merger */
    private $orderMerger;

    /**
     * @param CartManager $cartManager
     * @param Merger      $orderMerger
     */
    public function __construct(CartManager $cartManager, Merger $orderMerger)
    {
        $this->cartManager = $cartManager;
        $this->orderMerger = $orderMerger;
    }

    /**
     * @param Sheet $sheet
     *
     * @return IncludedParticipantView
     */
    public function getIncludedParticipantView(Sheet $sheet)
    {
        $totalQuantity     = $this->getParticipantIncludedQuantity($sheet);
        $remainingQuantity = min(0, $totalQuantity - $sheet->countParticipant());

        return new IncludedParticipantView($totalQuantity, $remainingQuantity);
    }

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    private function getParticipantIncludedQuantity(Sheet $sheet)
    {
        $selectedPlan = $this->getSelectedPlan($sheet);

        if (null !== $selectedPlan) {
            $participantProductIncluded = $selectedPlan->getIncludedParticipantProduct();

            if ($participantProductIncluded instanceof ProductIncluded) {
                return $participantProductIncluded->getQuantity();
            }
        }

        return 0;
    }

    /**
     * @param Sheet $sheet
     *
     * @return null|Product
     */
    private function getSelectedPlan(Sheet $sheet)
    {
        if ($sheet->hasNotCancelledOrders()) {
            $orderMerged  = $this->orderMerger->merge($sheet->getNotCancelledOrders());
            return $orderMerged->getPlan();
        }

        $cart = $this->cartManager->getCart($sheet);

        if (null !== $cart->getPlanRow()) {
            return $cart->getPlanRow()->getProduct();
        }

        return null;
    }
}
