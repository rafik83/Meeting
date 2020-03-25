<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;

class GetWebinarViewQuery implements Query
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
