<?php

namespace Proximum\Vimeet\Application\Command\Event\Find;

use Proximum\Vimeet\Domain\Model\Admin;

class Find
{
    const FIND_INVOICE = 'invoice';
    const FIND_ORDER   = 'order';

    /** @var string */
    public $type;

    /** @var string */
    public $numero;

    /** @var Admin */
    public $admin;

    /**
     * @param Admin $admin
     */
    public function __construct(Admin $admin)
    {
        $this->admin = $admin;
    }

    /**
     * @return bool
     */
    public function findOrder()
    {
        return self::FIND_ORDER === $this->type;
    }

    /**
     * @return bool
     */
    public function findInvoice()
    {
        return self::FIND_INVOICE === $this->type;
    }
}
