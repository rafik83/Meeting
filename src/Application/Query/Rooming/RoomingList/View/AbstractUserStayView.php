<?php

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList\View;

use Proximum\Vimeet\Domain\Time\AbstractTimeRange;

abstract class AbstractUserStayView extends AbstractTimeRange
{
    abstract public function isAssigned(): bool;
}
