<?php

namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;

class NewPassword implements Command
{
    /** @var Admin */
    public $admin;

    /** @var string */
    public $password;

    public function __construct(Admin $admin)
    {
        $this->admin = $admin;
    }
}
