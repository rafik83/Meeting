<?php

namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;

class ChangePassword implements Command
{
    /** @var Admin */
    public $admin;

    /** @var string */
    public $currentPassword;

    /** @var string */
    public $plainPassword;

    public function __construct(Admin $admin)
    {
        $this->admin = $admin;
    }
}
