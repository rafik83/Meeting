<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package\Feature;

use Proximum\Vimeet\Application\View\Package\FeatureView;

class FeatureViewQueryHandler
{
    /**
     * @param FeatureViewQuery $featureViewQuery
     *
     * @return FeatureView
     */
    public function handle(FeatureViewQuery $featureViewQuery)
    {
        return new FeatureView(
            $featureViewQuery->feature->getTranslations()->get($featureViewQuery->locale)->getTitle(),
            $featureViewQuery->feature->getTranslations()->get($featureViewQuery->locale)->getDescription()
        );
    }
}
