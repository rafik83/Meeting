<?php

namespace Proximum\Vimeet\Domain\Model;

class CartRowParticipant
{
    /** @var CartRow */
    private $cartRow;

    /** @var Participant */
    private $participant;

    public function __construct(CartRow $cartRow, Participant $participant)
    {
        $this->cartRow = $cartRow;
        $this->participant = $participant;
    }

    public function getCartRow(): CartRow
    {
        return $this->cartRow;
    }

    public function getParticipant(): Participant
    {
        return $this->participant;
    }
}
