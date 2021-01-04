<?php

namespace Proximum\Vimeet\Application\Command\Admin;

use Proximum\Vimeet\Domain\Model\Admin;

class ActivateAccountPassword
{
    /**
     * @var Admin
     */
    public $admin;

    /**
     * @var string
     */
    public $password;

    /**
     * @param Admin $admin
     */
    public function __construct(Admin $admin)
    {
        $this->admin = $admin;
    }
}
