<?php

namespace Proximum\Vimeet\Application\Command\Unavailability\MassAssignment;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;

class Update implements Command
{
    /**
     * @var MassAssignment
     */
    public $massAssignment;

    /**
     * @var \DateTimeInterface
     */
    public $begin;

    /**
     * @var \DateTimeInterface
     */
    public $end;

    /**
     * @var bool
     */
    public $enabled;

    /**
     * Update constructor.
     *
     * @param MassAssignment $massAssignment
     */
    public function __construct(MassAssignment $massAssignment)
    {
        $this->massAssignment = $massAssignment;
    }
}
