<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\View;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;

class PrepareOrderConfirmedMailView extends AbstractPrepareMail
{
    /** @var Order */
    public $order;

    public function __construct(
        Event $event,
        User $user,
        string $locale,
        Sheet $sheet,
        Order $order
    ) {
        parent::__construct(
            $event,
            $user,
            Constant::TRANSACTIONAL_MAIL_KEY_ORDER_CONFIRMED,
            $locale,
            $sheet
        );

        $this->order = $order;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }
}
