<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\MassAssignement;

use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;

class MassAssignmentViewQuery
{
    /**
     * @var MassAssignment
     */
    public $massAssignment;

    /**
     * @var string
     */
    public $locale;

    /**
     * MassAssignmentViewQuery constructor.
     *
     * @param MassAssignment $massAssignment
     * @param string         $locale
     */
    public function __construct(MassAssignment $massAssignment, $locale)
    {
        $this->massAssignment = $massAssignment;
        $this->locale         = $locale;
    }
}
