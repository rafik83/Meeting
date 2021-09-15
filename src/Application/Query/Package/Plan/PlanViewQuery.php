<?php

namespace Proximum\Vimeet\Application\Query\Package\Plan;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;

class PlanViewQuery
{
    /**
     * @var Product
     */
    public $product;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var Event
     */
    public $event;

    /**
     * @param Event   $event
     * @param Product $product
     * @param string  $locale
     */
    public function __construct(Event $event, Product $product, $locale)
    {
        $this->event   = $event;
        $this->product = $product;
        $this->locale  = $locale;
    }
}
