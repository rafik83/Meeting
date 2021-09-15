<?php

namespace Proximum\Vimeet\Application\Event\Sheet\Order;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\EventDispatcher;

class OrdersCancelledEvent extends EventDispatcher\Event
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
