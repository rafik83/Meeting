<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Meeting;

class RemoveMeeting
{
    /**
     * @var Meeting
     */
    public $meeting;

    /**
     * @var Admin
     */
    public $user;

    /**
     * RemoveMeeting constructor.
     *
     * @param Meeting $meeting
     * @param Admin   $user
     */
    public function __construct(Meeting $meeting, Admin $user)
    {
        $this->meeting = $meeting;
        $this->user    = $user;
    }
}
