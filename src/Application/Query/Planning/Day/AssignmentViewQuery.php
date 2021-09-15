<?php

namespace Proximum\Vimeet\Application\Query\Planning\Day;

use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;

class AssignmentViewQuery
{
    /** @var MassAssignment */
    public $assignment;

    /** @var string */
    public $locale;

    /**
     * @param MassAssignment $assignment
     * @param string         $locale
     */
    public function __construct(MassAssignment $assignment, $locale)
    {
        $this->assignment = $assignment;
        $this->locale     = $locale;
    }
}
