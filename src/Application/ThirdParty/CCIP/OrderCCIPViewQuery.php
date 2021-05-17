<?php


namespace Proximum\Vimeet\Application\ThirdParty\CCIP;


use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\User;

class OrderCCIPViewQuery implements Query
{
    public string $locale;
    public Event $event;
    public Order $order;
    public User $user;
    public string $captureToken;

    public function __construct(Event $event, string $locale, Order $order, User $user, string $captureToken)
    {
        $this->event = $event;
        $this->locale = $locale;
        $this->order = $order;
        $this->user = $user;
        $this->captureToken = $captureToken;
    }
}
