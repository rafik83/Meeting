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
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\User;

class OrderConfirmMail extends Mail
{
    /**
     * @var string
     */
    protected $subject = 'mail.orderConfirm.subject';

    /**
     * @var string
     */
    protected $template = 'MailBundle:Mail:Order/orderConfirm.html.twig';

    /**
     * @var string
     */
    protected $messageId = Events::ORDER_CONFIRMED;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var User
     */
    private $user;

    /**
     * @param string $sender
     * @param string $receiver
     * @param string $locale
     * @param Order  $order
     * @param User   $user
     */
    public function __construct(
        $sender,
        $receiver,
        $locale,
        Order $order,
        User $user
    ) {
        parent::__construct($sender, $receiver, $locale, null, null, $order->getSheet()->getEvent());

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
}
