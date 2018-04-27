<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Order;

use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Order;

class OrderConfirmMail extends UserMail
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
     * @var bool
     */
    protected $sendToEmailTeam = true;

    /**
     * @param string              $sender
     * @param string              $receiver
     * @param string              $locale
     * @param Order               $order
     * @param ParticipantInfoView $participantInfoView
     */
    public function __construct(
        $sender,
        $receiver,
        $locale,
        Order $order,
        ParticipantInfoView $participantInfoView
    ) {
        parent::__construct(
            $sender,
            $receiver,
            $locale,
            $order->getSheet()->getEvent(),
            $participantInfoView
        );

        $this->order = $order;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }
}
