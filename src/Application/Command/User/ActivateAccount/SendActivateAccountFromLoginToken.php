<?php

namespace Proximum\Vimeet\Application\Command\User\ActivateAccount;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class SendActivateAccountFromLoginToken implements Command
{
    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    public function __construct(Sheet $sheet, User $user)
    {
        $this->sheet = $sheet;
        $this->user = $user;
    }
}
