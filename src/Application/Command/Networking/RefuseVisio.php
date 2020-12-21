<?php


namespace Proximum\Vimeet\Application\Command\Networking;


use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class RefuseVisio implements Command
{

    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $fromUser;

    /** @var User */
    public $toUser;

    public function __construct(Sheet $sheet, User $fromUser, User $toUser)
    {
        $this->sheet = $sheet;
        $this->fromUser = $fromUser;
        $this->toUser = $toUser;
    }
}
