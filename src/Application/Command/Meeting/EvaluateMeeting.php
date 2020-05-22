<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class EvaluateMeeting implements Command
{
    /** @var User */
    public $user;

    /** @var Meeting */
    public $meeting;

    /** @var int */
    public $evaluation;

    /** @var Sheet */
    public $sheet;

    public function __construct(User $user, Sheet $sheet, Meeting $meeting)
    {
        $this->user = $user;
        $this->meeting = $meeting;
        $this->sheet = $sheet;
        $this->evaluation = 3;
    }
}
