<?php

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\ChangeMailToken;
use Proximum\Vimeet\Domain\Model\User;

class ChangeMailActivation implements Command
{
    /** @var User */
    public $user;

    /** @var string */
    public $mail;

    public function __construct(ChangeMailToken $changeMailToken)
    {
        $this->user = $changeMailToken->getUser();
        $this->mail = $changeMailToken->getMail();
    }
}
