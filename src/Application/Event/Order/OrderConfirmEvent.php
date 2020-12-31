<?php

namespace Proximum\Vimeet\Application\Event\Order;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Component\EventDispatcher;

class OrderConfirmEvent extends EventDispatcher\Event
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @var User
     */
    private $user;

    /**
     * @param Order $order
     * @param User  $user
     */
    public function __construct(Order $order, User $user)
    {
        $this->order = $order;
        $this->user  = $user;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->order->getSheet()->getEvent();
    }
}
