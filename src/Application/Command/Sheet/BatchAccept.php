<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Admin;

class BatchAccept extends AbstractBatch
{
    /**
     * @var array
     */
    public $ids;

    /**
     * @var Admin
     */
    public $admin;

    /**
     * BatchAccept constructor.
     *
     * @param array $ids
     * @param Admin $admin
     */
    public function __construct(array $ids, Admin $admin)
    {
        $this->ids   = $ids;
        $this->admin = $admin;
    }
}
