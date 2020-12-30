<?php

namespace Proximum\Vimeet\Application\Command\Sheet\SheetViewed;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class Add implements Command
{
    /** @var User */
    public $user;

    /** @var Sheet */
    public $sheet;

    /**
     * Add constructor.
     *
     * @param User  $user
     * @param Sheet $sheet
     */
    public function __construct(User $user, Sheet $sheet)
    {
        $this->user  = $user;
        $this->sheet = $sheet;
    }
}
