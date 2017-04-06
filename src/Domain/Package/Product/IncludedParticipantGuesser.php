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
        $product             = null;
        $totalQuantity       = 0;
        $participantIncluded = $this->getParticipantIncluded($sheet);

        if (null !== $participantIncluded) {
            $totalQuantity = $participantIncluded->getQuantity();
            $product       = $participantIncluded->getIncluded();
        }

        $remainingQuantity = max(0, $totalQuantity - $sheet->countParticipant());

        return new IncludedParticipantView($product, $totalQuantity, $remainingQuantity);
    }

    /**
     * @param Sheet $sheet
     *
     * @return ProductIncluded
     */
    private function getParticipantIncluded(Sheet $sheet)
    {
        $selectedPlan = $this->getSelectedPlan($sheet);

        if (null !== $selectedPlan) {
            $participantProductIncluded = $selectedPlan->getIncludedParticipantProduct();

            if ($participantProductIncluded instanceof ProductIncluded) {
                return $participantProductIncluded;
            }
        }

        return null;
    }

    /**
     * @param Sheet $sheet
     *
     * @return null|Product
     */
    private function getSelectedPlan(Sheet $sheet)
    {
        if ($sheet->hasNotCancelledOrders()) {
            $orderMerged = $this->orderMerger->merge($sheet->getNotCancelledOrders());

            return $orderMerged->getPlan();
        }

        $cart = $this->cartManager->getCart($sheet);

        if (null !== $cart->getPlanRow()) {
            return $cart->getPlanRow()->getProduct();
        }

        return null;
    }
}
