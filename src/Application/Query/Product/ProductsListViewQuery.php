<?php

namespace Proximum\Vimeet\Application\Query\Product;

use Proximum\Vimeet\Domain\Model\Event;

class ProductsListViewQuery
{
    /** @var Event */
    public $event;

    /** @var string */
    public $adminLocale;

    public function __construct(Event $event, $adminLocale)
    {
        $this->event = $event;
        $this->adminLocale = $adminLocale;
    }
}
