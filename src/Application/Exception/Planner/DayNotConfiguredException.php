<?php

namespace Proximum\Vimeet\Application\Exception\Planner;

class DayNotConfiguredException extends PlannerException
{
    /**
     * {@inheritdoc}
     */
    public function __construct($message = 'admin.planner.export.daysNotConfigured', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
