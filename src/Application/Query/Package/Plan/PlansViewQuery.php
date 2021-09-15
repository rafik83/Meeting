<?php

namespace Proximum\Vimeet\Application\Query\Package\Plan;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;

class PlansViewQuery
{
    /**
     * @var Package
     */
    public $package;

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
     * @param Package $package
     * @param string  $locale
     */
    public function __construct(Event $event, Package $package, $locale)
    {
        $this->event   = $event;
        $this->package = $package;
        $this->locale  = $locale;
    }
}
