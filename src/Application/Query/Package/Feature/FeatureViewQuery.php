<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package\Feature;

use Proximum\Vimeet\Domain\Model\Product\Feature;

class FeatureViewQuery
{
    /**
     * @var Feature
     */
    public $feature;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param Feature $feature
     * @param string  $locale
     */
    public function __construct(Feature $feature, $locale)
    {
        $this->feature = $feature;
        $this->locale  = $locale;
    }
}
