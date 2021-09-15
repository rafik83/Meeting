<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Admin;

class BatchPending extends AbstractBatch
{
    /**
     * @var int[]
     */
    public $ids;

    /**
     * @var Admin
     */
    public $admin;

    /**
     * BatchPending constructor.
     *
     * @param int[] $ids
     * @param Admin $admin
     */
    public function __construct(array $ids, Admin $admin)
    {
        $this->ids   = $ids;
        $this->admin = $admin;
    }
}
