<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Admin;

class BatchRefuse extends AbstractBatch
{
    /** @var Admin */
    public $admin;

    /**
     * @param int[] $ids   array of Sheets id
     * @param Admin $admin
     */
    public function __construct(array $ids, Admin $admin)
    {
        $this->ids = $ids;
        $this->admin = $admin;
    }
}
