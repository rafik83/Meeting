<?php

namespace Proximum\Vimeet\Application\View\Agenda\Slot;

class SlotView
{
    public int $id;

    public string $label;

    /**
     * ParticipantView constructor.
     *
     * @param int    $id
     * @param string $label
     */
    public function __construct($id, $label)
    {
        $this->id       = $id;
        $this->label = $label;
    }
}
