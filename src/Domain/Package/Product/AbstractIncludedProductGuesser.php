<?php

namespace Proximum\Vimeet\Domain\Package\Product;

use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;

abstract class AbstractIncludedProductGuesser
{
    /** @var CartManager */
    protected $cartManager;

    /** @var Merger */
    protected $orderMerger;

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
     * @return null|Product
     */
    protected function getSelectedPlan(Sheet $sheet): ?Product
    {
        $orderMerged = $this->orderMerger->getMergedOrders($sheet);

        if (null !== $orderMerged && null !== $orderMerged->getPlan()) {
            return $orderMerged->getPlan();
        }

        $cart = $this->cartManager->getCart($sheet);

        if (null === $cart->getPlanRow()) {
            return null;
        }

        return $cart->getPlanRow()->getProduct();
    }
}
