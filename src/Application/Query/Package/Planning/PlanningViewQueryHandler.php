<?php

namespace Proximum\Vimeet\Application\Query\Package\Planning;

use Proximum\Vimeet\Application\View\Package\ProductView;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Order\Merger;

class PlanningViewQueryHandler
{
    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * @var Merger
     */
    private $orderMerger;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @param CartManager        $cartManager
     * @param Merger             $orderMerger
     * @param \DateTimeInterface $dateTime
     */
    public function __construct(CartManager $cartManager, Merger $orderMerger, \DateTimeInterface $dateTime)
    {
        $this->cartManager = $cartManager;
        $this->orderMerger = $orderMerger;
        $this->dateTime    = $dateTime;
    }

    /**
     * @param PlanningViewQuery $planningViewQuery
     *
     * @return ProductView
     */
    public function handle(PlanningViewQuery $planningViewQuery)
    {
        $cart            = $this->cartManager->getCart($planningViewQuery->sheet);
        $locale          = $planningViewQuery->locale;
        $planningProduct = $planningViewQuery->sheet->getPackage()->getPlanning();
        $included        = 0;

        if ($planningViewQuery->sheet->hasNotCancelledOrders()) {
            $order        = $this->orderMerger->merge($planningViewQuery->sheet->getNotCancelledOrders());
            $selectedPlan = $order->getPlan();
        } else {
            $selectedPlan = null !== $cart->getPlanRow() ? $cart->getPlanRow()->getProduct() : null;
        }

        if ($selectedPlan) {
            $planningProductIncluded = $selectedPlan->getIncludedPlanningProduct();

            if ($planningProductIncluded) {
                $included = $planningProductIncluded->getQuantity();
            }
        }

        return new ProductView(
            $planningProduct->getId(),
            $planningProduct->getTitle($locale),
            $planningProduct->getUnitPrice(),
            $planningProduct->getHeading($locale),
            $planningProduct->getDescription($locale),
            $planningProduct->getAddon($locale),
            $planningProduct->getImage(),
            $planningProduct->getAvailabilityCurrent(),
            $planningProduct->getAvailabilityMax(),
            $planningProduct->isOutOfStock(),
            $planningProduct->getVatMode(),
            $planningProduct->getEvent()->getCurrency(),
            $planningProduct->getSubjectedToValidationHelp($planningViewQuery->locale),
            $planningProduct->isSubjectedToValidation(),
            $included,
            $planningProduct->isBuyable($this->dateTime)
        );
    }
}
