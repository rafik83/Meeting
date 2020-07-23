<?php

namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Domain\Model\Admin;

class NewPassword
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
