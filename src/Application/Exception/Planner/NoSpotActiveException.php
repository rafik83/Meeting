<?php

namespace Proximum\Vimeet\Application\Exception\Planner;

class NoSpotActiveException extends PlannerException
{
    /**
     * {@inheritdoc}
     */
    public function __construct($message = 'admin.planner.export.noSpotActive', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
