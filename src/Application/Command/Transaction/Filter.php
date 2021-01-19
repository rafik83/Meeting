<?php

namespace Proximum\Vimeet\Application\Command\Transaction;

use Proximum\Vimeet\Domain\Model\Admin;

class Filter
{
    /** @var \DateTime */
    public $beginDate;

    /** @var \DateTime */
    public $endDate;

    /** @var Admin */
    public $admin;

    /**
     * Find constructor.
     *
     * @param Admin $admin
     */
    public function __construct(Admin $admin)
    {
        $this->admin  = $admin;
    }
}
