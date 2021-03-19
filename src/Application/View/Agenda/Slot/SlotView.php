<?php

namespace Proximum\Vimeet\Application\View\Agenda\Slot;

class SlotView
{
    public int $id;

    public string $label;

    public function __construct(int $id, string $label)
    {
        $this->id       = $id;
        $this->label = $label;
    }
}
