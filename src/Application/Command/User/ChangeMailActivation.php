<?php

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Domain\Model\ChangeMailToken;
use Proximum\Vimeet\Domain\Model\User;

class ChangeMailActivation
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
