<?php


namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;

class UnDelete implements Command
{
    /** @var Admin */
    public $admin;

    public function __construct(Admin $admin)
    {
        $this->admin = $admin;
    }
}
