<?php

namespace Proximum\Vimeet\Application\Command\Rooming\Stay;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Rooming\Stay;

class AssignRoomNumber implements Command
{
    /** @var Stay */
    public $stay;

    /** @var string|null */
    public $roomNumber;

    public function __construct(Stay $stay, ?string $roomNumber)
    {
        $this->stay = $stay;
        $this->roomNumber = $roomNumber;
    }
}
