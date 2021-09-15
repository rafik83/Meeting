<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver;

use Proximum\Vimeet\Domain\Model\Admin;

class AdminDomain
{
    /** @var Admin */
    private $admin;

    /**
     * @param Admin $admin
     */
    public function __construct(Admin $admin)
    {
        $this->admin = $admin;
    }

    /**
     * @return Admin
     */
    public function getAdmin(): Admin
    {
        return $this->admin;
    }
}
