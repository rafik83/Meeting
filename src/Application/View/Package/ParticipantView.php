<?php

namespace Proximum\Vimeet\Application\View\Package;

use Proximum\Vimeet\Application\View\Participant;

class ParticipantView
{
    /** @var int */
    public $id;

    /** @var Participant\CardView */
    public $card;

    /**
     * @param int                  $id
     * @param Participant\CardView $card
     */
    public function __construct($id, Participant\CardView $card)
    {
        $this->id = $id;
        $this->card = $card;
    }
}
