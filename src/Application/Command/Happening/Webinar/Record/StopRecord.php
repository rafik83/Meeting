<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Record;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Happening;

class StopRecord implements Command
{
    /** @var Happening */
    public $happening;

    public function __construct(Happening $happening)
    {
        $this->happening = $happening;
    }
}
