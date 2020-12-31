<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class UnApproveMeetingRequest
{
    /**
     * @var Meeting\Request
     */
    public $meetingRequest;

    /**
     * @var User
     */
    public $user;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @param User            $user
     * @param Meeting\Request $meetingRequest
     * @param Sheet           $sheet
     */
    public function __construct(User $user, Meeting\Request $meetingRequest, Sheet $sheet)
    {
        $this->sheet          = $sheet;
        $this->meetingRequest = $meetingRequest;
        $this->user           = $user;
    }
}
