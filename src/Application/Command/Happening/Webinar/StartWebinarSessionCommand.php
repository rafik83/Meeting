<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;

class StartWebinarSessionCommand implements Command
{
    /** @var Happening */
    private $happening;

    /** @var User */
    private $user;

    public function __construct(Happening $happening, User $user)
    {
        $this->happening = $happening;
        $this->user = $user;
    }

    public function getHappening(): Happening
    {
        return $this->happening;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
