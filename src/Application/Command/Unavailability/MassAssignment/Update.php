<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability\MassAssignment;

use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;

class Update
{
    /**
     * @var MassAssignment
     */
    public $massAssignment;

    /**
     * @var \DateTimeInterface
     */
    public $begin;

    /**
     * @var \DateTimeInterface
     */
    public $end;

    /**
     * @var boolean
     */
    public $enabled;

    /**
     * Update constructor.
     *
     * @param MassAssignment     $massAssignment
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param bool               $enabled
     */
    public function __construct(
        MassAssignment $massAssignment,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $enabled
    ) {
        $this->massAssignment = $massAssignment;
        $this->begin          = $begin;
        $this->end            = $end;
        $this->enabled        = $enabled;
    }
}
