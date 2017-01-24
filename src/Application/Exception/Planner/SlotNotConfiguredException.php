<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Exception\Planner;

class SlotNotConfiguredException extends PlannerException
{
    /**
     * {@inheritdoc}
     */
    public function __construct($message = "admin.planner.export.slotNotConfigured", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
