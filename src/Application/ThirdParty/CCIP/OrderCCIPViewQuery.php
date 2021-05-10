<?php


namespace Proximum\Vimeet\Application\ThirdParty\CCIP;


use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\User;

class OrderCCIPViewQuery implements Query
{
    /** @var string */
    public $locale;

    /** @var Event */
    public $event;

    /** @var Order */
    public $order;

    public User $user;

    public function __construct(Event $event, string $locale, Order $order, User $user)
    {
        $this->event = $event;
        $this->locale = $locale;
        $this->order = $order;
        $this->user = $user;
    }
}
