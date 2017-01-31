<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Unavailability;

use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;

interface MassAssignmentRepositoryInterface
{
    /**
     * @param MassAssignment $massAssignment
     */
    public function add(MassAssignment $massAssignment);
}
