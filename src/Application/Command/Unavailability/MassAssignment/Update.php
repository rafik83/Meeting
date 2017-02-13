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
     * @var string
     */
    public $begin;

    /**
     * @var string
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
     * @param string             $begin
     * @param string             $end
     * @param bool               $enabled
     */
    public function __construct(
        MassAssignment $massAssignment,
        $begin,
        $end,
        $enabled
    ) {
        $this->massAssignment = $massAssignment;
        $this->begin          = $begin;
        $this->end            = $end;
        $this->enabled        = $enabled;
    }
}
