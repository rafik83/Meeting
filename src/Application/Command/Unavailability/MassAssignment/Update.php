<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
     * @var bool
     */
    public $enabled;

    /**
     * Update constructor.
     *
     * @param MassAssignment $massAssignment
     */
    public function __construct(MassAssignment $massAssignment)
    {
        $this->massAssignment = $massAssignment;
    }
}
