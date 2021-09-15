<?php

namespace Proximum\Vimeet\Application\Query\Package\Plan;

use Proximum\Vimeet\Application\Query\Package\Feature\FeaturesViewQuery;
use Proximum\Vimeet\Application\Query\Package\Feature\FeaturesViewQueryHandler;
use Proximum\Vimeet\Application\View\Package\PlanView;

class PlanViewQueryHandler
{
    /**
     * @var FeaturesViewQueryHandler
     */
    private $featuresViewQueryHandler;

    /**
     * @param FeaturesViewQueryHandler $featuresViewQueryHandler
     */
    public function __construct(FeaturesViewQueryHandler $featuresViewQueryHandler)
    {
        $this->featuresViewQueryHandler = $featuresViewQueryHandler;
    }

    /**
     * @param PlanViewQuery $planViewQuery
     *
     * @return PlanView
     */
    public function handle(PlanViewQuery $planViewQuery)
    {
        return new PlanView(
            $planViewQuery->product->getId(),
            $planViewQuery->product->getTitle($planViewQuery->locale),
            $planViewQuery->product->getUnitPrice(),
            $planViewQuery->product->isOutOfStock(),
            $planViewQuery->product->getHeading($planViewQuery->locale),
            $planViewQuery->product->getDescription($planViewQuery->locale),
            $planViewQuery->product->getImage(),
            $planViewQuery->event->getMode(),
            $planViewQuery->event->getCurrency(),
            $this->featuresViewQueryHandler->handle(
                new FeaturesViewQuery(
                    $planViewQuery->product,
                    $planViewQuery->locale
                )
            )->features,
            $planViewQuery->product->getAddon($planViewQuery->locale)
        );
    }
}
