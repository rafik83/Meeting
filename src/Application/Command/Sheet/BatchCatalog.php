<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Admin;

class BatchCatalog extends AbstractBatch
{
    /**
     * @var array
     */
    public $ids;

    /**
     * @var bool
     */
    public $state;

    /**
     * @var Admin
     */
    public $admin;

    /**
     * BatchCatalog constructor.
     *
     * @param array $ids
     * @param bool  $state
     * @param Admin $admin
     */
    public function __construct(array $ids, $state, Admin $admin)
    {
        $this->ids   = $ids;
        $this->state = $state;
        $this->admin = $admin;
    }
}
