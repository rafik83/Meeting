<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Application\Command\Command;

class UpdateNetworkingChatViewedAt implements Command
{
    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    /** @var \DateTime */
    public $updateTime;

    public function __construct(
        Sheet $sheet,
        User $user,
        \DateTime $updateTime
    ) {
        $this->sheet = $sheet;
        $this->user = $user;
        $this->updateTime = $updateTime;
    }
}
