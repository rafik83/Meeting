<?php

namespace Proximum\Vimeet\Application\Command\Payment;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

abstract class AbstractChoice implements Command
{
    /**
     * @var string
     */
    public $mode;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var User
     */
    public $user;

    /**
     * @param Sheet $sheet
     * @param User  $user
     */
    public function __construct(Sheet $sheet, User $user)
    {
        $this->sheet = $sheet;
        $this->user  = $user;
    }
}
