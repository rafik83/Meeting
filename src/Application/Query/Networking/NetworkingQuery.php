<?php


namespace Proximum\Vimeet\Application\Query\Networking;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class NetworkingQuery implements Query
{
    /** @var Event */
    public $sheet;

    /** @var User */
    public $user;

    public function __construct(
        Sheet $sheet,
        User $user
    )
    {
        $this->sheet = $sheet;
        $this->user = $user;
    }
}
