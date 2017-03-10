<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Planning;

use Proximum\Vimeet\Domain\Model\Type;

class ExportPlanningJobCreator
{
    /**
     * @var Type[]
     */
    public $types;

    /**
     * @var string
     */
    public $orderBy;

    /**
     * Create the job for the planning export
     */
    public function __construct()
    {
        $this->types = [];
    }
}
