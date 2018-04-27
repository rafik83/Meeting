<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda\Admin;

class RequestSlotView
{
    /** @var int[] */
    public $availableSlotsId;

    /**
     * @param int[] $availableSlotsId
     */
    public function __construct(array $availableSlotsId)
    {
        $this->availableSlotsId = $availableSlotsId;
    }
}
