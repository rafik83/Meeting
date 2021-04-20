<?php

namespace Proximum\Vimeet\Application\Query\MassAssignment;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;

class MassAssignmentViewQuery implements Query
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
     * @var Event
     */
    public $event;

    /**
     * MassAssignmentViewQuery constructor.
     *
     * @param MassAssignment $massAssignment
     * @param Event          $event
     * @param string         $locale
     */
    public function __construct(MassAssignment $massAssignment, Event $event, $locale)
    {
        $this->massAssignment = $massAssignment;
        $this->locale         = $locale;
        $this->event          = $event;
    }
}
