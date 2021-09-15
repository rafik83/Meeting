<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Meeting;

class DeleteMeeting implements Command
{
    /**
     * @var Meeting
     */
    public $meeting;

    /**
     * @param Meeting $meeting
     */
    public function __construct(Meeting $meeting)
    {
        $this->meeting = $meeting;
    }
}
