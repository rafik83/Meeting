<?php

namespace Proximum\Vimeet\Application\Query\Order\Export;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;

class OrderViewQuery
{
    /** @var Order */
    public $order;

    /** @var string */
    public $locale;

    /** @var string */
    public $adminLocale;

    /** @var Event */
    public $event;

    /**
     * @param Event  $event
     * @param Order  $order
     * @param string $locale
     * @param string $adminLocale
     */
    public function __construct(Event $event, Order $order, $locale, $adminLocale)
    {
        $this->event = $event;
        $this->order = $order;
        $this->locale = $locale;
        $this->adminLocale = $adminLocale;
    }
}
