<?php

namespace Proximum\Vimeet\Application\View\Agenda\Admin;

class SpotView
{
    public int $id;

    public string $label;

    public int $seatCapacity;

    public array $slotsId;

    public function __construct(int $id, string $label, int $seatCapacity, array $possibleSlotsId)
    {
        $this->id = $id;
        $this->label = $label;
        $this->seatCapacity = $seatCapacity;
        $this->slotsId = $possibleSlotsId;
    }
}
