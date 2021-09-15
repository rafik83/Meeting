<?php

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Domain\Model\Admin;

class Find
{
    /**
     * @var string
     */
    public $numero;

    /**
     * @var Admin
     */
    public $admin;

    /**
     * @param Admin  $admin
     * @param string $numero
     */
    public function __construct(Admin $admin, $numero)
    {
        $this->admin  = $admin;
        $this->numero = $numero;
    }
}
