<?php

namespace Proximum\Vimeet\Application\Exception\Planner;

class CallPlannerException extends PlannerException
{
    /**
     * {@inheritdoc}
     */
    public function __construct($message = 'admin.planner.export.callPlannerError', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
