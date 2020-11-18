<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Happening;

class OpenStreamToPublicCommand implements Command
{
    /** @var Happening */
    public $happening;

    public function __construct(Happening $happening)
    {
        $this->happening = $happening;
    }
}
