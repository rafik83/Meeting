<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Order;

use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Order;

class OrderConfirmMail extends UserMail
{
    public const SUBJECT = 'mail.orderConfirm.subject';
    public const TEMPLATE = 'MailBundle:Mail:Order/orderConfirm.html.twig';
    public const TEMPLATE_FULL_TEXT = 'MailBundle:Mail:Order/orderConfirm_full_text.html.twig';

    /** @var string */
    protected $subject = self::SUBJECT;

    /** @var string */
    protected $template = self::TEMPLATE;

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
