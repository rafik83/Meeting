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

    public function __construct(
        Sheet $sheet,
        User $user
    ) {
        $this->sheet = $sheet;
        $this->user = $user;
    }
}
