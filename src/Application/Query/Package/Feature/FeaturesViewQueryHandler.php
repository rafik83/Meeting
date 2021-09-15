<?php

namespace Proximum\Vimeet\Application\Query\Package\Feature;

use Proximum\Vimeet\Application\View\Package\FeaturesView;

class FeaturesViewQueryHandler
{
    /**
     * @var FeatureViewQueryHandler
     */
    private $featureViewQueryHandler;

    /**
     * @param FeatureViewQueryHandler $featureViewQueryHandler
     */
    public function __construct(FeatureViewQueryHandler $featureViewQueryHandler)
    {
        $this->featureViewQueryHandler = $featureViewQueryHandler;
    }

    /**
     * @param FeaturesViewQuery $featuresViewQuery
     *
     * @return FeaturesView
     */
    public function handle(FeaturesViewQuery $featuresViewQuery)
    {
        $featuresView = new FeaturesView();

        foreach ($featuresViewQuery->product->getFeatures() as $feature) {
            $featuresView->features[] = $this->featureViewQueryHandler->handle(new FeatureViewQuery(
                $feature,
                $featuresViewQuery->locale
            ));
        }

        return $featuresView;
    }
}
