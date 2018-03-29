<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Product\Participant;

use Proximum\Vimeet\Application\Command\Product\AbstractCreate;
use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;

class CreateParticipant extends AbstractCreate
{
    /** @var AvailabilityTimeRange[] */
    public $availabilityTimeRanges = [];
}
