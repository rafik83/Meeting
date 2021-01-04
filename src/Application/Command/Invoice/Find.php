<?php

namespace Proximum\Vimeet\Application\Command\Invoice;

use Proximum\Vimeet\Domain\Model\Admin;

class Find
{
    /** @var Admin */
    public $admin;

    /** @var string */
    public $numero;

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
