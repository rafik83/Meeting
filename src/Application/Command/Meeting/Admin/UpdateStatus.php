<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Meeting;

class UpdateStatus implements Command
{
    /** @var Meeting */
    public $meeting;

    /** @var string */
    public $value;

    public function __construct(Meeting $meeting, string $value)
    {
        $this->meeting = $meeting;
        $this->value = $value;
    }
}
