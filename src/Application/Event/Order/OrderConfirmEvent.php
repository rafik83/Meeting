<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
     * @var Event
     */
    private $event;

    /**
     * OrderConfirmEvent constructor.
     *
     * @param Order $order
     * @param User  $user
     * @param Event $event
     */
    public function __construct(Order $order, User $user, Event $event)
    {
        $this->order = $order;
        $this->user  = $user;
        $this->event = $event;
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
        return $this->event;
    }
}
