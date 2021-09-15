<?php

namespace Proximum\Vimeet\Application\Command\Happening\Speaker;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Happening\Speaker;

class Delete implements Command
{
    /**
     * @var Speaker
     */
    public $speaker;

    /**
     * Delete constructor.
     *
     * @param Speaker $speaker
     */
    public function __construct(Speaker $speaker)
    {
        $this->speaker = $speaker;
    }
}
