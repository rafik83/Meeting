<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Happening;

class StopBroadcast implements Command
{
    /** @var Happening */
    public $happening;

    /** @var string */
    public $type;

    public function __construct(Happening $happening, string $type)
    {
        $this->happening = $happening;
        $this->type = $type;
    }
}
