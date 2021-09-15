<?php

namespace Proximum\Vimeet\Application\Command\Invoice;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;

class Export implements Command
{
    /**
     * @var Admin
     */
    public $admin;

    /**
     * @var \DateTimeInterface
     */
    public $beginDate;

    /**
     * @var \DateTimeInterface
     */
    public $endDate;

    /**
     * Export constructor.
     *
     * @param Admin $admin
     */
    public function __construct(Admin $admin)
    {
        $this->admin = $admin;
    }
}
