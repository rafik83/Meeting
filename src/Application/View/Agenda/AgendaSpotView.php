<?php

namespace Proximum\Vimeet\Application\View\Agenda;

class AgendaSpotView
{
    /**
     * @var AgendaDayView[]
     */
    public $days;

    /**
     * @var int "Spot ID"
     */
    public $id;

    /**
     * @var string
     */
    public $reference;

    /**
     * AgendaSpotView constructor.
     *
     * @param int       $id
     * @param string    $reference
     * @param AgendaDayView[] $days
     */
    public function __construct($id, $reference, array $days = [])
    {
        $this->days      = $days;
        $this->reference = $reference;
        $this->id        = $id;
    }
}
