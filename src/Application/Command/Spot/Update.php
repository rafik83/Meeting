<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Spot;

use Proximum\Vimeet\Domain\Model\Spot;

class Update
{
    /**
     * @var Spot
     */
    public $spot;

    /**
     * @var string
     */
    public $reference;

    /**
     * @var int
     */
    public $size;

    /**
     * @var int
     */
    public $meetingCapacity;

    /**
     * @var int
     */
    public $seatCapacity;

    /**
     * Update constructor.
     *
     * @param Spot $spot
     */
    public function __construct(Spot $spot)
    {
        $this->spot            = $spot;
        $this->reference       = $spot->getReference();
        $this->size            = $spot->getSize();
        $this->meetingCapacity = $spot->getMeetingCapacity();
        $this->seatCapacity    = $spot->getSeatCapacity();
    }

    /**
     * @return bool
     */
    public function referenceHasChanged()
    {
        return $this->reference !== $this->spot->getReference();
    }
}
