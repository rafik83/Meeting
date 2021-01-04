<?php

namespace Proximum\Vimeet\Application\Exception\Planner;

class SlotNotConfiguredException extends PlannerException
{
    /**
     * {@inheritdoc}
     */
    public function __construct($message = 'admin.planner.export.slotNotConfigured', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
