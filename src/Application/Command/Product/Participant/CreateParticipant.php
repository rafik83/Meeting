<?php

namespace Proximum\Vimeet\Application\Command\Product\Participant;

use Proximum\Vimeet\Application\Command\Product\AbstractCreate;
use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;

class CreateParticipant extends AbstractCreate
{
    /** @var AvailabilityTimeRange[] */
    public $availabilityTimeRanges = [];
}
