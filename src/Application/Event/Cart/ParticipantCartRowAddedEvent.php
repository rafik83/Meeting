<?php

namespace Proximum\Vimeet\Application\Event\Cart;

use Proximum\Vimeet\Domain\Model\Participant;
use Symfony\Component\EventDispatcher\Event;

class ParticipantCartRowAddedEvent extends Event
{
    /** @var Participant */
    public $participant;

    /** @var bool */
    public $hadPreviousProduct;

    /** @var bool */
    public $hadAlreadyThisProductInCart;

    public function __construct(
        Participant $participant,
        bool $hadPreviousProduct,
        bool $hadAlreadyThisProductInCart
    ) {
        $this->participant = $participant;
        $this->hadPreviousProduct = $hadPreviousProduct;
        $this->hadAlreadyThisProductInCart = $hadAlreadyThisProductInCart;
    }
}
