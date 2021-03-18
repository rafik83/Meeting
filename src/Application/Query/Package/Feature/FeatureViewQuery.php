<?php

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
