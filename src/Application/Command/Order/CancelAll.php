<?php

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;

class CancelAll implements Command
{
    /** @var Sheet */
    public $sheet;

    /** @var Admin */
    public $admin;

    public function __construct(Sheet $sheet, Admin $admin)
    {
        $this->sheet = $sheet;
        $this->admin = $admin;
    }
}
