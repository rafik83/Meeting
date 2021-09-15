<?php

namespace Proximum\Vimeet\Application\Query\Package\Summary;

use Proximum\Vimeet\Application\View\Package\Summary\PlanGroupView;

class PlanGroupViewQueryHandler
{
    /**
     * @var ProductViewQueryHandler
     */
    private $productViewQueryHandler;

    /**
     * @param ProductViewQueryHandler $productViewQueryHandler
     */
    public function __construct(ProductViewQueryHandler $productViewQueryHandler)
    {
        $this->productViewQueryHandler = $productViewQueryHandler;
    }

    /**
     * @param PlanGroupViewQuery $planGroupViewQuery
     *
     * @throws \Exception
     *
     * @return null|PlanGroupView
     */
    public function handle(PlanGroupViewQuery $planGroupViewQuery)
    {
        $cart    = $planGroupViewQuery->cart;
        $package = $planGroupViewQuery->sheet->getPackage();

        $plan = $cart->getPlanRow();

        if ($planGroupViewQuery->sheet->hasNotCancelledOrders()) {
            return null;
        }

        if ($package->isPlansEnabled() && null === $plan) {
            throw new \Exception('Plan is enabled and no plan is selected');
        }

        $planView = $this->productViewQueryHandler->handle(new ProductViewQuery(
            $planGroupViewQuery->sheet,
            $plan->getProduct(),
            $cart,
            $planGroupViewQuery->locale
        ));

        return new PlanGroupView(
            $package->getPlansLabel($planGroupViewQuery->locale),
            [$planView],
            null !== $planView ? $planView->total : 0
        );
    }
}
