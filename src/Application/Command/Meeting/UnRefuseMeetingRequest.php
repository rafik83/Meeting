<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class UnRefuseMeetingRequest
{
    /**
     * @var Meeting\Request
     */
    public $meetingRequest;

    /**
     * @var User
     */
    public $editor;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @param User            $editor
     * @param Meeting\Request $meetingRequest
     * @param Sheet           $sheet
     */
    public function __construct(User $editor, Meeting\Request $meetingRequest, Sheet $sheet)
    {
        $this->editor         = $editor;
        $this->meetingRequest = $meetingRequest;
        $this->sheet          = $sheet;
    }
}
