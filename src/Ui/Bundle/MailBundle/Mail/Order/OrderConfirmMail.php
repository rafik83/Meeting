<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Order;

use Proximum\Vimeet\Application\Components\Mail\Mail;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\User;

class OrderConfirmMail extends Mail
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
     * OrderConfirmMail constructor.
     *
     * @param string $sender
     * @param string $receiver
     * @param string $template
     * @param string $messageId
     * @param string $locale
     * @param Order  $order
     * @param User   $user
     * @param Event  $event
     */
    public function __construct(
        $sender,
        $receiver,
        $template,
        $messageId,
        $locale,
        Order $order,
        User $user,
        Event $event
    ) {
        parent::__construct($sender, $receiver, $template, $messageId, $locale);

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
